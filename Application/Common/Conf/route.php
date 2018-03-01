<?php

return array(
	'URL_ROUTER_ON'   => true,      // 开启路由
	'URL_MODEL'=>1, //
	'URL_ROUTE_RULES'=>array(
        '/^manual_(\d+)$/' => 'Doc/Index/manual?id=:1',
        '/^developer_(\w+)$/' => 'Doc/Index/index?developer=:1',
        'manual_list'=>'Doc/Index/manual_list',
        'download'=>'Index/Index/download',
        'product'=>'Index/Index/product',

        'articleList'=>'Index/Article/articleList',
        '/^articleList_cat_id_(\d+)$/'=>'Index/Article/articleList?cat_id=:1',
        '/^article_id_(\d+)$/'=>'Index/Article/detail?article_id=:1',
	),
	'URL_HTML_SUFFIX'       =>  'html',  // URL伪静态后缀设置  默认为html  去除默认的 否则很多地址报错
	'DEFAULT_MODULE'        =>  'Home',  // 默认模块

);
?>