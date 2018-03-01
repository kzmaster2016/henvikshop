<?php
namespace Api\Controller;

use Think\Controller;

require_once("plugins/payment/braintree/lib/braintree.php");

class PayPalController extends BaseController
{
    public function _initialize()
    {
        require_once("plugins/payment/braintree/paypal.config.php");

        if(!$paypal_config['environment'] || !$paypal_config['access_token']){
            $res = array('msg'=>'没有配置paypal支付参数','status'=>-1);
            $this->ajaxReturn($res);
        }
        $this->paypal_config = $paypal_config;

        $this->gateway = new \Braintree_Gateway(array(
            'accessToken' => $paypal_config['access_token'],
        ));

    }

    /**
    *GET 方法
    *生成token给前端去初始化paypal Button
    **/
    public function generate_token($customer_id=0){
        $client_token = "";
        $res = "";

        try{
            $client_token = $this->gateway->clientToken()->generate();
            $res = array("status"=>"0","client_token"=>$client_token);
        }catch (Exception $e){
            $res = array("status"=>"-1","msg"=>$e->getMessage());
            $this->ajaxReturn($res);
        }
        $this->ajaxReturn($res);
    }

    /**
    *POST 方法
    *付款,这里的付款只是预付款,braintree需要时间去扣款.做完这一步之后,需要写一个定时任务去检查是否真的扣钱,然后去改变订单状态
    */
    public function pay_order(){
        $payment_type= I('post.payment_type');   //braintree支付方式名字, paypal或者card
        $payment_nonce = I('post.nonce');
        $order_sn = I('post.order_sn');
        //这里有必要检查参数
        $data = array(
            'pay_time' => time(),
            'pay_status' => 0,
            'pay_code' => 'braintree',
            'pay_name' => $payment_type,
            
           /* 'consignee' => I('post.first_name')." ".I('post.last_name'),
            'country' => I('post.country'),
            'province' => I('post.state'),
            'city' => I('post.city'),
            'address' => I('post.address'),
            'zipcode' => I('post.postcode'),
            'email' => I('post.email'),
            'mobile' => I('post.mobile')*/
        );

        $order = M('order')->where(array('order_sn'=>$order_sn))->find();
        if(!$order){
            $res = array('msg'=>'该订单不存在','status'=>-1);
            $this->ajaxReturn($res);
        }

        //if($order['pay_status'] != 0){   //如果已经支付或预支付,直接返回不必再支付了
        //     $res = array('msg'=>'已经支付过了','status'=>0);
        //                $this->ajaxReturn($res);
        //}

        // 获取支付金额
        $amount = $order['order_amount'];
        $amount = round($amount, 2);

        if(!$payment_nonce || !$amount){
            $res = array('msg'=>'缺少参数nonce或order_sn','status'=>-1);
            $this->ajaxReturn($res);
        }

        $sale_parameters = array(
            "amount" => $amount,  //到时改成$amount
            'merchantAccountId' => 'USD',
            "paymentMethodNonce" => $payment_nonce,
            "orderId" => $order_sn,
            "shipping" => [
              "firstName" => I('post.first_name'),
              "lastName" => I('post.last_name'),
              "company" => "Braintree",
              "streetAddress" => I('post.address'),
              "extendedAddress" => "#36",
              "locality" => I('post.city'),
              "region" => I('post.state'), 
              "postalCode" => I('post.postcode'),
              "countryCodeAlpha2" => I('post.country')
            ]
        );


        $result = $this->gateway->transaction()->sale($sale_parameters);

        $status = $result->transaction->status;
        $braintree_transaction_id = $result->transaction->id;
        $data['pay_code'] = $braintree_transaction_id;

        $payment_data = array(
            'pay_code' => $braintree_transaction_id,
            'pay_name' => $payment_type,
            'pay_desc' => 'paypal',
            'pay_status' => $status,
            'pay_config' => serialize($this->paypal_config),
            'order_sn' => $order_sn
        );

        $payment = M('payment');
        $payment->data($payment_data)->add();
        $status_code = 0;

        if($result->success){ //预支付成功
            $data['pay_status'] = 2;    //2表示预支付
            $order = M('order')->where(array('order_sn'=>$order_sn))->save($data);

        }else{ //支付失败
            $status_code = -1;
            $status = $result->message;
        }

        $res = array('msg'=>$status,'status'=>$status_code);

        if ($status_code) {
            $this->success('Pre payment is successful',U('Index/index'));
        }else{
            $this->error('Payment failure,Please try again later',U('User/order_list'));
        }
         
    }

    /**
    *GET方法
    *检查订单是否被braintree扣钱,这个方法用于查询订单是否真的被扣钱.
    */
    public function check_if_settled($order_sn){
        $map['order_sn'] = $order_sn;
        $map['pay_name'] = array('in', array("paypal","card"));
        $payment = M('payment')->where($map)->find();
        if(!$payment){
            $res = array('msg'=>'没有支付信息','status'=>-1);
            $this->ajaxReturn($res);
        }

        if($payment['pay_code']){
            $transaction = $this->gateway->transaction()->find($payment['pay_code']);
            $status = $transaction->status;
            dump($status);
            $pay_status = 0;
            if($transaction->status == \Braintree_Transaction::SETTLED){
                $pay_status = 1;
            	$res = array("status"=>0, "msg"=>"success");
            }else{
                dump($transaction->status);
            	$res = array("status"=>-1,"msg"=>"settled error");
            }
            //修改订单和支付信息状态
            M('order')->where(array('order_sn'=>$order_sn))->save(array('pay_status'=>$pay_status));
            M('payment')->where($map)->save(array('status'=> $status));
        }else{
            $res = array('msg'=>'没有pay_code','status'=>-1);
        }

        $this->ajaxReturn($res);

    }

    /**
    *GET方法
    *退款,这里需要加上管理员控制权限
    * $refund_money 退款金额
    */
    public function refund_order($order_sn, $refund_money){

        //简单的管理员控制权限
        //if(session('?admin_id') && session('admin_id')>0){
        //         $this->error("您已登录",U('Admin/Index/index'));
        //}

        $map = array();
        $map['order_sn'] = $order_sn;
        $map['pay_name'] = array('in', array("paypal","card"));
        $map['status'] = array('in', array(\Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status == \Braintree_Transaction::SETTLED, $transaction->status == \Braintree_Transaction::SETTLING));  //这些状态都能退款
        $payment = M('payment')->where($map)->find();
        if(!$payment){
            $res = array('msg'=>'没有支付信息','status'=>-1);
            $this->ajaxReturn($res);
        }

        if($payment['pay_code']){
            $result = $this->gateway->transaction()->refund($payment['pay_code'], $refund_money);
            if($result->success){
                $data = array(
                          'pay_status' => 4      //4表示已经退款
                        );
                $order = M('order')->where(array('order_sn'=>$order_sn))->save($data);
                $res = array("status"=>0,"msg"=>"success");
            }else{
                $res = array("status"=>-1,"msg"=>$result);
            }
        }else{
            $res = array('msg'=>'没有braintree_id','status'=>-1);
        }

        $this->ajaxReturn($res);
    }

     /**
     *GET方法
     *对预支付的订单进行取消支付操作(还没扣钱),这里需要加上管理员控制权限
     */
    public function void_order($order_sn){

        //简单的管理员控制权限
        //if(session('?admin_id') && session('admin_id')>0){
        //         $this->error("您已登录",U('Admin/Index/index'));
        //}

        $map = array();
        $map['order_sn'] = $order_sn;
        $map['pay_name'] = array('in', array("paypal","card"));
        $map['status'] = array('in', array($transaction->status == \Braintree_Transaction::AUTHORIZED, $transaction->status == \Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT));  //这些状态都能取消支付操作
        $payment = M('payment')->where($map)->find();
        if(!$payment){
            $res = array('msg'=>'没有完成支付(包括paypal还没扣钱这种情况)','status'=>-1);
            $this->ajaxReturn($res);
        }

        if($payment['pay_code']){
            $result = $this->gateway->transaction()->void($payment['pay_code']);
            if($result->success){
                $data = array(
                        'pay_status' => 3       //3表示已取消支付
                        );
                $order = M('order')->where(array('order_sn'=>$order_sn))->save($data);
                $res = array("status"=>0,"msg"=>"success");
            }else{
                $res = array("status"=>-1,"msg"=>$result->errors);
            }
        }else{
            $res = array('msg'=>'没有braintree_id','status'=>-1);
        }

        $this->ajaxReturn($res);
    }
}

?>