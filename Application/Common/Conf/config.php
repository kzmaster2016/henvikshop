<?php
//  加载常量配置文件
require_once('constant.php');

return array(
    
    'LOAD_EXT_FILE' =>'common',//加载公共函数 
    'AUTH_CODE' => "TPSHOP", //安装完毕之后不要改变，否则所有密码都会出错
    //'URL_CASE_INSENSITIVE' => false, //URL大小写不敏感
    'LOAD_EXT_CONFIG'=>'db,route', // 加载数据库配置文件
    
    'DATA_CACHE_TIME' => 60,
    
    //'URL_MODEL'=>2, // 如果需要 隐藏 index.php  打开这行"URL_MODEL"注释 同时在apache环境下 开启 伪静态模块，  如果在nginx 下需要另外配置，参考thinkphp官网手册
    /*
     * RBAC认证配置信息
     */

    'SESSION_AUTO_START'        => true,
    'USER_AUTH_ON'              => true,
    'USER_AUTH_TYPE'            => 1,         // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'             => 'authId',  // 用户认证SESSION标记
    'ADMIN_AUTH_KEY'            => 'administrator',
    'USER_AUTH_MODEL'           => 'User',    // 默认验证数据表模型
    'AUTH_PWD_ENCODER'          => 'md5',     // 用户认证密码加密方式
    'USER_AUTH_GATEWAY'         => '/Public/login',// 默认认证网关
    'NOT_AUTH_MODULE'           => 'Public',  // 默认无需认证模块
//     'REQUIRE_AUTH_MODULE'       => '',        // 默认需要认证模块
//     'NOT_AUTH_ACTION'           => '',        // 默认无需认证操作
//     'REQUIRE_AUTH_ACTION'       => '',        // 默认需要认证操作
    'GUEST_AUTH_ON'             => false,     // 是否开启游客授权访问
    'GUEST_AUTH_ID'             => 0,         // 游客的用户ID
    'DB_LIKE_FIELDS'            => 'title|remark',
    'RBAC_ROLE_TABLE'           => 'think_role',
    'RBAC_USER_TABLE'           => 'think_role_user',
    'RBAC_ACCESS_TABLE'         => 'think_access',
    'RBAC_NODE_TABLE'           => 'think_node',
    'SHOW_PAGE_TRACE'           =>0,         //显示调试信息
    //'RBAC_ERROR_PAGE'         => '/Public/tp404.html',
    //'ERROR_PAGE'=>'/Index/Index/error_page.html',
    'ERROR_PAGE'=>'/index.php/Home/Tperror/tp404.html',    
    // 表单令牌验证相关的配置参数
    'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
    'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true 
    'TAGLIB_LOAD'   => true,
    'APP_AUTOLOAD_PATH'  =>'@.TagLib',
    'TAGLIB_BUILD_IN'  =>  'cx,tpshop', // tpshop 为自定义标签类名称
    'TMPL_TEMPLATE_SUFFIX'  =>  '.html',     // 默认模板文件后缀
    'URL_HTML_SUFFIX'       =>  'html',  // URL伪静态后缀设置  默认为html  去除默认的 否则很多地址报错

    'ORDER_STATUS' => array(
        0 => '待确认',
        1 => '已确认',
        2 => '已收货',
        3 => '已取消',                
        4 => '已完成',//评价完
        5 => '已作废',
    ),
    'SHIPPING_STATUS' => array(
        0 => '未发货',
        1 => '已发货',
    	2 => '部分发货'	        
    ),
    'PAY_STATUS' => array(
        0 => '未支付',
        1 => '已支付',
    ),
    'SEX' => array(
        0 => '保密',
        1 => '男',
        2 => '女'
    ),
    'COUPON_TYPE' => array(
    	0 => '面额模板',
        1 => '按用户发放',   		
        2 => '注册发放',
        3 => '邀请发放',
    	4 => '线下发放'	
    ),
	'PROM_TYPE' => array(
		0 => '默认',
		1 => '抢购',
		2 => '团购',
		3 => '优惠'			
	),
    // 订单用户端显示状态
    'WAITPAY'=>' AND pay_status = 0 AND order_status = 0 AND "pay_code" !="cod" ', //订单查询状态 待支付
    'WAITSEND'=>' AND (pay_status=1 OR "pay_code"="cod") AND shipping_status !=1 AND order_status in(0,1) ', //订单查询状态 待发货
    'WAITRECEIVE'=>' AND shipping_status=1 AND order_status = 1 ', //订单查询状态 待收货    
    'WAITCCOMMENT'=> ' AND order_status=2 ', // 待评价 确认收货     //'FINISHED'=>'  AND order_status=1 ', //订单查询状态 已完成 
    'FINISH'=> ' AND order_status = 4 ', // 已完成
    'CANCEL'=> ' AND order_status = 3 ', // 已取消
    'CANCELLED'=> 'AND order_status = 5 ',//已作废
    
    'ORDER_STATUS_DESC' => array(
        'WAITPAY' => '待支付',
        'WAITSEND'=>'待发货',
        'WAITRECEIVE'=>'待收货',
        'WAITCCOMMENT'=> '待评价',
        'CANCEL'=> '已取消',
        'FINISH'=> '已完成', //
        'CANCELLED'=> '已作废'
    ),
    
    /**
     *  订单用户端显示按钮     
     *  去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
     *   取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0 
     *  确认收货  AND shipping_status=1 AND order_status=0 
     *   评价      AND order_status=1 
     *   查看物流  if(!empty(物流单号))   
     *  退货按钮（联系客服）  所有退换货操作， 都需要人工介入   不支持在线退换货
     */
    'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称    
    
    'APP_SUB_DOMAIN_DEPLOY'   =>    1, // 开启子域名或者IP配置
    'APP_SUB_DOMAIN_RULES'    =>    array( 
        'm.tpshop.com'   => 'Mobile/',  // 手机访问网站
        'web1.henvik.com' =>'Web'
    ),    
        
    'DEFAULT_FILTER'        => 'strip_sql,htmlspecialchars',   // 系统默认的变量过滤机制

    /**
     * coreseek/sphinx全文检索引擎配置
     */
    'SPHINX_HOST'         =>      '127.0.0.1',
    'SPHINX_PORT'         =>      '9312',
);