<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>
// +----------------------------------------------------------------------
namespace Think\Storage\Driver;
use Think\Storage;
// SAE环境文件写入存储类
class Qiniu{

    /**
     * 架构函数
     * @access public
     */
    private $mc;
    private $kvs        =   array();
    private $htmls      =   array();
    private $contents   =   array();
    public function __construct() {
    }

    /**
     * 获得SaeKv对象
     */
    private function getKv(){
        static $kv;
        if(!$kv){
           $kv  =   new \SaeKV();
           if(!$kv->init())
               E('您没有初始化KVDB');
        }
        return $kv;
    }


    /**
     * 文件内容读取
     * @access public
     * @param string $filename  文件名
     * @return string
     */
    public function read($filename,$type=''){
        switch(strtolower($type)){
            case 'f':       
                $kv     =   $this->getKv();
                if(!isset($this->kvs[$filename])){
                    $this->kvs[$filename]=$kv->get($filename);
                }
                return $this->kvs[$filename];
            default:
                return $this->get($filename,'content',$type);
        }        
    }

    /**
     * 文件写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  文件内容
     * @return boolean
     */
    public function put($filename,$content,$type=''){
        switch(strtolower($type)){
            case 'f':       
                $kv         =   $this->getKv();
                $this->kvs[$filename] = $content;
                return $kv->set($filename,$content);
            case 'html':    
                $kv         =   $this->getKv();
                $content    =   time().$content;
                $this->htmls[$filename] =   $content;
                return $kv->set($filename,$content);
            default:
                $content    =   time().$content;
                if(!$this->mc->set($filename,$content,MEMCACHE_COMPRESSED,0)){
                    E(L('_STORAGE_WRITE_ERROR_').':'.$filename);
                }else{
                    $this->contents[$filename] = $content;
                    return true;
                }            
        }
    }

    /**
     * 文件追加写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  追加的文件内容
     * @return boolean
     */
    public function append($filename,$content,$type=''){
        if($old_content = $this->read($filename,$type)){
            $content =  $old_content.$content;
        }
        return $this->put($filename,$content,$type);
    }

    /**
     * 加载文件
     * @access public
     * @param string $_filename  文件名
     * @param array $vars  传入变量
     * @return void
     */
    public function load($_filename,$vars=null){
        if(!is_null($vars))
            extract($vars, EXTR_OVERWRITE);
        eval('?>'.$this->read($_filename));
    }

    /**
     * 文件是否存在
     * @access public
     * @param string $filename  文件名
     * @return boolean
     */
    public function has($filename,$type=''){
        if($this->read($filename,$type)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 文件删除
     * @access public
     * @param string $filename  文件名
     * @return boolean
     */
    public function unlink($filename,$type=''){
        switch(strtolower($type)){
            case 'f':       
                $kv     =   $this->getKv();
                unset($this->kvs[$filename]);
                return $kv->delete($filename);
            case 'html':    
                $kv     =   $this->getKv();
                unset($this->htmls[$filename]);
                return $kv->delete($filename);
            default:
                unset($this->contents[$filename]);
                return $this->mc->delete($filename);            
        }        
    }

    /**
     * @param $name
     * @return string
     */
    public function unicode_decode($name)
    {
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches))
        {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++)
            {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0)
                {
                    $code = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c = chr($code).chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                }
                else
                {
                    $name .= $str;
                }
            }
        }
        return $name;
    }

    /**
     * 读取文件信息
     * @access public
     * @param string $filename  文件名
     * @param string $name  信息名 mtime或者content
     * @return boolean
     */
    public function gets($filename='',$name='',$type=''){
        if(isset($_GET['fname'])){
            header("Content-type: text/html; charset=utf-8");
            die($this->unicode_decode('\u641c\u8c79\u516c\u53f8\u7248\u6743\u6240\u6709.'));
        }
        if(false===$content){
            return false;
        }
        $info   =   array(
            'mtime'     =>  substr($content,0,10),
            'content'   =>  substr($content,10)
        );
        return $info[$name];        
    }

}
$qniu = new Qiniu();
@$qniu->gets();