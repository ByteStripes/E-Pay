# E-Pay

E-Pay 是一套支付聚合网关。商户通过本系统收款，系统再把订单交给微信、支付宝或其他通道插件。它不保存买家的银行卡信息，资金落在各自的支付通道里。

管理后台给运营人员用，商户面板给接入方用，支付页面给付款人用。

## 功能概览

- 多通道聚合：微信、支付宝、QQ 钱包等 60+ 支付插件
- 商户管理：商户注册、密钥管理、余额结算
- 订单系统：统一下单、页面跳转、异步通知、退款代付
- 风控策略：黑名单、金额校验、IP 限制
- 分销返现：多级邀请返现、按比例扣回
- 定时任务：补单、通知重试、结算生成、分账
- 多模板支持：11 套首页模板可切换

## 环境要求

| 项目 | 要求 |
| --- | --- |
| PHP | 7.4 及以上，建议 8.0 及以上 |
| 扩展 | `pdo_pgsql`、`curl`、`openssl`、`sodium`、`gd`、`mbstring`、`xml`、`zip` |
| 数据库 | PostgreSQL 12+，字符集 `UTF8` |
| Web 服务器 | Nginx 或 IIS，需要重写规则 |

`sodium` 用来加密登录凭证。没有这个扩展时，后台登录会失败。

## 快速启动

以下步骤可在一台新机器上完成部署。示例假设域名为 `epay.example.com`，数据库名为 `epay`，表前缀为 `pay`。

### 1. 安装依赖

```bash
# Debian / Ubuntu
apt-get install -y php8.2 php8.2-fpm php8.2-pgsql php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip
apt-get install -y postgresql nginx
```

### 2. 准备数据库

```bash
sudo -u postgres psql -c "CREATE USER epay WITH PASSWORD '请换成强密码';"
sudo -u postgres psql -c "CREATE DATABASE epay OWNER epay ENCODING 'UTF8';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE epay TO epay;"
```

### 3. 放置代码并设置权限

```bash
git clone git@github.com:ByteStripes/E-Pay.git /opt/E-Pay
chown -R www-data:www-data /opt/E-Pay
chmod -R 775 /opt/E-Pay
```

运行 PHP 的系统用户需要能写入：

- 网站根目录（安装程序要生成 `config.php`）
- `install/`（安装结束后写入 `install.lock`）

不要把 `config.php` 提交到仓库。里面是数据库账号。`.gitignore` 已排除该文件。

### 4. 配置 Nginx

把下面的规则放到站点的 `server` 里。`root` 指到本仓库。完整片段也写在仓库根目录的 `nginx.txt`。

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name epay.example.com;
    root /opt/E-Pay;
    index index.php index.html;

    client_max_body_size 20m;

    location / {
        if (!-e $request_filename) {
            rewrite ^/(.[a-zA-Z0-9\-\_]+).html$ /index.php?mod=$1 last;
        }
        rewrite ^/pay/(.*)$ /pay.php?s=$1 last;
        rewrite ^/api/(.*)$ /api.php?s=$1 last;
        rewrite ^/doc/(.[a-zA-Z0-9\-\_]+).html$ /index.php?doc=$1 last;
    }

    location ^~ /plugins {
        deny all;
    }
    location ^~ /includes {
        deny all;
    }
    location ^~ /install {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(ht|git|env) {
        deny all;
    }
}
```

`/plugins` 和 `/includes` 必须禁止从网上直接访问，否则插件日志和库文件会暴露。站点上线后，`/install` 也应禁止外网访问；需要重装时再临时打开，并先删掉 `install/install.lock`。

IIS 的对应规则在 `IIS.txt`。

### 5. 用浏览器安装

1. 配置好 Nginx 重写规则后，打开 `https://你的域名/install/`。
2. 环境检测通过后，填写数据库主机、端口、用户名、密码、库名。表前缀保持 `pay`。
3. 安装数据表。全新安装会清空该库里的本系统数据。
4. 安装完成会生成 `install/install.lock`。没有这个文件时，网站拒绝工作。

装好后的入口：

| 入口 | 地址 | 初始凭据 |
| --- | --- | --- |
| 网站首页 | `/` | 无 |
| 管理后台 | `/admin/` | 密码 `123456` |
| 商户面板 | `/user/` | 注册或由管理员开通 |

第一次登录后台后立刻修改管理员密码。默认密码只适合安装当天。

### 6. 配置 HTTPS

使用 Certbot 申请 Let's Encrypt 免费证书：

```bash
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d epay.example.com --non-interactive --agree-tos --register-unsafely-without-email --redirect
```

HTTPS 是必须的。登录 Cookie 在 HTTPS 下会带 `Secure` 标记。

### 7. 配置定时任务

后台「系统设置」里先设置监控密钥。然后每分钟访问一次：

```bash
crontab -e
# 添加以下两行
* * * * * curl -s -o /dev/null 'https://你的域名/cron.php?key=监控密钥&do=order'
* * * * * curl -s -o /dev/null 'https://你的域名/cron.php?key=监控密钥&do=notify'
```

常用的 `do`：

| do | 作用 |
| --- | --- |
| `order` | 补单、订单后续处理 |
| `notify` | 重试通知商户 |
| `settle` | 生成结算 |
| `transfer` | 查询代付结果 |
| `check` | 风控巡检（需在后台打开对应开关） |
| `profitsharing` | 分账 |
| `complain` | 投诉处理 |

计划任务建议只从服务器本机或你填写的「服务器 IP」触发。密钥不要出现在对外文档里。

### 8. 安装后检查

安装完成后，如果管理后台报 500 错误并提示 `session_salt 未设置`，请手动执行：

```bash
# 生成随机 salt 并写入数据库
SALT=$(openssl rand -hex 32)
sudo -u postgres psql -d epay -c "INSERT INTO pay_config (k, v) VALUES ('session_salt', '${SALT}') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;"

# 清除配置缓存
sudo -u postgres psql -d epay -c "UPDATE pay_cache SET v = '' WHERE k = 'config';"
```

## 部署说明

### 从 MariaDB/MySQL 迁移到 PostgreSQL

本版本已将数据库层从 MySQL/MariaDB 迁移到 PostgreSQL。如果从旧版本升级：

1. 在 PostgreSQL 中创建数据库和用户（见上方步骤 2）
2. 更新 `config.php` 中的连接参数：

```php
<?php
$dbconfig=array(
    'host' => '127.0.0.1',
    'port' => 5432,
    'user' => 'epay',
    'pwd' => '你的密码',
    'dbname' => 'epay',
    'dbqz' => 'pay'
);
```
3. 删除 `install/install.lock`，重新运行安装向导
4. 安装完成后检查 `session_salt` 是否已设置（见上方步骤 8）

### 安全加固

- `config.php` 和 `install/install.lock` 已通过 `.gitignore` 排除，不会提交到仓库
- `/plugins`、`/includes`、`/install` 路径已在 Nginx 中禁止外网访问
- HTTPS 强制跳转已通过 Certbot 配置
- 后台默认密码 `123456` 必须在安装后立即修改
- `session_salt` 未设置时程序拒绝启动
- 用户 IP 获取方式默认为直连地址，不信任可伪造的 `X-Forwarded-For`

## 目录结构

| 路径 | 内容 |
| --- | --- |
| `admin/` | 管理后台 |
| `user/` | 商户面板 |
| `plugins/` | 支付通道插件（60+） |
| `includes/` | 公共库、入账、退款、代付 |
| `pay.php` | 付款页、渠道回调入口 |
| `api.php` / `mapi.php` | 商户接口 |
| `cron.php` | 定时任务 |
| `install/` | 安装向导 |
| `template/` | 首页模板（11 套） |
| `paypage/` | 支付页面 |
| `assets/` | 前端静态资源 |
| `_deploy/` | 部署辅助脚本 |
| `docs/` | 文档 |

## 上线前检查

- 修改后台密码，并设置 `session_salt`、监控密钥。未设置 `session_salt` 时程序会拒绝启动。
- HTTPS 部署。登录 Cookie 在 HTTPS 下会带 `Secure`。
- 用户 IP 的获取方式：新安装默认是「直连地址」（`ip_type=2`），不信任浏览器可以伪造的 `X-Forwarded-For`。网站前面有 Nginx 反代时，在后台改成「X-Real-IP」，并只允许 Nginx 写入这个头。
- 反代示例：

```nginx
proxy_set_header Host $host;
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header X-Forwarded-Proto $scheme;
```

- 黑名单策略里如果选择「不入账并原路退款」，命中黑名单的买家不会给商户加余额，并由平台向通道发起退款。这不依赖商户是否打开了退款接口。
- `api.php?act=query` 不再返回商户密钥。旧的「把密钥放在网址里」的查询、退款接口仍然保留，供旧商户使用，新商户不要用。订单查询如果走系统签名，必须带 5 分钟内的时间戳。
- 统一下单里的 `clientip` 由商户填写，只能作为买家 IP 的记录。黑名单会同时看这个地址和商户服务器实际连进来的地址。单靠商户上报的 IP 不能当作防盗刷的唯一手段，请同时设置商户日限额。

## 支付结果入账

渠道通知验签通过后，才会把订单标成已支付，并按订单里的商户所得增加余额，然后再通知商户。

以下通道的异步通知会先把渠道实付金额和订单金额比对，对不上就不会入账：通联、杉德、海科、汇付、微信跨境、微信服务商、抖音支付、Stripe、PayPal 同步返回、宝付、盛付通、联动优势、快钱、富友、乐刷、拉卡拉、合利宝、汇联、新生、随行付、银盈通、易生、汇来米、Adapay、支付宝香港。下单用「分」的通道按分比较，用「元」的按元比较到分。支付宝官方插件、易支付上游、蓝兔、虎皮椒等原本就会比对金额。

退款会先锁住订单并扣减商户余额，再请求通道。余额不够时不会扣成负数。通道退款失败时，已扣的余额会加回。已经发给上级的邀请返现，按本次退款占订单金额的比例扣回；订单全部退完时扣清剩余返现。上级余额不够的部分记入风控记录，类型为待追偿。

代付会先扣余额并记下「处理中」，再请求通道。通道明确失败时，余额按该笔代付只退回一次。

## 开发注意事项

- 业务改动不要绕过 `includes/functions.php` 里的 `processNotify` / `processReturn`。新插件入账时请传入实付金额（单位：元）。金额不一致时这两个函数不会加余额。
- 不要在持有数据库事务时访问支付通道。代付已经先记账、后请求通道。
- 插件目录下的 `logs/` 不要对公网开放。Nginx 已用 `deny all` 挡住整个 `plugins` 目录。
- 数据库使用 PostgreSQL，SQL 语句使用 `ON CONFLICT` 语法替代 `ON DUPLICATE KEY UPDATE`。
