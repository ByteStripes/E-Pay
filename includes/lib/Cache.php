<?php
namespace lib;

class Cache {
	public function get($key) {
		global $_CACHE;
		return $_CACHE[$key];
	}
	public function read($key = 'config') {
		global $DB;
		$value = $DB->getColumn("SELECT v FROM pre_cache WHERE k=:key LIMIT 1", [':key'=>$key]);
		return $value;
	}
	public function save($key ,$value, $expire=0) {
		if (is_array($value)) $value = json_encode($value, JSON_UNESCAPED_UNICODE);
		global $DB;
		if($expire) $expire = time() + $expire;
		return $DB->exec("INSERT INTO pre_cache (k, v, expire) VALUES (:key, :value, :expire) ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v, expire = EXCLUDED.expire", [':key'=>$key, ':value'=>$value, ':expire'=>$expire]);
	}
	public function pre_fetch(){
		global $_CACHE;
		$_CACHE=array();
		$cache = $this->read('config');
		$_CACHE = @json_decode($cache, true) ?: @unserialize($cache, ['allowed_classes' => false]);
		if(empty($_CACHE['version']))$_CACHE = $this->update();
		return $_CACHE;
	}
	public function update() {
		global $DB;
		$cache = array();
		$result = $DB->getAll("SELECT * FROM pre_config");
		foreach($result as $row){
			$cache[ $row['k'] ] = $row['v'];
		}
		$this->save('config', $cache);
		return $cache;
	}
	public function clear($key = 'config') {
		global $DB;
		return $DB->exec("UPDATE pre_cache SET v='' WHERE k=:key", [':key'=>$key]);
	}
	public function delete($key) {
		global $DB;
		return $DB->exec("DELETE FROM pre_cache WHERE k=:key", [':key'=>$key]);
	}
	public function clean() {
		global $DB;
		$DB->exec("DELETE FROM pre_cache WHERE expire>0 AND expire<:now", [':now'=>time()]);
		//$DB->exec("OPTIMIZE TABLE pre_cache");
		return true;
	}
}
