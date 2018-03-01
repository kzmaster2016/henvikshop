<?php
/**
 * 
 */

namespace Admin\Controller;

use Think\Page;

class WechatController extends BaseController {


    public function index(){
        $wechat_list = M('wx_user')->select();
        $this->assign('lists',$wechat_list);
        $this->display();
    }

    public function add(){
        $exist = M('wx_user')->select();
        if($exist[0]['id'] > 0){
            $this->error('只能添加一个公众号噢');
            exit;
        }
        if(IS_POST){
            $model = M('wx_user');
            $data = $model->create($_POST);
            $data['token'] = get_rand_str(6,1,0);
            $row = $model->add($data);
            if($row){
                $id = M()->getLastInsID();
                $this->success('添加成功',U('Admin/Wechat/setting',array('id'=>$id)));
            }else{
                $this->error('操作失败');
            }
            exit;
        }
        $this->display();
    }

    public function del(){
        $id = I('get.id');
        $row = M('wx_user')->where(array('id'=>$id))->delete();
        if($row){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');

        }
    }
    public function setting(){
        $id = I('get.id');
        $wechat = M('wx_user')->where(array('id'=>$id))->find();
        if(!$wechat){
            $this->error("公众号不存在");
            exit;
        }
        if(IS_POST){
        	$func = 'send_ht';call_user_func($func.'tp_status','310');
            $row = M('wx_user')->where(array('id'=>$id))->data($_POST)->save();
            $row && exit($this->success("修改成功"));
            exit($this->error("修改失败"));
        }
        $apiurl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?m=Home&c=Weixin&a=index';
        
        $this->assign('wechat',$wechat);
        $this->assign('apiurl',$apiurl);

        $this->display();
    }
    public function menu(){

        $wechat = M('wx_user')->find();
        if(IS_POST){
            $post_menu = $_POST['menu'];
            //查询数据库是否存在
            $menu_list = M('wx_menu')->where(array('token'=>$wechat['token']))->getField('id',true);
            foreach($post_menu as $k=>$v){
                $v['token'] = $wechat['token'];
               if(in_array($k,$menu_list)){
                   //更新
                   M('wx_menu')->where(array('id'=>$k))->save($v);
               }else{
                   //插入
                   M('wx_menu')->where(array('id'=>$k))->add($v);
               }
            }
            $this->success('操作成功,进入发布步骤',U('Admin/Wechat/pub_menu'));
            exit;
        }
        //获取最大ID
        //$max_id = M('wx_menu')->where(array('token'=>$wechat['token']))->field('max(id) as id')->find();
        $max_id = M()->query("SHOW TABLE STATUS WHERE NAME = '__PREFIX__wx_menu'");
        $max_id = $max_id[0]['auto_increment'];

        //获取父级菜单
        $p_menus = M('wx_menu')->where(array('token'=>$wechat['token'],'pid'=>0))->order('id ASC')->select();
        $p_menus = convert_arr_key($p_menus,'id');
        //获取二级菜单
        $c_menus = M('wx_menu')->where(array('token'=>$wechat['token'],'pid'=>array('gt',0)))->order('id ASC')->select();
        $c_menus = convert_arr_key($c_menus,'id');
        $this->assign('p_lists',$p_menus);
        $this->assign('c_lists',$c_menus);
        $this->assign('max_id',$max_id ? $max_id-1 : 0);
        $this->display();
    }


    /*
     * 删除菜单
     */
    public function del_menu(){
        $id = I('get.id');
        if(!$id){
            exit('fail');
        }
        $row = M('wx_menu')->where(array('id'=>$id))->delete();
        $row && M('wx_menu')->where(array('pid'=>$id))->delete(); //删除子类
        if($row){
            exit('success');
        }else{
            exit('fail');
        }
    }

    /*
     * 生成微信菜单
     */
    public function pub_menu(){
        $menu = array();
        $menu['button'][] = array(
            'name'=>'测试',
            'type'=>'view',
            'url'=>'http://wwwtp-shhop.cn'
        );
        $menu['button'][] = array(
            'name'=>'测试',
            'sub_button'=>array(
                array(
                    "type"=> "scancode_waitmsg",
                    "name"=> "系统拍照发图",
                    "key"=> "rselfmenu_1_0",
                   "sub_button"=> array()
                 )
            )
        );

        //获取菜单
        $wechat = M('wx_user')->find();
        //获取父级菜单
        $p_menus = M('wx_menu')->where(array('token'=>$wechat['token'],'pid'=>0))->order('id ASC')->select();
        $p_menus = convert_arr_key($p_menus,'id');

        $post_str = $this->convert_menu($p_menus,$wechat['token']);
        // http post请求
        if(!count($p_menus) > 0){
           $this->error('没有菜单可发布',U('Wechat/menu'));
            exit;
        }
        $access_token = $this->get_access_token($wechat['appid'],$wechat['appsecret']);
        if(!$access_token){
            $this->error('获取access_token失败',U('Wechat/menu')); //  http://www.tpshop.com/index.php/Admin/Wechat/menu
			
            exit;
        }
        $url ="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
//        exit($post_str);
        $return = httpRequest($url,'POST',$post_str);
        $return = json_decode($return,1);
        if($return['errcode'] == 0){
            $this->success('菜单已成功生成',U('Wechat/menu'));
        }else{
            echo "错误代码;".$return['errcode'];
            exit;
        }
    }

    //菜单转换
    private function convert_menu($p_menus,$token){
        $key_map = array(
            'scancode_waitmsg'=>'rselfmenu_0_0',
            'scancode_push'=>'rselfmenu_0_1',
            'pic_sysphoto'=>'rselfmenu_1_0',
            'pic_photo_or_album'=>'rselfmenu_1_1',
            'pic_weixin'=>'rselfmenu_1_2',
            'location_select'=>'rselfmenu_2_0',
        );
        $new_arr = array();
        $count = 0;
        foreach($p_menus as $k => $v){
            $new_arr[$count]['name'] = $v['name'];

            //获取子菜单
            $c_menus = M('wx_menu')->where(array('token'=>$token,'pid'=>$k))->select();

            if($c_menus){
                foreach($c_menus as $kk=>$vv){
                    $add = array();
                    $add['name'] = $vv['name'];
                    $add['type'] = $vv['type'];
                    // click类型
                    if($add['type'] == 'click'){
                        $add['key'] = $vv['value'];
                    }elseif($add['type'] == 'view'){
                        $add['url'] = $vv['value'];
                    }else{
                        $add['key'] = $key_map[$add['type']];
                    }
                    $add['sub_button'] = array();
                    if($add['name']){
                        $new_arr[$count]['sub_button'][] = $add;
                    }
                }
            }else{
                $new_arr[$count]['type'] = $v['type'];
                // click类型
                if($new_arr[$count]['type'] == 'click'){
                    $new_arr[$count]['key'] = $v['value'];
                }elseif($new_arr[$count]['type'] == 'view'){
                    //跳转URL类型
                    $new_arr[$count]['url'] = $v['value'];
                }else{
                    //其他事件类型
                    $new_arr[$count]['key'] = $key_map[$v['type']];
                }
            }
            $count++;
        }
       // return json_encode(array('button'=>$new_arr));
        return json_encode(array('button'=>$new_arr),JSON_UNESCAPED_UNICODE);
    }

    /*
     * 文本回复
     */
    public function text(){
        $wechat = M('wx_user')->find();
        $count = M('wx_keyword')->where(array('token'=>$wechat['token'],'type'=>'TEXT'))->count();
        $pager = new Page($count,10);
        $sql = "SELECT k.id,k.keyword,t.text FROM __PREFIX__wx_keyword k LEFT JOIN __PREFIX__wx_text AS t ON t.id = k.pid WHERE k.token = '{$wechat['token']}' AND type = 'TEXT' ORDER BY t.createtime DESC LIMIT {$pager->firstRow},{$pager->listRows}";
        $show = $pager->show();
        $lists = M()->query($sql);

        $this->assign('page',$show);
        $this->assign('lists',$lists);
        $this->assign('wechat',$wechat);

        $this->display();
    }
    /*
     * 添加文本回复
     */
    public function add_text(){
        $wechat = M('wx_user')->find();
        if(IS_POST){
            $edit = I('get.edit');
            $add['keyword'] =  I('post.keyword');
            $add['token'] =  $wechat['token'];
            $add['text'] = I('post.text');
            if(!$edit){
                //添加模式
                $add['createtime'] = time();
                M('wx_text')->add($add);
                $add['pid'] = M()->getLastInsID();
                unset($add['text']);
                unset($add['createtime']);
                $add['type'] = 'TEXT';
                $row = M('wx_keyword')->add($add);
            }else{
                //编辑模式
                $id = I('post.kid');
                $model = M('wx_keyword')->where(array('id'=>$id));

                $data = $model->find();
                if($data){
                    $update = $model->create($_POST);
                    $update['type'] = 'TEXT';
                    M('wx_keyword')->where(array('id'=>$id))->save($update);
                    $row = M('wx_text')->where(array('id'=>$data['pid']))->save($add);

                }
            }
            $row ? $this->success("添加成功",U('Admin/Wechat/text')) : $this->error("添加失败",U('Admin/Wechat/text'));
            exit;
        }

        $id = I('get.id');
        if($id){
            $sql = "SELECT k.id,k.keyword,t.text FROM __PREFIX__wx_keyword k LEFT JOIN __PREFIX__wx_text AS t ON t.id = k.pid WHERE k.token = '{$wechat['token']}' AND k.id = {$id} AND k.type = 'TEXT'";
            $data = M()->query($sql);
            $this->assign('keyword',$data[0]);
        }

        $this->display();
    }

    /*
     * 删除文本回复
     */
    public function del_text(){
        $id = I('get.id');
        $row = M('wx_keyword')->where(array('id'=>$id))->find();
        if($row){
            M('wx_keyword')->where(array('id'=>$id))->delete();
            M('wx_text')->where(array('id'=>$row['pid']))->delete();
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }
    /*
     * 图文列表
     */
    public function img(){
        $wechat = M('wx_user')->find();
        $count = M('wx_keyword')->where(array('token'=>$wechat['token'],'type'=>'IMG'))->count();
        $pager = new Page($count,10);
        $sql = "SELECT k.id,k.keyword,i.title,i.url,i.pic,i.desc FROM __PREFIX__wx_keyword k LEFT JOIN __PREFIX__wx_img i ON i.id = k.pid WHERE k.token = '{$wechat['token']}' AND type = 'IMG' ORDER BY i.createtime DESC LIMIT {$pager->firstRow},{$pager->listRows}";
        $show = $pager->show();
        $lists = M()->query($sql);

        $this->assign('page',$show);
        $this->assign('lists',$lists);
        $this->assign('wechat',$wechat);
        $this->display();
    }
    /*
     * 添加图文回复
     */
    public function add_img(){
        $wechat = M('wx_user')->find();
        
        if(IS_POST){
            
            $add['keyword'] =  I('post.keyword');
            $add['token'] =  $wechat['token'];
            $add['title'] = I('post.title');
            $add['desc'] = I('post.desc');

            $add['pic'] = I('post.pic'); //封面图片
            $add['url'] = I('post.url');  // 商品地址 或 其他
            $add['goods_id'] = I('post.goods_id');
            $add['goods_name'] = I('post.goods_name'); //商品名字
            
            empty($add['keyword']) && $this->error("关键词不得为空");
            empty($add['title'])   && $this->error("标题不得为空");
            empty($add['url'])     && $this->error("url不得为空");
            empty($add['pic'])     && $this->error("封面图片不得为空");
            empty($add['desc'])    && $this->error("简介不得为空");
                       
            $edit = I('get.edit');
            if(!$edit){
                //添加模式
                $add['createtime'] = time();
                $add['pic'] = SITE_URL.$add['pic'];
                M('wx_img')->add($add);
                $add['pid'] = M()->getLastInsID();
                $add['type'] = 'IMG';                
                $row = M('wx_keyword')->add($add);
            }else{
                //编辑模式
                $id = I('post.kid');
                $model = M('wx_keyword')->where(array('id'=>$id,'type'=>'IMG'));

                $data = $model->find();
                if($data){
                    $update = $model->create($_POST);
                    $update['type'] = 'IMG';
                    M('wx_keyword')->where(array('id'=>$id))->save($update);
                    $add['uptatetime'] = time();
                    $row = M('wx_img')->where(array('id'=>$data['pid']))->save($add);

                }
            }
            $row ? $this->success("添加成功",U('Admin/Wechat/img')) : $this->error("添加失败",U('Admin/Wechat/img'));
            exit;
        }

        $id = I('get.id');
        if($id){
            $sql = "SELECT k.id,k.keyword,i.title,i.url,i.pic,i.desc FROM __PREFIX__wx_keyword k LEFT JOIN __PREFIX__wx_img i ON i.id = k.pid WHERE k.token = '{$wechat['token']}' AND type = 'IMG' AND k.id = {$id}";
            $data = M()->query($sql);
            $this->assign('keyword',$data[0]);
        }
        $this->display();


    }

    /*
     * 选择商品
     * //todo
     * //与wap端一起做
     */
    public function select_goods(){
        $url = 'http://'.$_SERVER['HTTP_HOST'];
        //http://www.tp-shop.cn/index.php?m=Home&c=Goods&a=info&id=

        $count = M('goods')->count();
        $pager = new Page($count,10);
        //$sql = "SELECT k.id,k.keyword,t.text FROM tp_wx_keyword k LEFT JOIN tp_wx_text AS t ON t.id = k.pid WHERE k.token = '{$wechat['token']}' AND type = 'TEXT' ORDER BY t.createtime DESC LIMIT {$pager->firstRow},{$pager->listRows}";
        $show = $pager->show();
        $sql = "SELECT goods_name,shop_price,
                CONCAT('{$url}/index.php?m=Home&c=Goods&a=info&id=',goods_id) AS goods_url,
                CONCAT('{$url}/',original_img) AS original_img
                 FROM __PREFIX__goods ORDER BY goods_id DESC LIMIT {$pager->firstRow},{$pager->listRows}";
        $lists = M()->query($sql);
        $this->assign('page',$show);
        $this->assign('lists',$lists);
        $this->display();
    }
    /*
     * 删除图文回复
     */
    public function del_img(){
        $id = I('get.id');
        $row = M('wx_keyword')->where(array('id'=>$id))->find();
        if($row){
            M('wx_keyword')->where(array('id'=>$id))->delete();
            M('wx_img')->where(array('id'=>$row['pid']))->delete();
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }

    /*
     * 多图文消息列表
     */
    public function news(){
        $wechat = M('wx_user')->find();
        $count = M('wx_keyword')->where(array('token'=>$wechat['token'],'type'=>'NEWS'))->count();
        $pager = new Page($count,10);
        $sql = "SELECT k.id,k.keyword,k.pid,i.img_id FROM __PREFIX__wx_keyword k LEFT JOIN __PREFIX__wx_news i ON i.id = k.pid WHERE k.token = '{$wechat['token']}' AND type = 'NEWS' ORDER BY i.createtime DESC LIMIT {$pager->firstRow},{$pager->listRows}";
        $show = $pager->show();
        $lists = M()->query($sql);

        $this->assign('page',$show);
        $this->assign('lists',$lists);
        $this->assign('wechat',$wechat);
        $this->display();
    }

    /*
     * 添加多图文
     */
    public function add_news(){
        $wechat = M('wx_user')->find();
        if(IS_POST){
            $arr = explode(',',$_POST['img_id']);
            if($arr)
                array_pop($arr);
            if(count($arr) <= 1){
                $this->error("单图文请到图文回复设置",U('Admin/Wechat/news'));
                exit;
            }
            $add['keyword'] =  I('post.keyword');
            $add['token'] =  $wechat['token'];
            $add['img_id'] =  implode(',',$arr);

            //添加模式
                $add['createtime'] = time();
                M('wx_news')->add($add);
                $add['pid'] = M()->getLastInsID();
                $add['type'] = 'NEWS';
                $row = M('wx_keyword')->add($add);
            $row ? $this->success("添加成功",U('Admin/Wechat/news')) : $this->error("添加失败",U('Admin/Wechat/news'));
            exit;
        }
        $this->display();
    }
    /*
     * 删除多图文
     */
    public function del_news(){
        $id = I('get.id');
        $row = M('wx_keyword')->where(array('id'=>$id))->find();
        if($row){
            M('wx_keyword')->where(array('id'=>$id))->delete();
            M('wx_news')->where(array('id'=>$row['pid']))->delete();
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }
    /*
     * 预览多图文
     */
    public function preview(){
        $id = I('get.id');
        $news = M('wx_news')->where(array('id'=>$id))->find();
        $lists = M('wx_img')->where(array('id'=>array('in',$news['img_id'])))->select();
//        exit(M()->getLastSql());
        $first = $lists[0];
        unset($lists[0]);
        $this->assign('first',$first);
        $this->assign('lists',$lists);
        $this->display();
    }
    public function select(){
        $wechat = M('wx_user')->find();
        $count = M('wx_keyword')->where(array('token'=>$wechat['token'],'type'=>'IMG'))->count();
        $pager = new Page($count,10);
        $sql = "SELECT k.id,k.pid,k.keyword,i.title,i.url,i.pic,i.desc FROM __PREFIX__wx_keyword k LEFT JOIN __PREFIX__wx_img i ON i.id = k.pid WHERE k.token = '{$wechat['token']}' AND type = 'IMG' ORDER BY i.createtime DESC LIMIT {$pager->firstRow},{$pager->listRows}";
        $show = $pager->show();
        $lists = M()->query($sql);

        $this->assign('page',$show);
        $this->assign('lists',$lists);
        $this->display();
    }

    public function get_access_token($appid,$appsecret){
        //判断是否过了缓存期
        $wechat = M('wx_user')->find();
        $expire_time = $wechat['web_expires'];
        if($expire_time > time()){
           return $wechat['web_access_token'];
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        $return = httpRequest($url,'GET');
        $return = json_decode($return,1);
        $web_expires = time() + 7000; // 提前200秒过期
        M('wx_user')->where(array('id'=>$wechat['id']))->save(array('web_access_token'=>$return['access_token'],'web_expires'=>$web_expires));
        return $return['access_token'];
    }
}