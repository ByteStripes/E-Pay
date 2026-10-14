DROP TABLE IF EXISTS pre_config;
CREATE TABLE pre_config (
  k varchar(32) NOT NULL,
  v text NULL,
  PRIMARY KEY (k)
);

INSERT INTO pre_config (k, v) VALUES ('version', '2054') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('admin_user', 'admin') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('admin_pwd', '123456') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('admin_paypwd', '123456') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('homepage', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('sitename', '聚合支付平台') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('title', '聚合支付 - 行业领先的免签约支付平台') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('keywords', '聚合支付,支付宝免签约即时到账,财付通免签约,微信免签约支付,QQ钱包免签约,免签约支付') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('description', '聚合支付是XX公司旗下的免签约支付产品，完美解决支付难题，一站式接入支付宝，微信，财付通，QQ钱包,微信wap，帮助开发者快速集成到自己相应产品，效率高，见效快，费率低！') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('orgname', 'XX公司') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('kfqq', '123456789') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('template', 'index1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('pre_maxmoney', '1000') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('blockname', '') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('blockalert', '温馨提醒该商品禁止出售，如有疑问请联系网站客服！') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_open', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_type', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_money', '30') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_rate', '0.5') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_fee_min', '0.1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_fee_max', '20') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_alipay', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_wxpay', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_qqpay', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('settle_bank', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('transfer_alipay', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('transfer_wxpay', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('transfer_qqpay', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('transfer_name', '聚合支付平台') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('transfer_desc', '聚合支付平台自动结算') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('login_qq', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('login_alipay', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('login_alipay_channel', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('login_wx', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('login_wx_channel', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('reg_open', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('reg_pay', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('reg_pre_uid', '1000') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('reg_pre_price', '5') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('verifytype', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('test_open', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('test_pre_uid', '1000') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('mail_cloud', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('mail_smtp', 'smtp.qq.com') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('mail_port', '465') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('mail_name', '') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('mail_pwd', '') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('sms_api', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('captcha_open', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('captcha_id', '') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('captcha_key', '') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('onecode', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('recharge', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('pageordername', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('notifyordername', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('user_refund', '1') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('cdnpublic', '0') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;
INSERT INTO pre_config (k, v) VALUES ('ip_type', '2') ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v;

DROP TABLE IF EXISTS pre_cache;
CREATE TABLE pre_cache (
  k varchar(32) NOT NULL,
  v text NULL,
  expire integer NOT NULL DEFAULT 0,
  PRIMARY KEY (k)
);

DROP TABLE IF EXISTS pre_anounce;
CREATE TABLE pre_anounce (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  content text DEFAULT NULL,
  color varchar(10) DEFAULT NULL,
  sort integer NOT NULL DEFAULT 1,
  addtime timestamp DEFAULT NULL,
  status smallint NOT NULL DEFAULT 1
);

DROP TABLE IF EXISTS pre_type;
CREATE TABLE pre_type (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  name varchar(30) NOT NULL,
  device integer NOT NULL DEFAULT 0,
  showname varchar(30) NOT NULL,
  status smallint NOT NULL DEFAULT 0
);
CREATE INDEX idx_pre_type_name_device ON pre_type (name, device);

INSERT INTO pre_type (id, name, device, showname, status) VALUES (1, 'alipay', 0, '支付宝', 1) ON CONFLICT (id) DO NOTHING;
INSERT INTO pre_type (id, name, device, showname, status) VALUES (2, 'wxpay', 0, '微信支付', 1) ON CONFLICT (id) DO NOTHING;
INSERT INTO pre_type (id, name, device, showname, status) VALUES (3, 'qqpay', 0, 'QQ钱包', 1) ON CONFLICT (id) DO NOTHING;
INSERT INTO pre_type (id, name, device, showname, status) VALUES (4, 'bank', 0, '网银支付', 0) ON CONFLICT (id) DO NOTHING;
INSERT INTO pre_type (id, name, device, showname, status) VALUES (5, 'jdpay', 0, '京东支付', 0) ON CONFLICT (id) DO NOTHING;
INSERT INTO pre_type (id, name, device, showname, status) VALUES (6, 'paypal', 0, 'PayPal', 0) ON CONFLICT (id) DO NOTHING;
INSERT INTO pre_type (id, name, device, showname, status) VALUES (7, 'douyinpay', 0, '抖音支付', 0) ON CONFLICT (id) DO NOTHING;
SELECT setval(pg_get_serial_sequence('pre_type', 'id'), 7, true);

DROP TABLE IF EXISTS pre_plugin;
CREATE TABLE pre_plugin (
  name varchar(30) NOT NULL,
  showname varchar(60) DEFAULT NULL,
  author varchar(60) DEFAULT NULL,
  link varchar(255) DEFAULT NULL,
  types varchar(50) DEFAULT NULL,
  transtypes varchar(50) DEFAULT NULL,
  PRIMARY KEY (name)
);

DROP TABLE IF EXISTS pre_channel;
CREATE TABLE pre_channel (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  mode smallint DEFAULT 0,
  type integer NOT NULL,
  plugin varchar(30) NOT NULL,
  name varchar(30) NOT NULL,
  rate numeric(5,2) NOT NULL DEFAULT 100.00,
  status smallint NOT NULL DEFAULT 0,
  apptype varchar(50) DEFAULT NULL,
  daytop integer DEFAULT 0,
  daystatus smallint DEFAULT 0,
  paymin varchar(10) DEFAULT NULL,
  paymax varchar(10) DEFAULT NULL,
  appwxmp integer DEFAULT NULL,
  appwxa integer DEFAULT NULL,
  costrate numeric(5,2) DEFAULT NULL,
  config text DEFAULT NULL,
  daymaxorder integer DEFAULT 0,
  timestart integer DEFAULT NULL,
  timestop integer DEFAULT NULL
);
CREATE INDEX idx_pre_channel_type ON pre_channel (type);

DROP TABLE IF EXISTS pre_roll;
CREATE TABLE pre_roll (
  id integer GENERATED BY DEFAULT AS IDENTITY (START WITH 101) PRIMARY KEY,
  type integer NOT NULL,
  name varchar(30) NOT NULL,
  kind integer NOT NULL DEFAULT 0,
  info text DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  index integer NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS pre_weixin;
CREATE TABLE pre_weixin (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  type smallint NOT NULL DEFAULT 0,
  name varchar(30) NOT NULL,
  status smallint NOT NULL DEFAULT 0,
  appid varchar(150) DEFAULT NULL,
  appsecret varchar(250) DEFAULT NULL,
  access_token varchar(300) DEFAULT NULL,
  addtime timestamp DEFAULT NULL,
  updatetime timestamp DEFAULT NULL,
  expiretime timestamp DEFAULT NULL
);

DROP TABLE IF EXISTS pre_order;
CREATE TABLE pre_order (
  trade_no char(19) NOT NULL,
  out_trade_no varchar(150) NOT NULL,
  api_trade_no varchar(150) DEFAULT NULL,
  uid integer NOT NULL,
  tid smallint NOT NULL DEFAULT 0,
  type integer NOT NULL DEFAULT 0,
  channel integer NOT NULL DEFAULT 0,
  name varchar(64) NOT NULL,
  money numeric(10,2) NOT NULL,
  realmoney numeric(10,2) DEFAULT NULL,
  getmoney numeric(10,2) DEFAULT NULL,
  profitmoney numeric(10,2) DEFAULT NULL,
  refundmoney numeric(10,2) DEFAULT NULL,
  notify_url varchar(255) DEFAULT NULL,
  return_url varchar(255) DEFAULT NULL,
  param varchar(255) DEFAULT NULL,
  addtime timestamp NOT NULL,
  endtime timestamp DEFAULT NULL,
  date date DEFAULT NULL,
  domain varchar(64) DEFAULT NULL,
  domain2 varchar(64) DEFAULT NULL,
  ip varchar(50) DEFAULT NULL,
  buyer varchar(100) DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  notify integer NOT NULL DEFAULT 0,
  notifytime timestamp DEFAULT NULL,
  invite integer NOT NULL DEFAULT 0,
  invitemoney numeric(10,2) DEFAULT NULL,
  combine smallint NOT NULL DEFAULT 0,
  profits integer NOT NULL DEFAULT 0,
  profits2 integer NOT NULL DEFAULT 0,
  settle smallint NOT NULL DEFAULT 0,
  subchannel integer NOT NULL DEFAULT 0,
  payurl varchar(500) DEFAULT NULL,
  ext text DEFAULT NULL,
  version smallint NOT NULL DEFAULT 0,
  bill_trade_no varchar(150) DEFAULT NULL,
  bill_mch_trade_no varchar(150) DEFAULT NULL,
  mobile varchar(100) DEFAULT NULL,
  cert_info varchar(300) DEFAULT NULL,
  province varchar(2) DEFAULT NULL,
  PRIMARY KEY (trade_no)
);
CREATE INDEX idx_pre_order_uid ON pre_order (uid);
CREATE INDEX idx_pre_order_out_trade_no ON pre_order (out_trade_no, uid);
CREATE INDEX idx_pre_order_api_trade_no ON pre_order (api_trade_no);
CREATE INDEX idx_pre_order_bill_trade_no ON pre_order (bill_trade_no);
CREATE INDEX idx_pre_order_bill_mch_trade_no ON pre_order (bill_mch_trade_no);
CREATE INDEX idx_pre_order_date ON pre_order (date);

DROP TABLE IF EXISTS pre_group;
CREATE TABLE pre_group (
  gid integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  name varchar(30) NOT NULL,
  info varchar(1024) DEFAULT NULL,
  isbuy smallint NOT NULL DEFAULT 0,
  price numeric(10,2) DEFAULT NULL,
  sort integer NOT NULL DEFAULT 0,
  expire integer NOT NULL DEFAULT 0,
  config text DEFAULT NULL,
  settings text DEFAULT NULL,
  visible varchar(30) DEFAULT NULL,
  index integer NOT NULL DEFAULT 0
);

INSERT INTO pre_group (gid, name, info) VALUES (0, '默认用户组', '{"1":{"type":"","channel":"-1","rate":""},"2":{"type":"","channel":"-1","rate":""},"3":{"type":"","channel":"-1","rate":""}}') ON CONFLICT (gid) DO NOTHING;

DROP TABLE IF EXISTS pre_user;
CREATE TABLE pre_user (
  uid integer GENERATED BY DEFAULT AS IDENTITY (START WITH 1000) PRIMARY KEY,
  gid integer NOT NULL DEFAULT 0,
  upid integer NOT NULL DEFAULT 0,
  key varchar(32) NOT NULL,
  pwd varchar(255) DEFAULT NULL,
  account varchar(128) DEFAULT NULL,
  username varchar(128) DEFAULT NULL,
  codename varchar(32) DEFAULT NULL,
  settle_id smallint NOT NULL DEFAULT 1,
  alipay_uid varchar(32) DEFAULT NULL,
  qq_uid varchar(32) DEFAULT NULL,
  wx_uid varchar(32) DEFAULT NULL,
  wxa_uid varchar(32) DEFAULT NULL,
  money numeric(10,2) NOT NULL DEFAULT 0.00,
  email varchar(32) DEFAULT NULL,
  phone varchar(20) DEFAULT NULL,
  qq varchar(20) DEFAULT NULL,
  url varchar(64) DEFAULT NULL,
  cert smallint NOT NULL DEFAULT 0,
  certtype smallint NOT NULL DEFAULT 0,
  certmethod smallint NOT NULL DEFAULT 0,
  certno varchar(18) DEFAULT NULL,
  certname varchar(32) DEFAULT NULL,
  certtime timestamp DEFAULT NULL,
  certtoken varchar(64) DEFAULT NULL,
  certcorpno varchar(30) DEFAULT NULL,
  certcorpname varchar(80) DEFAULT NULL,
  addtime timestamp DEFAULT NULL,
  lasttime timestamp DEFAULT NULL,
  endtime timestamp DEFAULT NULL,
  level smallint NOT NULL DEFAULT 1,
  pay smallint NOT NULL DEFAULT 1,
  settle smallint NOT NULL DEFAULT 1,
  keylogin smallint NOT NULL DEFAULT 1,
  apply smallint NOT NULL DEFAULT 0,
  mode smallint NOT NULL DEFAULT 0,
  status smallint NOT NULL DEFAULT 0,
  refund smallint NOT NULL DEFAULT 1,
  transfer smallint NOT NULL DEFAULT 0,
  keytype smallint NOT NULL DEFAULT 0,
  publickey varchar(500) DEFAULT NULL,
  channelinfo text DEFAULT NULL,
  ordername varchar(255) DEFAULT NULL,
  msgconfig text DEFAULT NULL,
  remain_money varchar(20) DEFAULT NULL,
  open_code smallint NOT NULL DEFAULT 0,
  deposit numeric(10,2) DEFAULT NULL,
  voice_order smallint NOT NULL DEFAULT 0,
  voice_devid varchar(30) DEFAULT NULL,
  device_secret varchar(64) DEFAULT NULL,
  print_order smallint NOT NULL DEFAULT 0,
  print_config varchar(300) DEFAULT NULL,
  pay_maxmoney varchar(10) DEFAULT NULL,
  pay_minmoney varchar(10) DEFAULT NULL
);
CREATE INDEX idx_pre_user_email ON pre_user (email);
CREATE INDEX idx_pre_user_phone ON pre_user (phone);

DROP TABLE IF EXISTS pre_settle;
CREATE TABLE pre_settle (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  uid integer NOT NULL,
  batch varchar(20) DEFAULT NULL,
  auto smallint NOT NULL DEFAULT 1,
  type smallint NOT NULL DEFAULT 1,
  account varchar(128) NOT NULL,
  username varchar(128) NOT NULL,
  money numeric(10,2) NOT NULL,
  realmoney numeric(10,2) NOT NULL,
  addtime timestamp DEFAULT NULL,
  endtime timestamp DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  transfer_no varchar(64) DEFAULT NULL,
  transfer_channel integer DEFAULT NULL,
  transfer_status smallint NOT NULL DEFAULT 0,
  transfer_result varchar(64) DEFAULT NULL,
  transfer_date timestamp DEFAULT NULL,
  transfer_ext text DEFAULT NULL,
  result varchar(64) DEFAULT NULL
);
CREATE INDEX idx_pre_settle_uid ON pre_settle (uid);
CREATE INDEX idx_pre_settle_batch ON pre_settle (batch);
CREATE INDEX idx_pre_settle_transfer_no ON pre_settle (transfer_no);

DROP TABLE IF EXISTS pre_log;
CREATE TABLE pre_log (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  uid integer NOT NULL DEFAULT 0,
  type varchar(20) NULL,
  date timestamp NOT NULL,
  ip varchar(50) DEFAULT NULL,
  city varchar(20) DEFAULT NULL,
  data text NULL
);
CREATE INDEX idx_pre_log_logincheck ON pre_log (ip, date, uid);

DROP TABLE IF EXISTS pre_record;
CREATE TABLE pre_record (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  uid integer NOT NULL,
  action smallint NOT NULL DEFAULT 0,
  money numeric(10,2) NOT NULL,
  oldmoney numeric(10,2) NOT NULL,
  newmoney numeric(10,2) NOT NULL,
  type varchar(20) DEFAULT NULL,
  trade_no varchar(64) DEFAULT NULL,
  date timestamp NOT NULL
);
CREATE INDEX idx_pre_record_uid ON pre_record (uid);
CREATE INDEX idx_pre_record_trade_no ON pre_record (trade_no);

DROP TABLE IF EXISTS pre_batch;
CREATE TABLE pre_batch (
  batch varchar(20) NOT NULL,
  allmoney numeric(10,2) NOT NULL,
  count integer NOT NULL DEFAULT 0,
  time timestamp DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (batch)
);

DROP TABLE IF EXISTS pre_regcode;
CREATE TABLE pre_regcode (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  uid integer NOT NULL DEFAULT 0,
  scene varchar(20) NOT NULL DEFAULT '',
  type smallint NOT NULL DEFAULT 0,
  code varchar(32) NOT NULL,
  "to" varchar(32) DEFAULT NULL,
  time integer NOT NULL,
  ip varchar(50) DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  errcount integer NOT NULL DEFAULT 0
);
CREATE INDEX idx_pre_regcode_code ON pre_regcode ("to", type);

DROP TABLE IF EXISTS pre_risk;
CREATE TABLE pre_risk (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  uid integer NOT NULL DEFAULT 0,
  type smallint NOT NULL DEFAULT 0,
  url varchar(64) DEFAULT NULL,
  content varchar(64) DEFAULT NULL,
  date timestamp DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0
);
CREATE INDEX idx_pre_risk_uid ON pre_risk (uid);

DROP TABLE IF EXISTS pre_domain;
CREATE TABLE pre_domain (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  uid integer NOT NULL DEFAULT 0,
  domain varchar(128) NOT NULL,
  status smallint NOT NULL DEFAULT 0,
  addtime timestamp DEFAULT NULL,
  endtime timestamp DEFAULT NULL
);
CREATE INDEX idx_pre_domain_domain ON pre_domain (domain, uid);

DROP TABLE IF EXISTS pre_psreceiver;
CREATE TABLE pre_psreceiver (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  channel integer NOT NULL,
  subchannel integer DEFAULT NULL,
  mode smallint NOT NULL DEFAULT 0,
  uid integer DEFAULT NULL,
  account varchar(128) NOT NULL,
  name varchar(50) DEFAULT NULL,
  rate varchar(10) DEFAULT NULL,
  minmoney varchar(10) DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  addtime timestamp DEFAULT NULL,
  info varchar(1024) DEFAULT NULL
);
CREATE INDEX idx_pre_psreceiver_channel ON pre_psreceiver (channel, uid);

DROP TABLE IF EXISTS pre_psorder;
CREATE TABLE pre_psorder (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  rid integer NOT NULL,
  trade_no char(19) NOT NULL,
  api_trade_no varchar(150) NOT NULL,
  sub_trade_no varchar(25) DEFAULT NULL,
  settle_no varchar(150) DEFAULT NULL,
  money numeric(10,2) NOT NULL,
  status smallint NOT NULL DEFAULT 0,
  result text DEFAULT NULL,
  addtime timestamp DEFAULT NULL,
  delay smallint NOT NULL DEFAULT 0,
  retry smallint NOT NULL DEFAULT 0,
  rdata text DEFAULT NULL
);
CREATE INDEX idx_pre_psorder_trade_no ON pre_psorder (trade_no);
CREATE INDEX idx_pre_psorder_addtime ON pre_psorder (addtime, delay, status);

DROP TABLE IF EXISTS pre_subchannel;
CREATE TABLE pre_subchannel (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  channel integer NOT NULL,
  uid integer NOT NULL,
  name varchar(30) NOT NULL,
  status smallint NOT NULL DEFAULT 0,
  info text DEFAULT NULL,
  addtime timestamp DEFAULT NULL,
  usetime timestamp DEFAULT NULL,
  apply_id integer DEFAULT NULL
);
CREATE INDEX idx_pre_subchannel_channel ON pre_subchannel (channel);
CREATE INDEX idx_pre_subchannel_uid ON pre_subchannel (uid);

DROP TABLE IF EXISTS pre_blacklist;
CREATE TABLE pre_blacklist (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  type smallint NOT NULL DEFAULT 0,
  content varchar(50) NOT NULL,
  addtime timestamp NOT NULL,
  endtime timestamp DEFAULT NULL,
  remark varchar(80) DEFAULT NULL,
  CONSTRAINT uk_pre_blacklist_content UNIQUE (content, type)
);

DROP TABLE IF EXISTS pre_wework;
CREATE TABLE pre_wework (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  name varchar(30) NOT NULL,
  status smallint NOT NULL DEFAULT 0,
  appid varchar(150) DEFAULT NULL,
  appsecret varchar(250) DEFAULT NULL,
  access_token varchar(300) DEFAULT NULL,
  addtime timestamp DEFAULT NULL,
  updatetime timestamp DEFAULT NULL,
  expiretime timestamp DEFAULT NULL
);

DROP TABLE IF EXISTS pre_wxkfaccount;
CREATE TABLE pre_wxkfaccount (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  wid integer NOT NULL,
  openkfid varchar(60) NOT NULL,
  url varchar(100) DEFAULT NULL,
  cursor varchar(30) DEFAULT NULL,
  name varchar(300) DEFAULT NULL,
  addtime timestamp NOT NULL,
  usetime timestamp DEFAULT NULL,
  CONSTRAINT uk_pre_wxkfaccount_openkfid UNIQUE (openkfid)
);
CREATE INDEX idx_pre_wxkfaccount_wid ON pre_wxkfaccount (wid);

DROP TABLE IF EXISTS pre_wxkflog;
CREATE TABLE pre_wxkflog (
  trade_no char(19) NOT NULL,
  aid integer NOT NULL,
  sid char(32) NOT NULL,
  payurl varchar(500) NOT NULL,
  addtime timestamp NOT NULL,
  status smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (trade_no)
);
CREATE INDEX idx_pre_wxkflog_sid ON pre_wxkflog (sid);
CREATE INDEX idx_pre_wxkflog_addtime ON pre_wxkflog (addtime);

DROP TABLE IF EXISTS pre_transfer;
CREATE TABLE pre_transfer (
  biz_no char(19) NOT NULL,
  out_biz_no varchar(150) NOT NULL DEFAULT '',
  pay_order_no varchar(80) DEFAULT NULL,
  uid integer NOT NULL,
  type varchar(10) NOT NULL,
  channel integer NOT NULL,
  account varchar(128) NOT NULL,
  username varchar(128) DEFAULT NULL,
  money numeric(10,2) NOT NULL,
  costmoney numeric(10,2) DEFAULT NULL,
  addtime timestamp DEFAULT NULL,
  paytime timestamp DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  api smallint NOT NULL DEFAULT 0,
  "desc" varchar(80) DEFAULT NULL,
  result varchar(80) DEFAULT NULL,
  ext text DEFAULT NULL,
  PRIMARY KEY (biz_no)
);
CREATE INDEX idx_pre_transfer_uid ON pre_transfer (uid);
CREATE INDEX idx_pre_transfer_out_biz_no ON pre_transfer (out_biz_no, uid);

DROP TABLE IF EXISTS pre_invitecode;
CREATE TABLE pre_invitecode (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  code varchar(40) NOT NULL,
  addtime timestamp NOT NULL,
  usetime timestamp DEFAULT NULL,
  uid integer DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0
);
CREATE INDEX idx_pre_invitecode_code ON pre_invitecode (code);

DROP TABLE IF EXISTS pre_refundorder;
CREATE TABLE pre_refundorder (
  refund_no char(19) NOT NULL,
  out_refund_no varchar(150) NOT NULL,
  trade_no char(19) NOT NULL,
  uid integer NOT NULL DEFAULT 0,
  money numeric(10,2) NOT NULL,
  reducemoney numeric(10,2) DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  addtime timestamp DEFAULT NULL,
  endtime timestamp DEFAULT NULL,
  PRIMARY KEY (refund_no)
);
CREATE INDEX idx_pre_refundorder_out_refund_no ON pre_refundorder (out_refund_no, uid);
CREATE INDEX idx_pre_refundorder_trade_no ON pre_refundorder (trade_no);

DROP TABLE IF EXISTS pre_suborder;
CREATE TABLE pre_suborder (
  sub_trade_no varchar(25) NOT NULL,
  trade_no char(19) NOT NULL,
  api_trade_no varchar(150) DEFAULT NULL,
  money numeric(10,2) NOT NULL,
  refundmoney numeric(10,2) DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  settle smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (sub_trade_no)
);
CREATE INDEX idx_pre_suborder_trade_no ON pre_suborder (trade_no);

DROP TABLE IF EXISTS pre_complain;
CREATE TABLE pre_complain (
  id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  paytype integer NOT NULL DEFAULT 0,
  channel integer NOT NULL DEFAULT 0,
  subchannel integer NOT NULL DEFAULT 0,
  uid integer NOT NULL DEFAULT 0,
  trade_no char(19) NOT NULL DEFAULT '',
  thirdid varchar(150) NOT NULL DEFAULT '',
  thirdmchid varchar(100) DEFAULT NULL,
  type varchar(64) DEFAULT NULL,
  title varchar(255) DEFAULT NULL,
  content text DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  phone varchar(32) DEFAULT NULL,
  addtime timestamp DEFAULT NULL,
  edittime timestamp DEFAULT NULL,
  source smallint NOT NULL DEFAULT 0
);
CREATE INDEX idx_pre_complain_uid ON pre_complain (uid);
CREATE INDEX idx_pre_complain_trade_no ON pre_complain (trade_no);
