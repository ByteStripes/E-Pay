<?php
namespace lib;

class Order
{
    public static function freeze($trade_no){
        global $DB;
        $row = $DB->find('order', 'uid,getmoney,status,channel', ['trade_no'=>$trade_no]);
        if(!$row)
            return ['code'=>-1, 'msg'=>'当前订单不存在！'];
        if($row['status']!=1)
            return ['code'=>-1, 'msg'=>'只支持冻结已支付状态的订单'];
        $channel = \lib\Channel::get($row['channel']);
        if($channel['mode']==1)
            return ['code'=>-1, 'msg'=>'当前支付通道为商户直清，不支持冻结'];
        if(!$DB->exec("UPDATE pre_order SET status=3 WHERE trade_no=:trade_no AND status=1", [':trade_no'=>$trade_no])){
            return ['code'=>-1, 'msg'=>'订单状态已变更，请勿重复冻结'];
        }
        if($row['getmoney']>0){
            changeUserMoney($row['uid'], $row['getmoney'], false, '订单冻结', $trade_no);
        }
        return ['code'=>0, 'msg'=>'已成功从UID:'.$row['uid'].'冻结'.$row['getmoney'].'元余额'];
    }

    public static function unfreeze($trade_no){
        global $DB;
        $row = $DB->find('order', 'uid,getmoney,status,channel', ['trade_no'=>$trade_no]);
        if(!$row)
            return ['code'=>-1, 'msg'=>'当前订单不存在！'];
        if($row['status']!=3)
            return ['code'=>-1, 'msg'=>'只支持解冻已冻结状态的订单'];
        $channel = \lib\Channel::get($row['channel']);
        if($channel['mode']==1)
            return ['code'=>-1, 'msg'=>'当前支付通道为商户直清，不支持冻结'];
        if(!$DB->exec("UPDATE pre_order SET status=1 WHERE trade_no=:trade_no AND status=3", [':trade_no'=>$trade_no])){
            return ['code'=>-1, 'msg'=>'订单状态已变更，请勿重复解冻'];
        }
        if($row['getmoney']>0){
            changeUserMoney($row['uid'], $row['getmoney'], true, '订单解冻', $trade_no);
        }
        return ['code'=>0, 'msg'=>'已成功为UID:'.$row['uid'].'恢复'.$row['getmoney'].'元余额'];
    }

    public static function refund_info($trade_no, $api = 0, $uid = 0){
        global $DB;
        $where = ['trade_no'=>$trade_no];
        if($uid > 0) $where['uid'] = $uid;
        $order = $DB->find('order', '*', $where);
        if(!$order)
            return ['code'=>-1, 'msg'=>'当前订单不存在！'];
        if(!in_array($order['status'], [1,2,3]))
            return ['code'=>-1, 'msg'=>'该订单状态不支持退款！'];
        if($order['status'] == 2 && empty($order['refundmoney'])) return ['code'=>-1, 'msg'=>'该订单已退款！'];
        if($order['status'] == 2 && $order['refundmoney'] > 0 && $order['refundmoney'] >= $order['realmoney']) return ['code'=>-1, 'msg'=>'该订单已全额退款！'];
        $money = !empty($order['refundmoney']) ? round($order['realmoney'] - $order['refundmoney'], 2) : $order['realmoney'];

        if($api==1){
            if(!$order['api_trade_no']) return ['code'=>-1, 'msg'=>'接口订单号不存在'];
            $channel = \lib\Channel::get($order['channel']);
            if(!$channel) return ['code'=>-1, 'msg'=>'当前支付通道信息不存在'];
            if(\lib\Plugin::isrefund($channel['plugin'])==false){
                return ['code'=>-1, 'msg'=>'当前支付通道不支持API退款'];
            }
        }

        return ['code'=>0, 'money'=>$money];
    }

    public static function refund($refund_no, $trade_no, $money, $api = 0, $uid = 0, $out_refund_no = null, $skipBalance = false){
        global $DB, $order, $conf;

        $DB->beginTransaction();
        $sql = "SELECT * FROM pre_order WHERE trade_no=:trade_no";
        $bind = [':trade_no'=>$trade_no];
        if($uid > 0){
            $sql .= " AND uid=:uid";
            $bind[':uid'] = $uid;
        }
        $sql .= " LIMIT 1 FOR UPDATE";
        $order = $DB->getRow($sql, $bind);
        if(!$order){
            $DB->rollBack();
            return ['code'=>-1, 'msg'=>'当前订单不存在！'];
        }
        if(!in_array($order['status'], [1,2,3])){
            $DB->rollBack();
            return ['code'=>-1, 'msg'=>'该订单状态不支持退款！'];
        }
        if(empty($money)) $money = $order['realmoney'];
        $money = round((float)$money, 2);
        if($money <= 0 || $money > $order['realmoney']){
            $DB->rollBack();
            return ['code'=>-1, 'msg'=>'退款金额不能大于订单金额'];
        }
        if(!$order['api_trade_no']){
            $DB->rollBack();
            return ['code'=>-1, 'msg'=>'接口订单号不存在'];
        }
        $refunded = round((float)$order['refundmoney'], 2);
        if($order['status'] == 2 && $refunded <= 0){
            $DB->rollBack();
            return ['code'=>-1, 'msg'=>'该订单已退款！'];
        }
        $remaining = $refunded > 0 ? round($order['realmoney'] - $refunded, 2) : round((float)$order['realmoney'], 2);
        if($money > $remaining){
            $DB->rollBack();
            return ['code'=>-1, 'msg'=>'退款金额不能超过该订单剩余可退款金额！'];
        }

        if(!$out_refund_no) $out_refund_no = $refund_no;

        $mode = $DB->findColumn('channel', 'mode', ['id'=>$order['channel']]);
        if($skipBalance || $order['status'] == 3 || $mode == 1){
            $reducemoney = 0;
        }elseif($conf['refund_fee_type']==1 && moneyEquals($money, $order['realmoney'])){
            $reducemoney = $order['realmoney'];
        }elseif(!$conf['refund_fee_type'] && (moneyEquals($money, $order['realmoney']) || $money >= $order['getmoney'])){
            $reducemoney = $order['getmoney'];
        }else{
            $reducemoney = $money;
        }

        if($order['tid'] == 2){
            $param = json_decode($order['param'], true);
            if(isset($param['uid'])){
                $order['uid'] = $param['uid'];
            }
        }

        if($reducemoney > 0){
            $deducted = changeUserMoney($order['uid'], $reducemoney, false, '订单退款', $trade_no, false);
            if($deducted === false){
                $DB->rollBack();
                return ['code'=>-1, 'msg'=>'商户余额不足，请先充值'];
            }
        }
        $refundmoney = round($refunded + $money, 2);
        $DB->update('order', ['refundmoney'=>$refundmoney], ['trade_no'=>$trade_no]);
        $DB->commit();

        if($api == 1){
            $message = null;
            if(!\lib\Plugin::refund($refund_no, $trade_no, $money, $message)){
                if($reducemoney > 0){
                    changeUserMoney($order['uid'], $reducemoney, true, '订单退款回退', $trade_no);
                }
                $DB->update('order', ['refundmoney'=>$refunded > 0 ? $refunded : null], ['trade_no'=>$trade_no]);
                return ['code'=>-1, 'msg'=>'退款失败：'.$message];
            }
        }
        if($mode == 1 && !$conf['refund_fee_type'] && $refundmoney >= $order['realmoney']){
            $record = $DB->getRow("SELECT * FROM pre_record WHERE trade_no=:trade_no AND (type='订单服务费' OR type='在线收款服务费') LIMIT 1", [':trade_no'=>$trade_no]);
            if($record){
                changeUserMoney($order['uid'], $record['money'], true, '服务费退款', $trade_no);
            }
        }

        if($api == 1){
            $DB->update('order', ['status'=>2], ['trade_no'=>$trade_no]);
            $DB->insert('refundorder', ['refund_no'=>$refund_no, 'out_refund_no'=>$out_refund_no, 'trade_no'=>$trade_no, 'uid'=>$order['uid'], 'money'=>$money, 'reducemoney'=>$reducemoney, 'addtime'=>date('Y-m-d H:i:s'), 'endtime'=>date('Y-m-d H:i:s'), 'status'=>1]);
        }else{
            $DB->update('order', ['status'=>2], ['trade_no'=>$trade_no]);
        }

        self::clawbackInvite($trade_no, $money, $order['realmoney'], $refundmoney);

        return ['code'=>0, 'refund_no'=>$refund_no, 'out_refund_no'=>$out_refund_no, 'trade_no'=>$trade_no, 'out_trade_no'=>$order['out_trade_no'], 'uid'=>$order['uid'], 'money'=>$money, 'reducemoney'=>$reducemoney];
    }

    private static function clawbackInvite($tradeNo, $refundMoney, $orderMoney, $totalRefunded){
        global $DB;
        $orderMoney = round((float)$orderMoney, 2);
        $refundMoney = round((float)$refundMoney, 2);
        if($orderMoney <= 0 || $refundMoney <= 0) return;
        $rows = $DB->getAll("SELECT uid, SUM(money) money FROM pre_record WHERE trade_no=:trade_no AND type='邀请返现' AND action=1 GROUP BY uid", [':trade_no'=>$tradeNo]);
        if(!$rows) return;
        foreach($rows as $row){
            $original = round((float)$row['money'], 2);
            if($original <= 0) continue;
            $already = round((float)$DB->getColumn("SELECT SUM(money) FROM pre_record WHERE trade_no=:trade_no AND uid=:uid AND type='邀请返现追回' AND action=2", [':trade_no'=>$tradeNo, ':uid'=>$row['uid']]), 2);
            $left = round($original - $already, 2);
            if($left <= 0) continue;
            if(moneyEquals($totalRefunded, $orderMoney)){
                $need = $left;
            }else{
                $share = round($original * $refundMoney / $orderMoney, 2);
                $need = $share > $left ? $left : $share;
            }
            if($need <= 0) continue;
            $ok = changeUserMoney($row['uid'], $need, false, '邀请返现追回', $tradeNo);
            if($ok !== false) continue;
            $balance = round((float)$DB->getColumn("SELECT money FROM pre_user WHERE uid=:uid LIMIT 1", [':uid'=>$row['uid']]), 2);
            $part = $balance > $need ? $need : $balance;
            if($part > 0){
                changeUserMoney($row['uid'], $part, false, '邀请返现追回', $tradeNo);
            }else{
                $part = 0;
            }
            $short = round($need - $part, 2);
            if($short > 0){
                $DB->exec("INSERT INTO `pre_risk` (`uid`, `type`, `content`, `date`) VALUES (:uid, 4, :content, NOW())", [':uid'=>$row['uid'], ':content'=>'邀请返现待追偿'.$short.'元，关联订单'.$tradeNo]);
            }
        }
    }

    public static function close($trade_no, $uid = 0){
        global $DB, $order, $conf;

        $where = ['trade_no'=>$trade_no];
        if($uid > 0) $where['uid'] = $uid;
        $order = $DB->find('order', '*', $where);
        if(!$order)
            return ['code'=>-1, 'msg'=>'当前订单不存在！'];
        if($order['status'] != 0)
            return ['code'=>-1, 'msg'=>'该订单状态不支持关闭！'];

        if(!\lib\Plugin::close($trade_no, $message)){
            return ['code'=>-1, 'msg'=>$message];
        }
        return ['code'=>0];
    }
}