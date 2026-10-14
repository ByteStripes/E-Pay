# E-Pay

E-Pay 是一套支付聚合网关。商户通过本系统收款，系统再把订单交给微信、支付宝或其他通道插件。它不保存买家的银行卡信息，资金落在各自的支付通道里。

管理后台给运营人员用，商户面板给接入方用，支付页面给付款人用。

## Quick Start

下面这几步可以在一台新机器上把站点跑起来。示例假设网站根目录就是本仓库，数据库名为 `epay`，表前缀为 `pay`。

### 1. 环境

| 项目 | 要求 |
| --- | --- |
| PHP | 7.4 及以上，建议 8.0 及以上 |
| 扩展 | `pdo_mysql`、`curl`、`openssl`、`sodium`、`gd` |
| 数据库 | MySQL 5.7+ / MariaDB，字符集 `utf8mb4` |
| Web 服务器 | Nginx 或 IIS，需要重写规则 |

`sodium` 用来加密登录凭证。没有这个扩展时，后台登录会失败。

### 2. 放置代码并设置权限

把代码放到网站根目录。运行 PHP 的系统用户需要能写入：

- 网站根目录（安装程序要生成 `config.php`）
- `install/`（安装结束后写入 `install.lock`）

不要把 `config.php` 提交到仓库。里面是数据库账号。

### 3. 准备数据库

```sql
CREATE DATABASE epay DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'epay'@'127.0.0.1' IDENTIFIED BY '请换成强密码';
GRANT ALL PRIVILEGES ON epay.* TO 'epay'@'127.0.0.1';
FLUSH PRIVILEGES;
```

### 4. 用浏览器安装

1. 配置好下面的重写规则后，打开 `https://你的域名/install/`。
2. 环境检测通过后，填写数据库主机、端口、用户名、密码、库名。表前缀保持 `pay`。
3. 安装数据表。已经装过、只想沿用旧表时，可以选择跳过建表。全新安装会清空该库里的本系统数据。
4. 安装完成会生成 `install/install.lock`。没有这个文件时，网站拒绝工作，避免别人再次执行安装。

装好后的入口：

| 入口 | 地址 | 初始凭据 |
| --- | --- | --- |
| 网站首页 | `/` | 无 |
| 管理后台 | `/admin/` | 密码 `123456` |
| 商户面板 | `/user/` | 注册或由管理员开通 |

第一次登录后台后立刻修改管理员密码。默认密码只适合安装当天。

### 5. Nginx

把下面的规则放到站点的 `server` 里。`root` 指到本仓库。完整片段也写在仓库根目录的 `nginx.txt`。

```nginx
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
```

`/plugins` 和 `/includes` 必须禁止从网上直接访问，否则插件日志和库文件会暴露。站点上线后，`/install` 也应禁止外网访问；需要重装时再临时打开，并先删掉 `install/install.lock`。

IIS 的对应规则在 `IIS.txt`。

### 6. 定时任务

后台「系统设置」里先设置监控密钥。然后每分钟访问一次：

```text
https://你的域名/cron.php?key=监控密钥&do=order
https://你的域名/cron.php?key=监控密钥&do=notify
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

### 7. 接一笔测试支付

1. 后台添加一个支付通道，并启用对应插件。
2. 注册一个商户，或在后台开通商户，记下商户 ID 和密钥。
3. 在商户面板把收款方式打开。
4. 用商户密钥调用统一下单或页面跳转支付。接口说明在站点的 `/doc/` 页面。
5. 用通道的沙箱或小额真实付款走通：下单、支付、异步通知、商户回调。

页面跳转支付和统一下单的地址形如：

```text
https://你的域名/submit.php
https://你的域名/mapi.php
https://你的域名/api/pay/create
```

新接入的商户请使用 RSA 签名，并带上 5 分钟内的 `timestamp`。文档页有签名步骤。

## 目录

| 路径 | 内容 |
| --- | --- |
| `admin/` | 管理后台 |
| `user/` | 商户面板 |
| `plugins/` | 支付通道插件 |
| `includes/` | 公共库、入账、退款、代付 |
| `pay.php` | 付款页、渠道回调入口 |
| `api.php` / `mapi.php` | 商户接口 |
| `cron.php` | 定时任务 |
| `install/` | 安装向导 |
| `template/` | 首页模板 |

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

## 支付结果怎么入账

渠道通知验签通过后，才会把订单标成已支付，并按订单里的商户所得增加余额，然后再通知商户。

以下通道的异步通知会先把渠道实付金额和订单金额比对，对不上就不会入账：通联、杉德、海科、汇付、微信跨境、微信服务商、抖音支付、Stripe、PayPal 同步返回、宝付、盛付通、联动优势、快钱、富友、乐刷、拉卡拉、合利宝、汇联、新生、随行付、银盈通、易生、汇来米、Adapay、支付宝香港。下单用「分」的通道按分比较，用「元」的按元比较到分。支付宝官方插件、易支付上游、蓝兔、虎皮椒等原本就会比对金额。

退款会先锁住订单并扣减商户余额，再请求通道。余额不够时不会扣成负数。通道退款失败时，已扣的余额会加回。已经发给上级的邀请返现，按本次退款占订单金额的比例扣回；订单全部退完时扣清剩余返现。上级余额不够的部分记入风控记录，类型为待追偿。

代付会先扣余额并记下「处理中」，再请求通道。通道明确失败时，余额按该笔代付只退回一次。

## 开发时注意

- 业务改动不要绕过 `includes/functions.php` 里的 `processNotify` / `processReturn`。新插件入账时请传入实付金额（单位：元）。金额不一致时这两个函数不会加余额。
- 不要在持有数据库事务时访问支付通道。代付已经先记账、后请求通道。
- 插件目录下的 `logs/` 不要对公网开放。Nginx 已用 `deny all` 挡住整个 `plugins` 目录。
