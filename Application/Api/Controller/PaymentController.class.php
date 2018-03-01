<?php
/**
 * 
 */ 
namespace Api\Controller;
use Think\Controller;
use Api\Logic\GoodsLogic;
use Think\Page;
class PaymentController extends BaseController {
        
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();                
    }

    /**
     * app端发起支付宝,支付宝返回服务器端,  返回到这里
     * http://www.tp-shop.cn/index.php/Api/Payment/alipayNotify
     */
    public function alipayNotify(){
             
        $paymentPlugin = M('Plugin')->where("code='alipay' and  type = 'payment' ")->find(); // 找到支付插件的配置
        $config_value = unserialize($paymentPlugin['config_value']); // 配置反序列化        
        
        require_once("plugins/payment/alipay/app_notify/alipay.config.php");
        require_once("plugins/payment/alipay/app_notify/lib/alipay_notify.class.php");
        
        $alipay_config['partner'] = $config_value['alipay_partner'];//合作身份者id，以2088开头的16位纯数字        
     
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();                        
        //验证成功
        if($verify_result) 
        {                           
                $order_sn = $out_trade_no = trim($_POST['out_trade_no']); //商户订单号
                $trade_no = $_POST['trade_no'];//支付宝交易号
                $trade_status = $_POST['trade_status'];//交易状态
            

            if($_POST['trade_status'] == 'TRADE_FINISHED') 
            {            
                update_pay_status($order_sn); // 修改订单支付状态                
            }
            else if ($_POST['trade_status'] == 'TRADE_SUCCESS') 
            {            
                update_pay_status($order_sn); // 修改订单支付状态                
            }               
            M('order')->where("order_sn = '$order_sn'")->save(array('pay_code'=>'alipay','pay_name'=>'app支付宝'));

            echo "success"; //  告诉支付宝支付成功 请不要修改或删除               
        }
        else 
        {                
            echo "fail"; //验证失败         
        }
    }
 
}
