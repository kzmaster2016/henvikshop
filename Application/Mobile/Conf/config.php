<?php
return array(
    'LOAD_EXT_CONFIG' => 'html',	// 加载其他自定义配置文件 html.php
	'URL_HTML_SUFFIX'   =>  'html',
	'HTML_CACHE_ON'     =>   true, // 开启静态缓存
	'HTML_CACHE_TIME'   =>   60,   // 全局静态缓存有效期（秒）
	'HTML_FILE_SUFFIX'  =>   '.html', //设置静态缓存文件后缀
	'HTML_CACHE_RULES'  =>   array(  //定义静态缓存规则
			// 定义格式1 数组方式
			//'静态地址' =>  array('静态规则', '有效期', '附加规则'),
			//'index:index'=>array('{:module}_{:controller}_{:action}',TPSHOP_CACHE_TIME),  // 首页静态缓存 3秒钟
			//'index:goodsList'=>array('{:module}_{:controller}_{:action}',300),  // 列表页静态缓存 3秒钟 无参数 post 提交的很难缓存
			//'index:goodsList'=>array('{:module}_{:controller}_{:action}_{id}',TPSHOP_CACHE_TIME),  // 列表页静态缓存 3秒钟
			//ajax 请求的商品列表内容在 ajaxGoodsList 函数中  S($keys,$html,300); 缓存
			//'Goods:goodsInfo'=>array('{:module}_{:controller}_{:action}_{id}',TPSHOP_CACHE_TIME),  // 商品详情页静态缓存 3秒钟
			'Goods:ajaxComment'=>array('{:module}_{:controller}_{:action}_{goods_id}_{commentType}_{p}',TPSHOP_CACHE_TIME),  // 商品评论页静态缓存 3秒钟
	),
	//默认错误跳转对应的模板文件
	'TMPL_ACTION_ERROR' => 'Public:tpmsg',
	//默认成功跳转对应的模板文件
	'TMPL_ACTION_SUCCESS' => 'Public:tpmsg',
);