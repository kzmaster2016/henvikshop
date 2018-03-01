<?php
/**
 * 
 */
namespace Mobile\Controller;
use Home\Logic\UsersLogic;

class LoginApiController extends MobileBaseController {
    public $config;
    public $oauth;
    public $class_obj;

    public function __construct(){
        parent::__construct();
        $this->oauth = I('get.oauth');
        //获取配置
        $data = M('Plugin')->where("code='{$this->oauth}' and  type = 'login' ")->find();
        $this->config = unserialize($data['config_value']); // 配置反序列化
        if(!$this->oauth)
            $this->error('非法操作',U('Home/User/login'));
        include_once  "plugins/login/{$this->oauth}/{$this->oauth}.class.php";
        $class = '\\'.$this->oauth; //
        $login = new $class($this->config); //实例化对应的登陆插件
        $this->class_obj = $login;
    }

    public function login(){
        if(!$this->oauth)
            $this->error('非法操作',U('Home/User/login'));
        include_once  "plugins/login/{$this->oauth}/{$this->oauth}.class.php";
        $this->class_obj->login();
    }

    public function callback(){
        $data = $this->class_obj->respon();
        $logic = new UsersLogic();
        $data = $logic->thirdLogin($data);
        if($data['status'] != 1)
            $this->error($data['msg']);
        session('user',$data['result']);
        // 登录后将购物车的商品的 user_id 改为当前登录的id            
        M('cart')->where("session_id = '{$this->session_id}'")->save(array('user_id'=>$data['result']['user_id']));
        $this->success('登陆成功',U('Mobile/User/index'));
    }
}