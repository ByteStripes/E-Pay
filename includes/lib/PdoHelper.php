<?php
namespace lib;

class PdoHelper
{
	private $sqlPrefix = "pre_";//SQL数据表前缀识别字符
	private $db;
	private $fetchStyle = \PDO::FETCH_ASSOC;
	private $prefix;
	private $errorInfo;
	private $lastTable = null;
	private static $pgReserved = ['desc', 'to', 'user', 'order', 'group', 'index', 'key', 'type', 'mode', 'default', 'time', 'date'];

	/**
	 * PdoHelper constructor (PostgreSQL).
	 *
	 * @param array $dbconfig 数据库信息
	 */
	function __construct($dbconfig)
	{
		$this->prefix = $dbconfig['dbqz'].'_';
		$port = !empty($dbconfig['port']) ? intval($dbconfig['port']) : 5432;
		try {
			$dsn = "pgsql:host={$dbconfig['host']};port={$port};dbname={$dbconfig['dbname']}";
			$this->db = new \PDO($dsn, $dbconfig['user'], $dbconfig['pwd']);
		} catch (\Exception $e) {
			error_log('PostgreSQL数据库连接失败: ' . $e->getMessage());
			exit('系统维护中，请稍后再试');
		}
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$this->db->exec("SET NAMES 'UTF8'");
		$this->db->exec("SET TIME ZONE '+8'");
	}

	/**
	 * 设置结果集方式
	 *
	 * @param string $_style
	 */
	public function setFetchStyle($_style)
	{
		$this->fetchStyle = $_style;
	}

	/**
	 * 包装PostgreSQL列名（处理保留字如 desc, to, user 等）
	 */
	private function quoteIdent($col)
	{
		$col = trim(str_replace('`', '', (string)$col));
		if (in_array(strtolower($col), self::$pgReserved, true)) {
			return '"' . $col . '"';
		}
		return $col;
	}

	/**
	 * 替换数据表前缀并将历史MySQL方言平滑转换为PostgreSQL标准语法
	 * @param string $_sql
	 *
	 * @return string
	 */
	private function dealPrefix($_sql)
	{
		$_sql = str_replace($this->sqlPrefix, $this->prefix, $_sql);

		// 1. 转换反引号标识符：若为PostgreSQL保留字则转为双引号，否则去掉反引号
		$_sql = preg_replace_callback('/`([a-zA-Z0-9_]+)`/', function($m) {
			return $this->quoteIdent($m[1]);
		}, $_sql);

		// 2. 转换 REPLACE INTO pre_config SET v=:v,k=:k
		if (preg_match('/^\s*REPLACE\s+INTO\s+([a-zA-Z0-9_]+)\s+SET\s+v\s*=\s*:v\s*,\s*k\s*=\s*:k/i', $_sql, $m)) {
			return "INSERT INTO {$m[1]} (k, v) VALUES (:k, :v) ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v";
		}

		// 3. 转换 REPLACE INTO pre_cache VALUES (:key, :value, :expire)
		if (preg_match('/^\s*REPLACE\s+INTO\s+([a-zA-Z0-9_]+)\s+VALUES\s*\(\s*:key\s*,\s*:value\s*,\s*:expire\s*\)/i', $_sql, $m)) {
			return "INSERT INTO {$m[1]} (k, v, expire) VALUES (:key, :value, :expire) ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v, expire = EXCLUDED.expire";
		}

		// 4. 转换 OPTIMIZE TABLE table -> VACUUM table
		if (preg_match('/^\s*OPTIMIZE\s+TABLE\s+([a-zA-Z0-9_"]+)/i', $_sql, $m)) {
			return "VACUUM {$m[1]}";
		}

		// 5. 转换 SHOW TABLES LIKE ...
		if (preg_match('/^\s*SHOW\s+TABLES\s+LIKE\s+(.+)$/i', $_sql, $m)) {
			return "SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename LIKE " . trim($m[1]);
		}

		// 6. 转换 COUNT(IF(cond, 1, NULL)) -> COUNT(CASE WHEN cond THEN 1 ELSE NULL END)
		$_sql = preg_replace('/COUNT\s*\(\s*IF\s*\(([^,]+),\s*1\s*,\s*NULL\s*\)\s*\)/i', 'COUNT(CASE WHEN $1 THEN 1 ELSE NULL END)', $_sql);

		// 7. 转换 ISNULL(expr) -> (expr IS NULL)
		$_sql = preg_replace('/\bISNULL\s*\(([^)]+)\)/i', '(CASE WHEN $1 IS NULL THEN 1 ELSE 0 END)', $_sql);

		// 8. 转换 CURDATE() / CURTIME()
		$_sql = preg_replace('/\bCURDATE\s*\(\s*\)/i', 'CURRENT_DATE', $_sql);
		$_sql = preg_replace('/\bCURTIME\s*\(\s*\)/i', 'CURRENT_TIME', $_sql);

		// 9. 转换 DATE_SUB(NOW(), INTERVAL :sec SECOND) 或 DATE_SUB(NOW(), INTERVAL N UNIT)
		$_sql = preg_replace(
			'/\bDATE_SUB\s*\(\s*NOW\s*\(\s*\)\s*,\s*INTERVAL\s+(:[a-zA-Z0-9_]+)\s+([a-zA-Z]+)\s*\)/i',
			"(NOW() - ($1 || ' $2')::interval)",
			$_sql
		);
		$_sql = preg_replace(
			'/\bDATE_SUB\s*\(\s*NOW\s*\(\s*\)\s*,\s*INTERVAL\s+([0-9]+)\s+([a-zA-Z]+)\s*\)/i',
			"(NOW() - INTERVAL '$1 $2')",
			$_sql
		);
		$_sql = preg_replace(
			'/\bDATE_ADD\s*\(\s*NOW\s*\(\s*\)\s*,\s*INTERVAL\s+([0-9]+)\s+([a-zA-Z]+)\s*\)/i',
			"(NOW() + INTERVAL '$1 $2')",
			$_sql
		);

		// 10. 转换 TO_DAYS(NOW()) - TO_DAYS(col) <= N
		$_sql = preg_replace(
			'/\(\s*TO_DAYS\s*\(\s*NOW\s*\(\s*\)\s*\)\s*-\s*TO_DAYS\s*\(\s*([a-zA-Z0-9_.]+)\s*\)\s*<=\s*([0-9]+)\s*\)/i',
			"($1 >= (NOW() - INTERVAL '$2 DAY'))",
			$_sql
		);

		// 11. 转换 WHERE 1 (非布尔条件) -> WHERE 1=1
		$_sql = preg_replace('/\bWHERE\s+1(?=\s+(?:ORDER|LIMIT|GROUP|$)|$)/i', 'WHERE 1=1', $_sql);

		// 12. 转换 MySQL 双参数 LIMIT offset, count -> PostgreSQL LIMIT count OFFSET offset
		$_sql = preg_replace(
			'/\bLIMIT\s+([0-9]+|:[a-zA-Z0-9_]+)\s*,\s*([0-9]+|:[a-zA-Z0-9_]+)\s*$/i',
			'LIMIT $2 OFFSET $1',
			$_sql
		);

		return $_sql;
	}

	private function _where($conditions){
		$result = array( "_where" => " ","_bindParams" => array());
		if(is_array($conditions) && !empty($conditions)){
			$fieldss = array(); $sql = null; $join = array();
			if(isset($conditions[0]) && $sql = $conditions[0]) unset($conditions[0]);
			foreach( $conditions as $key => $condition ){
				$cleanKey = ltrim($key, ':');
				unset($conditions[$key]);
				$conditions[":".$cleanKey] = $condition;
				$join[] = $this->quoteIdent($cleanKey) . " = :" . $cleanKey;
			}
			if(!$sql) $sql = join(" AND ",$join);

			$result["_where"] = " WHERE ". $sql;
			$result["_bindParams"] = $conditions;
		}elseif(!empty($conditions)){
			trigger_error('PdoHelper::_where() 传入了字符串条件，应使用数组条件或参数绑定以防止SQL注入', E_USER_DEPRECATED);
			$result["_where"] = " WHERE ". $conditions;
		}
		return $result;
	}

	private function _select($table, $fields = '*', $where = array(), $sort = null, $limit = null){
		$sort = $this->sanitizeOrderBy($sort);
		$sort = !empty($sort) ? ' ORDER BY '.$sort : '';
		$fields = !empty($fields) ? $fields : '*';
		if(is_array($fields)){
			$fields = implode(',', array_map([$this, 'quoteIdent'], $fields));
		}
		$conditions = $this->_where($where);

		$sql = ' FROM pre_'.$table.$conditions["_where"];
		if(is_array($limit)){
			$limit = ' LIMIT '.intval($limit[1]).' OFFSET '.intval($limit[0]);
		}elseif(!empty($limit)){
			$limit = ' LIMIT '.intval($limit);
		}else{
			$limit = '';
		}
		return array('sql'=>'SELECT '. $fields . $sql . $sort . $limit, 'bind'=>$conditions["_bindParams"]);
	}

	/**
	 * 校验并规范化 ORDER BY 子句，防止 SQL 注入。
	 */
	private function sanitizeOrderBy($sort){
		if(empty($sort)) return '';
		if(!is_string($sort)){
			trigger_error('PdoHelper::_select() ORDER BY 参数类型非法已被忽略', E_USER_DEPRECATED);
			return '';
		}
		$items = explode(',', $sort);
		$safe = array();
		foreach($items as $item){
			$item = trim($item);
			if($item === '') continue;
			if(preg_match('/^`?[a-zA-Z_][a-zA-Z0-9_]*`?(\.`?[a-zA-Z_][a-zA-Z0-9_]*`?)?(\s+(ASC|DESC))?$/i', $item)){
				$safe[] = $item;
			}else{
				trigger_error('PdoHelper::_select() 检测到非法 ORDER BY 片段已被忽略: '.$item, E_USER_DEPRECATED);
				return '';
			}
		}
		return implode(', ', $safe);
	}

	public function find($table, $fields = '*', $where = array(), $sort = null, $limit = null){
		$sql_arr = $this->_select($table, $fields, $where, $sort, $limit ?: 1);
		return $this->getRow($sql_arr['sql'], $sql_arr['bind']);
	}

	public function findAll($table, $fields = '*', $where = array(), $sort = null, $limit = null){
		$sql_arr = $this->_select($table, $fields, $where, $sort, $limit);
		return $this->getAll($sql_arr['sql'], $sql_arr['bind']);
	}

	public function findColumn($table, $fields, $where = array(), $sort = null){
		$sql_arr = $this->_select($table, $fields, $where, $sort, 1);
		return $this->getColumn($sql_arr['sql'], $sql_arr['bind']);
	}

	/**
	 * 插入数据（兼容PostgreSQL自增序列返回与ON CONFLICT DO NOTHING）
	 * @param string $table
	 * @param array $data
	 * @param bool $replace
	 *
	 * @return int|bool
	 */
	public function insert($table, $data, $replace = false){
		$values = array();
		$keys = array();
		$marks = array();
		foreach ($data as $k=>$v){
			$keys[] = $this->quoteIdent($k);
			if ($v === 'NOW()' || $v === 'CURDATE()' || $v === 'CURTIME()' || $v === 'CURRENT_DATE' || $v === 'CURRENT_TIME') {
				$marks[] = ($v === 'CURDATE()') ? 'CURRENT_DATE' : (($v === 'CURTIME()') ? 'CURRENT_TIME' : $v);
			}elseif ($v === null || $v === false) {
				$marks[] = 'NULL';
			}else{
				$values[":".$k] = $v;
				$marks[] = ":".$k;
			}
		}
		$this->lastTable = $this->prefix . $table;
		$conflictClause = $replace ? " ON CONFLICT DO NOTHING" : "";
		$rowCount = $this->exec("INSERT INTO pre_".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $marks).")".$conflictClause, $values);
		if($rowCount){
			$lastId = $this->lastInsertId();
			return $lastId ?: $rowCount;
		}else{
			return false;
		}
	}

	/**
	 * 更新数据
	 */
	public function update($table, $data, $where){
		$values = array();
		if(is_array($data) && !empty($data)){
			$setstr = array();
			foreach ($data as $k=>$v){
				$col = $this->quoteIdent($k);
				if($v === 'NOW()' || $v === 'CURDATE()' || $v === 'CURTIME()' || $v === 'CURRENT_DATE' || $v === 'CURRENT_TIME'){
					$sqlVal = ($v === 'CURDATE()') ? 'CURRENT_DATE' : (($v === 'CURTIME()') ? 'CURRENT_TIME' : $v);
					$setstr[] = "{$col} = ".$sqlVal;
				}elseif($v === null || $v === false){
					$setstr[] = "{$col} = NULL";
				}else{
					$values[":M_UPDATE_".$k] = $v;
					$setstr[] = "{$col} = :M_UPDATE_".$k;
				}
			}
			$update = implode(', ', $setstr);
		}elseif(!empty($data)){
			$update = $data;
		}else{
			return false;
		}
		$conditions = $this->_where($where);
		$rowCount = $this->exec("UPDATE pre_".$table." SET ".$update.$conditions["_where"], $conditions["_bindParams"] + $values);
		return $rowCount;
	}

	public function delete($table, $where){
		$conditions = $this->_where($where);
		$rowCount = $this->exec("DELETE FROM pre_".$table.$conditions["_where"], $conditions["_bindParams"]);
		return $rowCount;
	}

	public function count($table, $where){
		$conditions = $this->_where($where);
		$count = $this->getColumn("SELECT COUNT(*) FROM pre_".$table.$conditions["_where"], $conditions["_bindParams"]);
		return $count;
	}

	public function exec($_sql, $_array = null)
	{
		$_sql = $this->dealPrefix($_sql);
		if (preg_match('/^\s*INSERT\s+INTO\s+([a-zA-Z0-9_]+)/i', $_sql, $m)) {
			$this->lastTable = $m[1];
		}
		try {
			if (is_array($_array)) {
				$stmt = $this->db->prepare($_sql);
				if($stmt) {
					$result = $stmt->execute($_array);
					if($result!==false){
						return $stmt->rowCount();
					}else{
						$this->errorInfo = $stmt->errorInfo();
						return false;
					}
				}else{
					$this->errorInfo = $this->db->errorInfo();
					return false;
				}
			} else {
				$result = $this->db->exec($_sql);
				if($result!==false){
					return $result;
				}else{
					$this->errorInfo = $this->db->errorInfo();
					return false;
				}
			}
		} catch (\PDOException $e) {
			$this->errorInfo = $e->errorInfo ?: [$e->getCode(), $e->getCode(), $e->getMessage()];
			return false;
		}
	}

	public function query($_sql, $_array = null)
	{
		$_sql = $this->dealPrefix($_sql);
		try {
			if (is_array($_array)) {
				$stmt = $this->db->prepare($_sql);
				if($stmt) {
					if($stmt->execute($_array)){
						return $stmt;
					}else{
						$this->errorInfo = $stmt->errorInfo();
						return false;
					}
				}else{
					$this->errorInfo = $this->db->errorInfo();
					return false;
				}
			} else {
				if($stmt = $this->db->query($_sql)){
					return $stmt;
				}else{
					$this->errorInfo = $this->db->errorInfo();
					return false;
				}
			}
		} catch (\PDOException $e) {
			$this->errorInfo = $e->errorInfo ?: [$e->getCode(), $e->getCode(), $e->getMessage()];
			return false;
		}
	}

	public function getRow($_sql, $_array = null)
	{
		$stmt = $this->query($_sql, $_array);
		if($stmt) {
			return $stmt->fetch($this->fetchStyle);
		}else{
			return false;
		}
	}

	public function getAll($_sql, $_array = null)
	{
		$stmt = $this->query($_sql, $_array);
		if($stmt) {
			return $stmt->fetchAll($this->fetchStyle);
		}else{
			return false;
		}
	}

	public function getCount($_sql, $_array = null)
	{
		$stmt = $this->query($_sql, $_array);
		if($stmt) {
			return $stmt->rowCount();
		}else{
			return false;
		}
	}

	public function getColumn($_sql, $_array = null)
	{
		$stmt = $this->query($_sql, $_array);
		if($stmt) {
			return $stmt->fetchColumn();
		}else{
			return false;
		}
	}

	/**
	 * 返回最后插入行的ID（兼容PostgreSQL序列）
	 */
	public function lastInsertId()
	{
		try {
			return $this->db->lastInsertId();
		} catch (\PDOException $e) {
			return 0;
		}
	}

	public function error()
	{
		$error = $this->errorInfo;
		if($error){
			return '['.$error[1].']'.$error[2];
		}else{
			return null;
		}
	}

	public function beginTransaction()
	{
		return $this->db->beginTransaction();
	}

	public function commit()
	{
		return $this->db->commit();
	}

	public function rollBack()
	{
		return $this->db->rollBack();
	}

	public function transaction($action){
		if (is_callable($action))
		{
			$this->db->beginTransaction();

			try {
				$result = $action($this);

				if ($result === false)
				{
					$this->db->rollBack();
				}
				else
				{
					$this->db->commit();
				}
			}
			catch (\Exception $e) {
				$this->db->rollBack();
				throw $e;
			}

			return $result;
		}

		return false;
	}

	function __get($name)
	{
		return $this->$name;
	}

	function __destruct()
	{
		$this->db = null;
	}
}
