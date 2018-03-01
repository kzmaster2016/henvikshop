<?php
/**
 * 
 */
namespace Admin\Controller;
use Admin\Model;
use Think\AjaxPage;
use Think\Page;

class TemplateController extends BaseController {
    
    
    /**
     *  模板列表
     */
    public function templateList(){     
         $t = I('t','pc'); // pc or  mobile        
         $m = ($t == 'pc') ? 'Home' : 'Mobile';
         $arr = scandir("./Template/$t/");
         foreach($arr as $key => $val)
         {
                if($val == '.' || $val == '..' )
                    continue;                 
                 $template_config[$val] = include "./Template/$t/$val/config.php";
         }
        
        $this->assign('t',$t);        
        // $default_theme =  tpCache("hidden.{$t}_default_theme"); // //$default_theme = M('Config')->where("name='{$t}_default_theme'")->getField('value');
        $template_arr = include("./Application/$m/Conf/html.php");        
        $this->assign('default_theme',$template_arr['DEFAULT_THEME']);
        $this->assign('template_config',$template_config);
        $this->display();
    }    
    
    /**
     * 魔板选择
     */
    public function changeTemplate(){        
        
        $t = I('t','pc'); // pc or  mobile        
        $m = ($t == 'pc') ? 'Home' : 'Mobile';
        //$default_theme = tpCache("hidden.{$t}_default_theme"); // 获取原来的配置                
        //tpCache("hidden.{$t}_default_theme",$_GET['key']);
        //tpCache('hidden',array("{$t}_default_theme"=>$_GET['key']));                         
        // 修改文件配置  
         if(!is_writeable("./Application/$m/Conf/html.php"))
            return "文件/Application/$m/Conf/html.php不可写,不能启用魔板,请修改权限!!!";           
         
		$config_html ="<?php
		return array(
			'VIEW_PATH'       =>'./Template/$t/', // 改变某个模块的模板文件目录
			'DEFAULT_THEME'    =>'{$_GET['key']}', // 模板名称
			'TMPL_PARSE_STRING'  =>array(
		//                '__PUBLIC__' => '/Common', // 更改默认的/Public 替换规则
					'__STATIC__'     => '/Template/$t/{$_GET['key']}/Static', // 增加新的image  css js  访问路径  后面给 php 改了
			   ),
			   //'DATA_CACHE_TIME'=>60, // 查询缓存时间
			);
		?>";
         file_put_contents("./Application/$m/Conf/html.php", $config_html);       
        $this->success("操作成功!!!",U('Admin/Template/templateList',array('t'=>$t)));      
    }
}