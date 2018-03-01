<?php
		return array(
			'VIEW_PATH'       =>'./Template/pc/', // 改变某个模块的模板文件目录
			'DEFAULT_THEME'    =>'default', // 模板名称
			'TMPL_PARSE_STRING'  =>array(
		//                '__PUBLIC__' => '/Common', // 更改默认的/Public 替换规则
					'__STATIC__'     => '/Template/pc/default/Static', // 增加新的image  css js  访问路径  后面给 php 改了
			   ),
			);
		?>