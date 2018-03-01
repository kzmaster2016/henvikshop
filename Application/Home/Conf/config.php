<?php
return array(
	//'配置项'=>'配置值'           
    'LOAD_EXT_CONFIG' => 'html',    // 加载其他自定义配置文件 html.php
    'URL_HTML_SUFFIX'       =>  'html',  

    //'DB_SQL_BUILD_CACHE' => true, // sql 缓存
    //'DB_SQL_BUILD_QUEUE' => 'File', // 文件缓存
   // 'DB_SQL_BUILD_LENGTH' => 200, // SQL缓存的队列长度
    'DATA_CACHE_TIME' => 60,

    'HTML_CACHE_ON'     =>    false, // 开启静态缓存   
    'HTML_CACHE_TIME'   =>    60,   // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX'  =>    '.html', // 设置静态缓存文件后缀
    'HTML_CACHE_RULES'  =>     array(  // 定义静态缓存规则
         // 定义格式1 数组方式
         //'静态地址'    =>     array('静态规则', '有效期', '附加规则'), 
        'index:index'=>array('{:module}_{:controller}_{:action}',TPSHOP_CACHE_TIME),  
        // 首页静态缓存 3秒钟   

        'index:goodsList'=>array('{:module}_{:controller}_{:action}',300),  
        // 列表页静态缓存 3秒钟 无参数 post 提交的很难缓存

        'Goods:goodsList'=>array('{:module}_{:controller}_{:action}_{id})_{p}',TPSHOP_CACHE_TIME),  
        // 列表页静态缓存 3秒钟

        //ajax 请求的商品列表内容在 ajaxGoodsList 函数中  S($keys,$html,300);
        'Goods:goodsInfo'=>array('{:module}_{:controller}_{:action}_{id}',TPSHOP_CACHE_TIME),  
        // 商品详情页静态缓存 3秒钟    

        'Goods:ajaxComment'=>array('{:module}_{:controller}_{:action}_{goods_id}_{commentType}_{p}',TPSHOP_CACHE_TIME), 
         // 商品评论页静态缓存 3秒钟   

        'Goods:ajax_consult'=>array('{:module}_{:controller}_{:action}_{goods_id}_{consult_type}_{p}',TPSHOP_CACHE_TIME),  
        // 商品咨询页静态缓存 3秒钟                
        // 商品详情的规格价格缓存在 ajaxGoodsPrice 函数里面 S($keys,$price,300);缓存数据300秒  

    	'channel:index'=>array('{:module}_{:controller}_{:action}_{id}',TPSHOP_CACHE_TIME),  // 列表页静态缓存 3秒钟*/
    ), 
    
    // 'TMPL_CACHE_ON'   => false,  // 默认开启模板编译缓存 false 的话每次都重新编译模板
    'DB_FIELD_CACHE'  =>false,

    
    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' => 'Public:dispatch_jump',
    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => 'Public:dispatch_jump',
        
);