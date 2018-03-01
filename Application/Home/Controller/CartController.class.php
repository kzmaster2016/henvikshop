<?php
/**
 *
 */ 
namespace Home\Controller;

class CartController extends BaseController {
    
    public $cartLogic; // 购物车逻辑操作类
    public $user_id = 0;
    public $user = array();    
    /**
     * 初始化函数
     */
    public function _initialize() {       
        parent::_initialize();
        $this->cartLogic = new \Home\Logic\CartLogic();
        
        if(session('?user'))
        {
        	$user = session('user');
            $user = M('users')->where("user_id = {$user['user_id']}")->find();
            session('user',$user);  //覆盖session 中的 user
        	$this->user = $user;
        	$this->user_id = $user['user_id'];
        	$this->assign('user',$user); //存储用户信息
                // 给用户计算会员价 登录前后不一样
            if($user){
                $user[discount] = (empty($user[discount])) ? 1 : $user[discount];
                M('Cart')->execute("update `__PREFIX__cart` set member_goods_price = goods_price * {$user[discount]} where (user_id ={$user[user_id]} or session_id = '{$this->session_id}') and prom_type = 0");
            }                    
        }                        
    }

    public function cart(){  
        $this->ajaxCartList();   
        $this->display();
    }
    
    public function index(){
        $this->ajaxCartList(); 
    	$this->display('cart');
    }

    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart()
    {
        $goods_id = I("goods_id"); // 商品id
        $goods_num = I("goods_num");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格            
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车                     
        exit(json_encode($result));        
    }
    
    /**
     * ajax 删除购物车的商品
     */
    public function ajaxDelCart()
    {       
        $ids = I("ids"); // 商品 ids        
        $result = M("Cart")->where(" id in ($ids)")->delete();
        $return_arr = array('status'=>1,'msg'=>'delete success!','result'=>''); // 返回结果状态       
        exit(json_encode($return_arr));
    }
    
    
    /*
     * ajax 请求获取购物车列表
     */
    public function ajaxCartList()
    {    
        $post_goods_num = I("goods_num"); // goods_num 购物车商品数量
        $post_cart_select = I("cart_select"); // 购物车选中状态

        $where = "session_id = '$this->session_id' "; // 默认按照 session_id 查询

        $this->user_id && $where = " user_id = ".$this->user_id; // 如果这个用户已经登录了则按照用户id查询
        
        $cartList = M('Cart')->where($where)->getField("id,goods_num,selected,prom_type,prom_id"); 
        
        if($post_goods_num)
        {
            // 修改购物车数量 和勾选状态
            foreach($post_goods_num as $key => $val)
            {   
                $data['goods_num'] = $val < 1 ? 1 : $val;
                
                if($cartList[$key]['prom_type'] == 1) //限时抢购 不能超过购买数量
                {
                    $flash_sale = M('flash_sale')->where("id = {$cartList[$key]['prom_id']}")->find();
                    $data['goods_num'] = $data['goods_num'] > $flash_sale['buy_limit'] ? $flash_sale['buy_limit'] : $data['goods_num'];
                }
                
                $data['selected'] = $post_cart_select[$key] ? 1 : 0 ;                               
                if(($cartList[$key]['goods_num'] != $data['goods_num']) || ($cartList[$key]['selected'] != $data['selected'])) 
                    M('Cart')->where("id = $key")->save($data);
            }
            $this->assign('select_all', $_POST['select_all']); // 全选框
        }
                
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 选中的商品        
        if(empty($result['total_price']))
            $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0);
        
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $result['total_price']); // 总计

        if (IS_AJAX) {
            $this->display('ajax_cart_list');
        }
        
    }
    /**
     * 购物车第二步确定页面
     */
    public function cart2()
    {        
        
        if($this->user_id == 0)
            $this->error('Please login first',U('Home/User/login'));
        
        if($this->cartLogic->cart_count($this->user_id,1) == 0 ) 
            $this->error ('The cart is empty!','Cart/cart');
        
        $result = $this->cartLogic->cartList($this->user, $this->session_id,1,1); // 获取购物车商品

        $shippingList = M('Plugin')->where("`type` = 'shipping' and status = 1")->select();// 物流公司                
        
        $Model = new \Think\Model(); // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的               
        $sql = "select c1.name,c1.money,c1.condition, c2.* from __PREFIX__coupon as c1 inner join __PREFIX__coupon_list as c2  on c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0  where c2.uid = {$this->user_id}  and ".time()." < c1.use_end_time and c1.condition <= {$result['total_price']['total_fee']}";
        $couponList = $Model->query($sql);
               
        $this->assign('couponList', $couponList); // 优惠券列表
        $this->assign('shippingList', $shippingList); // 物流公司
        $this->assign('cartList', $result['cartList']); // 购物车的商品                
        $this->assign('total_price', $result['total_price']); // 总计                               
        $this->display();
    }
   
    /*
     * ajax 获取用户收货地址 用于购物车确认订单页面
     */
    public function ajaxAddress(){
        $model = M('UserAddress');
        $address_list = $model->where('user_id = '.$this->user_id.' AND is_pickup = 0')->select();
        if($address_list){
        	$area_id = array();
        	foreach ($address_list as $val){
        		$area_id[] = $val['country'];
                $area_id[] = $val['city'];
                $area_id[] = $val['district'];
                $area_id[] = $val['twon'];                        
        	}    
            $area_id = array_filter($area_id);
        	$area_id = implode(',', $area_id);
        	$regionList = M('region')->where("id in ($area_id)")->getField('id,name');
        	$this->assign('regionList', $regionList);
        }
        $address_where['is_default'] = 1;
        $c = $model->where('user_id = '.$this->user_id.' AND is_default=1 AND is_pickup = 0')->count(); // 看看有没默认收货地址
        // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
        if((count($address_list) > 0) && ($c == 0)) {
            $address_list[0]['is_default'] = 1;
        }                
        $this->assign('address_list', $address_list);
        $this->display('ajax_address');
    }

    public function test(){
        $user_id = 18991;
        echo crc32($user_id);
    }

    /**
     * @author dyr
     * @time 2016.08.22
     * 获取自提点信息
     */
    public function ajaxPickup()
    {
        $address_list = D('UserAddress')->getUserPickup($this->user_id);
        $province_id = I('province_id');
        $city_id = I('city_id');
        $district_id = I('district_id');
        if (empty($province_id) || empty($city_id) || empty($district_id)) {
            exit("<script>alert('参数错误');</script>");
        }
        $pickup_list = D('Pickup')->getPickupItemByPCD($province_id, $city_id, $district_id);
        $this->assign('pickup_list', $pickup_list);
        $this->assign('address_list', $address_list);
        $this->display('ajax_pickup');
    }

    /**
     * @author dyr
     * @time 2016.08.22
     * 更换自提点
     */
    public function replace_pickup()
    {
        $province_id = I('get.province_id');
        $city_id = I('get.city_id');
        $district_id = I('get.district_id');
        $region_model = M('region');
        $call_back = I('get.call_back');
        if (IS_POST) {
            echo "<script>parent.{$call_back}('success');</script>";
            exit(); // 成功
        }
        $address = array('province' => $province_id, 'city' => $city_id, 'district' => $district_id);
        $p = $region_model->where(array('parent_id' => 0, 'level' => 1))->select();
        $c = $region_model->where(array('parent_id' => $province_id, 'level' => 2))->select();
        $d = $region_model->where(array('parent_id' => $city_id, 'level' => 3))->select();
        $this->assign('province', $p);
        $this->assign('city', $c);
        $this->assign('district', $d);
        $this->assign('address', $address);
        $this->assign('call_back', $call_back);
        $this->display();
    }

    /**
     * @author dyr
     * @time 2016.08.22
     * 更换自提点
     */
    public function ajax_PickupPoint()
    {
        $province_id = I('province_id');
        $city_id = I('city_id');
        $district_id = I('district_id');
        $pick_up_model = D('Pickup');
        $pick_up_list = $pick_up_model->getPickupListByPCD($province_id,$city_id,$district_id);
        exit(json_encode($pick_up_list));
    }


    /**
     * ajax 获取订单商品价格 或者提交 订单
     */
    public function cart3(){
                                
        if($this->user_id == 0)
            exit(json_encode(array('status'=>-100,'msg'=>"Login timeout,!",'result'=>null))); // 返回结果状态
        
        $address_id = I("address_id"); //  收货地址id
        $shipping_code =  I("shipping_code"); //  物流编号

        // $invoice_title = I('invoice_title'); // 发票
        // $user_money =  I("user_money",0); //  使用余额        
        // $user_money = $user_money ? $user_money : 0;
        
        $couponTypeSelect =  I("couponTypeSelect"); // 优惠券类型  1 下拉框选择优惠券 2 输入框输入优惠券代码
        if ($couponTypeSelect=='1') {
            $coupon_id =  I("coupon_id"); //  优惠券id
            $couponCode ='';
        }else{
            $couponCode =  I("couponCode"); //  优惠券代码
            $coupon_id ='';
        }
        $pay_points =  I("pay_points",0); //  使用积分
        

        if($this->cartLogic->cart_count($this->user_id,1) == 0 ) exit(json_encode(array('status'=>-2,'msg'=>'There is not selected goods in your shopping cart!','result'=>null))); // 返回结果状态

        if(!$address_id) exit(json_encode(array('status'=>-3,'msg'=>'Please fill in the information of the consignee first!','result'=>null)));
		
		$address = M('UserAddress')->where("address_id = $address_id")->find();
		$order_goods = M('cart')->where("user_id = {$this->user_id} and selected = 1")->select();

        $result = calculate_price($this->user_id,$order_goods,$shipping_code,0,$address[province],$address[city],$address[district],$pay_points,$user_money,$coupon_id,$couponCode);
                
		if($result['status'] < 0)	
			exit(json_encode($result));      	
	   // 订单满额优惠活动		                
        $order_prom = get_order_promotion($result['result']['order_amount']);

        $result['result']['order_amount'] = $order_prom['order_amount'] ; // 应付金额
        $result['result']['order_prom_id'] = $order_prom['order_prom_id'] ;  
        $result['result']['order_prom_amount'] = $order_prom['order_prom_amount']; //订单 促销活动
        
        $car_price = array(
            'postFee'      => $result['result']['shipping_price'], // 物流费
            'couponFee'    => $result['result']['coupon_price'], // 优惠券            
            // 'balance'      => $result['result']['user_money'], // 使用用户余额
            'pointsFee'    => $result['result']['integral_money'], // 积分支付            
            'payables'     => $result['result']['order_amount'], // 应付金额
            'goodsFee'     => $result['result']['goods_price'],// 商品价格            
            'order_prom_id' => $result['result']['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['result']['order_prom_amount'], // 订单优惠活动优惠了多少钱
        );
       
        // 提交订单        
        if($_REQUEST['act'] == 'submit_order')
        {  
            if(empty($coupon_id) && !empty($couponCode))
               $coupon_id = M('CouponList')->where("`code`='$couponCode'")->getField('id');                    
            $result = $this->cartLogic->addOrder($this->user_id,$address_id,$shipping_code,$invoice_title,$coupon_id,$car_price); // 添加订单                        
            exit(json_encode($result));            
        }
            $return_arr = array('status'=>1,'msg'=>'Calculate is ok!','result'=>$car_price); // 返回结果状态
            exit(json_encode($return_arr));           
    }	
    /**
     * ajax 获取订单商品价格 或者提交 订单
	 * 已经用心方法 这个方法 cart9  准备作废
     */
   
    /*
     * 订单支付页面
     */
    public function cart4(){
        $order_id = I('order_id');        
        $order = M('Order')->where("order_id = $order_id")->find();
        // 如果已经支付过的订单直接到订单详情页面. 不再进入支付页面
        if($order['pay_status'] == 1){
            $order_detail_url = U("Home/User/order_detail",array('id'=>$order_id));
            header("Location: $order_detail_url");
        }      
        
        $paymentList = M('Plugin')->where("`type`='payment' and status = 1 and  scene in(0,2)")->select();                        
        $paymentList = convert_arr_key($paymentList, 'code');
        
        foreach($paymentList as $key => $val)
        {
            $val['config_value'] = unserialize($val['config_value']);            
            if($val['config_value']['is_bank'] == 2)
            {
                $bankCodeList[$val['code']] = unserialize($val['bank_code']);        
            }                
        }                
        
        $bank_img = include 'Application/Home/Conf/bank.php'; // 银行对应图片             
        $this->assign('paymentList',$paymentList);        
        $this->assign('bank_img',$bank_img);
        $this->assign('order',$order);
        $this->assign('bankCodeList',$bankCodeList);        
        $this->assign('pay_date',date('Y-m-d', strtotime("+1 day")));
        $this->display();                   
    }
 
    
    //ajax 请求购物车列表
    public function header_cart_list()
    {
    	$cart_result = $this->cartLogic->cartList($this->user, $this->session_id,0,1);
    	if(empty($cart_result['total_price'])){
    		$cart_result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0);
        }	
    	$this->assign('cartList', $cart_result['cartList']); // 购物车的商品
    	$this->assign('cart_total_price', $cart_result['total_price']); // 总计
        $template = I('template','header_cart_list');    	 
        $this->display($template);		 
    }

    /**
    *改变订单支付状态
    *
   */
   public function payOrder($order_sn){
        // 如果这笔订单已经处理过了
        $count = M('order')->where("order_sn = '$order_sn' and pay_status = 0")->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
        if($count == 0) return false;
        // 找出对应的订单
        // 修改支付状态  已支付
        M('order')->where("order_sn = '$order_sn'")->save(array('pay_status'=>1,'pay_time'=>time()));

        $result=array('status'=>1,'msg'=>'Payment is ok!','result'=>array());
        exit(json_encode($result));
   }
}
