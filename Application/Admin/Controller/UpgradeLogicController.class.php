<?php

/**
 * 
 */

namespace Admin\Controller;
use Think\Controller;
use Admin\Model;
use Admin\Logic\UpgradeLogic;
class UpgradeLogicController extends Controller {

    /**
     * 析构函数
     */
    function __construct() {
        parent::__construct();
        @ini_set('memory_limit', '1024M'); // 设置内存大小
        @ini_set("max_execution_time", "0"); // 请求超时时间 0 为不限时
        @ini_set('default_socket_timeout', 3600); // 设置 file_get_contents 请求超时时间 官方的说明，似乎没有不限时间的选项，也就是不能填0，你如果填0，那么socket就会立即返回失败，
   }    
   /**
    * 一键升级
    */
   function OneKeyUpgrade(){
      // sleep(3);
        $upgradeLogic = new UpgradeLogic();
        $msg = $upgradeLogic->OneKeyUpgrade(); //升级包消息
        exit("$msg");
   }     
}