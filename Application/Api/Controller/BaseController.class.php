<?php
/**
 * 
 */ 
namespace Api\Controller;
use Think\Controller;
class BaseController extends Controller {
    public $http_url;
    public $user = array();
    public $user_id = 0;    
    public $token = '';
    /**
     * 析构函数
     */
    function __construct() {       
        parent::__construct();
        if($_REQUEST['test'] == '1')
        {
            $test_str = 'POST'.print_r($_POST,true);
            $test_str .= 'GET'.print_r($_GET,true);
            file_put_contents('a.html', $test_str);            
        }    
        
        $this->checkToken(); // 检查token
   
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        define('SESSION_ID',$unique_id); //将当前的session_id保存为常量，供其它方法调用                

   }    
    
    /*
     * 初始化操作
     */
    public function _initialize() {
                
        $local_sign = $this->getSign();
        $api_secret_key = C('API_SECRET_KEY');
        
//         if('www.tp-shop.cn' == $api_secret_key)
//                exit(json_encode(array('status'=>-1,'msg'=>'请到后台修改php文件 Application/Api/Conf/config.php 文件内的秘钥','data'=>'' )));
            
        // 不参与签名验证的方法
        //@modify by wangqh. add notify
        if(!in_array(strtolower(ACTION_NAME), array('getservertime','group_list','getconfig','alipaynotify', 'notify', 'goodslist','search','goodsthumimages','login','favourite','homepage')))
        {        
            if($local_sign != $_POST['sign'])
            {    
                $json_arr = array('status'=>-1,'msg'=>'签名失败!!!','result'=>'' );
                 exit(json_encode($json_arr));

            }
            if(time() - $_POST['time'] > 600)
            {    
                $json_arr = array('status'=>-1,'msg'=>'请求超时!!!','result'=>'' );
                 exit(json_encode($json_arr));
            }
        } 

       
    }
    
    /**
     *  app 端万能接口 传递 sql 语句 sql 错误 或者查询 错误 result 都为 false 否则 返回 查询结果 或者影响行数
     */
    public function sqlApi()
    {            
        exit(json_encode(array('status'=>-1,'msg'=>'使用万能接口必须开启签名验证才安全','result'=>''))); //  开启后注释掉这行代码即可
        
        C('SHOW_ERROR_MSG',1);
            $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
            $sql = $_REQUEST['sql'];                        
            try
            {
                 if(preg_match("/insert|update|delete/i", $sql))            
                     $result = $Model->execute($sql);
                 else             
                     $result = $Model->query($sql);
             }
             catch (\Exception $e)
             {
                 $json_arr = array('status'=>-1,'msg'=>'系统错误','result'=>'');
                 $json_str = json_encode($json_arr);            
                 exit($json_str);            
             }            
                         
            if($result === false) // 数据非法或者sql语句错误            
                $json_arr = array('status'=>-1,'msg'=>'系统错误','result'=>'');
            else
                $json_arr = array('status'=>1,'msg'=>'成功!','result'=>$result);
                                   
            $json_str = json_encode($json_arr);            
            exit($json_str);            
    }

    /**
     * 获取全部地址信息
     */
    public function allAddress(){
        $data =  M('region')->where('level < 4')->select();
        $json_arr = array('status'=>1,'msg'=>'成功!','result'=>$data);
        $json_str = json_encode($json_arr);
        exit($json_str);
    }

    /**
     * app端请求签名
     * @return type
     */
    protected function getSign(){
        header("Content-type:text/html;charset=utf-8");
        $data = $_POST;        
        unset($data['time']);    // 删除这两个参数再来进行排序     
        unset($data['sign']);    // 删除这两个参数再来进行排序
        ksort($data);
        $str = implode('', $data);        
        $str = $str.$_POST['time'].C('API_SECRET_KEY');        
        return md5($str);
    }
        
    /**
     * 获取服务器时间
     */
    public function getServerTime()
    {
        $json_arr = array('status'=>1,'msg'=>'成功!','result'=>time());
        $json_str = json_encode($json_arr);
        exit($json_str);       
    }
    
    /**
     * 校验token
     */
    public function checkToken(){
        
        $this->token = I("token",''); // token
        
        //    $class_methods = get_class_methods(new \Api\Controller\UserController());        
        // 判断哪些控制器的 哪些方法需要登录验证的
        $check_arr = array(
            'payment'=>array('alipaynotify'), // 需要验证登录的方法             
            'user'=>array('userinfo','updateuserinfo','password','getaddresslist','addaddress','del_address','setdefaultaddress','getcouponlist','getgoodscollect','getorderlist','getorderdetail','cancelorder','orderconfirm','add_comment','account','return_goods_list','return_goods_info','return_goods_status','return_goods'), // 需要验证登录的方法 
            'cart'=>array('cart2','cart3'), // 需要验证登录的方法 
            'goods'=>array('collectgoods'), // 需要验证登录的方法             
            );
        
        if($this->token){
            $check_arr['cart'][] = 'cartlist';
            $check_arr['cart'][] = 'addcart';
        }
           
        $controller_name = strtolower(CONTROLLER_NAME);
        $action_name = strtolower(ACTION_NAME);                        
        
        if(in_array($controller_name,array_keys($check_arr)) && in_array($action_name, $check_arr[$controller_name]))
        {                                
                if(empty($this->token))
                    exit(json_encode(array('status'=>-100,'msg'=>'必须传递token','result'=>'')));                
                                                     
                    $this->user = M('users')->where("token = '{$this->token}'")->find();
                    if(empty($this->user))
                        exit(json_encode(array('status'=>-101,'msg'=>'token错误','result'=>'')));                                                            
                    // 登录超过72分钟 则为登录超时 需要重新登录.  //这个时间可以自己设置 可以设置为 20分钟
                    if(time() - $this->user['last_login'] > 3600000)   //3600                                          
                        exit(json_encode(array('status'=>-102,'msg'=>'登录超时,请重新登录!!!','result'=>(time() - $this->user['last_login']))));
                                        
                    $this->user_id = $this->user['user_id'];                    
                     // 更新最后一次操作时间 如果用户一直操作 则一直不超时
                    M('users')->where("user_id = {$this->user_id}")->save(array('last_login'=>time()));
                    
        
        }
        // 应app 端要求如果是查询收藏商品列表,不做判断 token判断
        elseif ($controller_name == 'user' && $action_name == 'getgoodscollect')
        {
            $this->user = M('users')->where("token = '{$this->token}'")->find();
            $this->user && ($this->user_id = $this->user['user_id']);
        }
    }
}