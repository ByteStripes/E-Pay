CREATE TABLE IF NOT EXISTS pre_suborder (
  sub_trade_no varchar(25) NOT NULL,
  trade_no char(19) NOT NULL,
  api_trade_no varchar(150) DEFAULT NULL,
  money numeric(10,2) NOT NULL,
  refundmoney numeric(10,2) DEFAULT NULL,
  status smallint NOT NULL DEFAULT 0,
  settle smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (sub_trade_no)
);
CREATE INDEX IF NOT EXISTS idx_pre_suborder_trade_no ON pre_suborder (trade_no);

ALTER TABLE pre_order ADD COLUMN IF NOT EXISTS bill_mch_trade_no varchar(150) DEFAULT NULL;
CREATE INDEX IF NOT EXISTS idx_pre_order_bill_mch_trade_no ON pre_order (bill_mch_trade_no);

ALTER TABLE pre_transfer ADD COLUMN IF NOT EXISTS out_biz_no varchar(150) NOT NULL DEFAULT '';
CREATE INDEX IF NOT EXISTS idx_pre_transfer_out_biz_no ON pre_transfer (out_biz_no, uid);

ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS voice_devid varchar(30) DEFAULT NULL;
ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS voice_order smallint NOT NULL DEFAULT 0;
ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS device_secret varchar(64) DEFAULT NULL;
ALTER TABLE pre_user ALTER COLUMN pwd TYPE varchar(255);

ALTER TABLE pre_psreceiver ADD COLUMN IF NOT EXISTS info varchar(1024) DEFAULT NULL;
ALTER TABLE pre_psorder ADD COLUMN IF NOT EXISTS rdata text DEFAULT NULL;
ALTER TABLE pre_psorder ADD COLUMN IF NOT EXISTS sub_trade_no varchar(25) DEFAULT NULL;
ALTER TABLE pre_user ALTER COLUMN msgconfig TYPE text;

ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS pay_maxmoney varchar(10) DEFAULT NULL;
ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS pay_minmoney varchar(10) DEFAULT NULL;
ALTER TABLE pre_group ADD COLUMN IF NOT EXISTS index integer NOT NULL DEFAULT 0;
ALTER TABLE pre_channel ADD COLUMN IF NOT EXISTS daymaxorder integer DEFAULT 0;
ALTER TABLE pre_psorder ADD COLUMN IF NOT EXISTS retry smallint NOT NULL DEFAULT 0;
ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS wxa_uid varchar(32) DEFAULT NULL;
ALTER TABLE pre_channel ADD COLUMN IF NOT EXISTS timestart integer DEFAULT NULL;
ALTER TABLE pre_channel ADD COLUMN IF NOT EXISTS timestop integer DEFAULT NULL;
ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS print_order smallint NOT NULL DEFAULT 0;
ALTER TABLE pre_user ADD COLUMN IF NOT EXISTS print_config varchar(300) DEFAULT NULL;
ALTER TABLE pre_order ADD COLUMN IF NOT EXISTS cert_info varchar(300) DEFAULT NULL;
ALTER TABLE pre_order ADD COLUMN IF NOT EXISTS province varchar(2) DEFAULT NULL;
ALTER TABLE pre_psreceiver ADD COLUMN IF NOT EXISTS mode smallint NOT NULL DEFAULT 0;

INSERT INTO pre_type (id, name, device, showname, status) VALUES (7, 'douyinpay', 0, '抖音支付', 0) ON CONFLICT (id) DO NOTHING;
