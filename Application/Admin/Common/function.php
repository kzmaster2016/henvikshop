<?php
/**
 * 
 */

/**
 * 管理员操作记录
 * @param $log_url 操作URL
 * @param $log_info 记录信息
 */
function adminLog($log_info){
    $add['log_time'] = time();
    $add['admin_id'] = session('admin_id');
    $add['log_info'] = $log_info;
    $add['log_ip'] = getIP();
    $add['log_url'] = __ACTION__;
    M('admin_log')->add($add);
}


function getAdminInfo($admin_id){
	return D('admin')->where("admin_id=$admin_id")->find();
}

function tpversion()
{     
    if(!empty($_SESSION['isset_push']))
        return false;    
    $_SESSION['isset_push'] = 1;    
    error_reporting(0);//关闭所有错误报告
    $app_path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
    $version_txt_path = $app_path.'/Application/Admin/Conf/version.txt';
    $curent_version = file_get_contents($version_txt_path);
    
    $vaules = array(            
            'domain'=>$_SERVER['HTTP_HOST'], 
            'last_domain'=>$_SERVER['HTTP_HOST'], 
            'key_num'=>$curent_version, 
            'install_time'=>INSTALL_DATE, 
            'cpu'=>'0001',
            'mac'=>'0002',
            'serial_number'=>SERIALNUMBER,
            );     
     $url = "http://service.tp-shop.cn/index.php?m=Home&c=Index&a=user_push&".http_build_query($vaules);
     stream_context_set_default(array('http' => array('timeout' => 3)));
     file_get_contents($url);       
}
 
/**
 * 面包屑导航  用于后台管理
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_admin()
{        
    $navigate = include APP_PATH.'Common/Conf/navigate.php';    
    $location = strtolower('Admin/'.CONTROLLER_NAME);
    $arr = array(
        '后台首页'=>'javascript:void();',
        $navigate[$location]['name']=>'javascript:void();',
        $navigate[$location]['action'][ACTION_NAME]=>'javascript:void();',
    );
    return $arr;
}

/**
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename)
{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 根据id获取地区名字
 * @param $regionId id
 */
function getRegionName($regionId){
    $data = M('region')->where(array('id'=>$regionId))->field('name')->find();
    return $data['name'];
}

function getMenuList($act_list){
	//根据角色权限过滤菜单
	$menu_list = getAllMenu();
	if($act_list != 'all'){
		$right = M('system_menu')->where("id in ($act_list)")->cache(true)->getField('right',true);
		foreach ($right as $val){
			$role_right .= $val.',';
		}
		$role_right = explode(',', $role_right);		
		foreach($menu_list as $k=>$mrr){
			foreach ($mrr['sub_menu'] as $j=>$v){
				if(!in_array($v['control'].'Controller@'.$v['act'], $role_right)){
					unset($menu_list[$k]['sub_menu'][$j]);//过滤菜单
				}
			}
		}
	}
	return $menu_list;
}

/**
 * 邮件发送
 * @param $to    接收人
 * @param string $subject   邮件标题
 * @param string $content   邮件内容(html模板渲染后的内容)
 * @throws Exception
 * @throws phpmailerException
 */
function send_templ_email($user,$pwd,$to,$subject='',$content){
    require_once THINK_PATH.'Library/Vendor/phpmailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $config = tpCache('smtp');

	$mail->CharSet  = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->Encoding = "base64"; 
    $mail->isSMTP();
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 1;
    //调试输出格式
	//$mail->Debugoutput = 'html';
    //smtp服务器
    $mail->Host = $config['smtp_server'];
    //端口 - likely to be 25, 465 or 587
    $mail->Port = $config['smtp_port'];
	
	// if($mail->Port === 465) 
    $mail->SMTPSecure = 'ssl';// 使用安全协议
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //用户名
    $mail->Username = $user;
    //密码
    $mail->Password = $pwd;
    //Set who the message is to be sent from
    $mail->setFrom($user);
    //回复地址
    //$mail->addReplyTo('replyto@example.com', 'First Last');
    //接收邮件方
    if(is_array($to)){
    	foreach ($to as $v){
    		$mail->addAddress($v);
    	}
    }else{
    	$mail->addAddress($to);
    }

    $mail->isHTML(true);// send as HTML
    //标题
    $mail->Subject = $subject;
    //HTML内容转换

    $mail->isHTML(true);// send as HTML
    // $mail->msgHTML($content);
    $mail->Body = $content;
    $mail->isHTML(true);// send as HTML
    //Replace the plain text body with one created manually
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    //添加附件
    //$mail->addAttachment('images/phpmailer_mini.png');
    //send the message, check for errors
    return $mail->send();

}

function getAllMenu(){
	return	array(
			'system' => array('name'=>'系统设置','icon'=>'fa-cog','sub_menu'=>array(
					array('name'=>'网站设置','act'=>'index','control'=>'System'),
					array('name'=>'友情链接','act'=>'linkList','control'=>'Article'),
					array('name'=>'自定义导航','act'=>'navigationList','control'=>'System'),
					array('name'=>'区域管理','act'=>'region','control'=>'Tools'),
					array('name'=>'权限资源列表','act'=>'right_list','control'=>'System'),
			)),
			'access' => array('name' => '权限管理', 'icon'=>'fa-gears', 'sub_menu' => array(
					array('name' => '管理员列表', 'act'=>'index', 'control'=>'Admin'),
					array('name' => '角色管理', 'act'=>'role', 'control'=>'Admin'),
					array('name' => '供应商管理', 'act'=>'supplier', 'control'=>'Admin'),
					array('name' => '管理员日志', 'act'=>'log', 'control'=>'Admin'),
			)),
			'member' => array('name'=>'会员管理','icon'=>'fa-user','sub_menu'=>array(
					array('name'=>'会员列表','act'=>'index','control'=>'User'),
					array('name'=>'会员等级','act'=>'levelList','control'=>'User'),
					array('name'=>'充值记录','act'=>'recharge','control'=>'User'),
					array('name' => '提现申请', 'act'=>'withdrawals', 'control'=>'User'),
					array('name' => '汇款记录', 'act'=>'remittance', 'control'=>'User'),
					//array('name'=>'会员整合','act'=>'integrate','control'=>'User'),
			)),
			'goods' => array('name' => '商品管理', 'icon'=>'fa-book', 'sub_menu' => array(
					array('name' => '商品列表', 'act'=>'goodsList', 'control'=>'Goods'),
					array('name' => '商品分类', 'act'=>'categoryList', 'control'=>'Goods'),
					array('name' => '商品类型', 'act'=>'goodsTypeList', 'control'=>'Goods'),
					array('name' => '商品规格', 'act' =>'specList', 'control' => 'Goods'),
					array('name' => '商品属性', 'act'=>'goodsAttributeList', 'control'=>'Goods'),
					array('name' => '品牌列表', 'act'=>'brandList', 'control'=>'Goods'),
					array('name' => '商品评论','act'=>'index','control'=>'Comment'),
					array('name' => '商品咨询','act'=>'ask_list','control'=>'Comment'),
			)),
			'order' => array('name' => '订单管理', 'icon'=>'fa-money', 'sub_menu' => array(
					array('name' => '订单列表', 'act'=>'index', 'control'=>'Order'),
					array('name' => '发货单', 'act'=>'delivery_list', 'control'=>'Order'),
					//array('name' => '快递单', 'act'=>'express_list', 'control'=>'Order'),
					array('name' => '退货单', 'act'=>'return_list', 'control'=>'Order'),
					array('name' => '添加订单', 'act'=>'add_order', 'control'=>'Order'),
					array('name' => '订单日志', 'act'=>'order_log', 'control'=>'Order'),
			)),
			'promotion' => array('name' => '促销管理', 'icon'=>'fa-bell', 'sub_menu' => array(
					array('name' => '抢购管理', 'act'=>'flash_sale', 'control'=>'Promotion'),
					array('name' => '团购管理', 'act'=>'group_buy_list', 'control'=>'Promotion'),
					array('name' => '商品促销', 'act'=>'prom_goods_list', 'control'=>'Promotion'),
					array('name' => '订单促销', 'act'=>'prom_order_list', 'control'=>'Promotion'),
					array('name' => '代金券管理','act'=>'index', 'control'=>'Coupon'),
			)),
			'Ad' => array('name' => '广告管理', 'icon'=>'fa-flag', 'sub_menu' => array(
					array('name' => '广告列表', 'act'=>'adList', 'control'=>'Ad'),
					array('name' => '广告位置', 'act'=>'positionList', 'control'=>'Ad'),
			)),
			'content' => array('name' => '内容管理', 'icon'=>'fa-comments', 'sub_menu' => array(
					array('name' => '文章列表', 'act'=>'articleList', 'control'=>'Article'),
					array('name' => '文章分类', 'act'=>'categoryList', 'control'=>'Article'),
					//array('name' => '帮助管理', 'act'=>'help_list', 'control'=>'Article'),
					//array('name' => '公告管理', 'act'=>'notice_list', 'control'=>'Article'),
					array('name' => '专题列表', 'act'=>'topicList', 'control'=>'Topic'),
					array('name' => '邮件模板', 'act'=>'email', 'control'=>'Article'),
			)),
			
			'theme' => array('name' => '模板管理', 'icon'=>'fa-adjust', 'sub_menu' => array(
					array('name' => 'PC端模板', 'act'=>'templateList?t=pc', 'control'=>'Template'),
					array('name' => '手机端模板', 'act'=>'templateList?t=mobile', 'control'=>'Template'),
			)),

			'topic'=>array(
               		'name' =>'专题管理','icon'=>'fa-adjust',
               		'sub_menu' =>array(
               			array('name'=>'专题列表','act'=>'topicList','control'=>'Topic'),
               			array('name'=>'添加专题','act'=>'topic','control'=>'Topic'),
               		
               		)
               ),
			'tools' => array('name' => '插件工具', 'icon'=>'fa-plug', 'sub_menu' => array(
					array('name' => '插件列表', 'act'=>'index', 'control'=>'Plugin'),
					array('name' => '数据备份', 'act'=>'index', 'control'=>'Tools'),
					array('name' => '数据还原', 'act'=>'restore', 'control'=>'Tools'),
			)),
			'count' => array('name' => '统计报表', 'icon'=>'fa-signal', 'sub_menu' => array(
					array('name' => '销售概况', 'act'=>'index', 'control'=>'Report'),
					array('name' => '销售排行', 'act'=>'saleTop', 'control'=>'Report'),
					array('name' => '会员排行', 'act'=>'userTop', 'control'=>'Report'),
					array('name' => '销售明细', 'act'=>'saleList', 'control'=>'Report'),
					array('name' => '会员统计', 'act'=>'user', 'control'=>'Report'),
					array('name' => '财务统计', 'act'=>'finance', 'control'=>'Report'),
			))
			,'weixin' => array('name' => '微信管理', 'icon'=>'fa-weixin', 'sub_menu' => array(
					array('name' => '公众号管理', 'act'=>'index', 'control'=>'Wechat'),
					array('name' => '微信菜单管理', 'act'=>'menu', 'control'=>'Wechat'),
					array('name' => '文本回复', 'act'=>'text', 'control'=>'Wechat'),
					array('name' => '图文回复', 'act'=>'img', 'control'=>'Wechat'),
					array('name' => '组合回复', 'act'=>'nes', 'control'=>'Wechat'),
					array('name' => '消息推送', 'act'=>'news', 'control'=>'Wechat'),
			))
			/*,'distribut' => array('name' => '分销管理', 'icon'=>'fa-cubes', 'sub_menu' => array(
					array('name' => '分销商品列表', 'act'=>'goods_list', 'control'=>'Distribut'),
					array('name' => '分销商列表', 'act'=>'distributor_list', 'control'=>'Distribut'),
					array('name' => '分销关系', 'act'=>'tree', 'control'=>'Distribut'),
					array('name' => '分销设置', 'act'=>'set', 'control'=>'Distribut'),
					array('name' => '分成日志', 'act'=>'rebate_log', 'control'=>'Distribut'),
			))*/
			/*,'pickup' => array('name' => '自提点管理', 'icon'=>'fa-anchor', 'sub_menu' => array(
					array('name' => '自提点列表', 'act'=>'index', 'control'=>'Pickup'),
					array('name' => '添加自提点', 'act'=>'add', 'control'=>'Pickup'),
			))*/
	);
}


function respose($res){
	exit(json_encode($res));
}