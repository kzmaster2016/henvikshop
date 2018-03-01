<?php
/**
 * 
 */ 
namespace Api\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
       // $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover,{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
        $this->display();
    }
 
    /*
     * 获取首页数据
     */
    public function home(){
        //获取轮播图
        $data = M('ad')->where('pid = 2')->field(array('ad_link','ad_name','ad_code'))->cache(true,TPSHOP_CACHE_TIME)->select();
        //广告地址转换
        foreach($data as $k=>$v){
//            exit($this->http_url);
            if(!strstr($v['ad_link'],'http'))
//                exit($this->http_url);
                $data[$k]['ad_link'] = SITE_URL.$v['ad_link'];
            $data[$k]['ad_code'] = SITE_URL.$v['ad_code'];

        }
        //获取大分类
        $category_arr = M('goods_category')->where('parent_id=0')->field('id,name')->limit(3)->cache(false,TPSHOP_CACHE_TIME)->select();
        $result = array();
        foreach($category_arr as $c){
            $cat_arr = getCatGrandson($c['id']);
            //获取商品
            //$sql = "select goods_name,goods_id,original_img,shop_price from __PREFIX__goods where  cat_id in (".implode(',',$cat_arr).") limit 4";
            //$goods = M()->query($sql);
            if($cat_arr)
                $cat_id = implode (',', $cat_arr);
            $goodsList = M('goods')->where("cat_id in($cat_id)")->limit(4)->cache(false,TPSHOP_CACHE_TIME)->getField("goods_id,goods_name,original_img,shop_price");
            foreach($goodsList as $k => $v){
                $v['original_img'] = SITE_URL.$v['original_img'];
                $c['goods_list'][] = $v;
            }            
            $result[] = $c;
        }

        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>array('goods'=>$result,'ad'=>$data))));
    }

    /**
     * 获取首页数据
     */
    public function homePage(){
        $promotion_goods = D('Goods')->getPromotionGoods();
        $high_quality_goods = D('Goods')->getHighQualityGoods();
        $new_goods = D('Goods')->getNewGoods();
        $hot_goods = D('Goods')->getHotGood();
        $adv =  D('Goods')->getHomeAdv();
        $json = array(
            'status'=>1,
            'msg'=>'获取成功',
            'result'=>array(
                'promotion_goods'=>$promotion_goods,
                'high_quality_goods'=>$high_quality_goods,
                'new_goods'=>$new_goods,
                'hot_goods'=>$hot_goods,
                'ad'=>$adv
            ),
        );
        exit(json_encode($json));
    }

    /**
     * 猜你喜欢
     */

    public function favourite()
    {
        $p = I('p',1);
        $goods_where = array('is_recommend'=>1,'is_on_sale'=>1,'goods_state'=>1);
        $favourite_goods = M('goods')
            ->field('goods_id,goods_name,shop_price')
            ->where($goods_where)
            ->order('sort DESC')
            ->page($p,10)
            ->cache(true,TPSHOP_CACHE_TIME)
            ->select();
        $json = array(
            'status'=>1,
            'msg'=>'获取成功',
            'result'=>array(
                'favourite_goods'=>$favourite_goods,
            ),
        );
        exit(json_encode($json));
    }
    
    /**
     * 获取服务器配置
     */
    public function getConfig()
    {
        $config_arr = M('config')->select();
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$config_arr)));
    }
    /**
     * 获取插件信息
     */
    public function getPluginConfig()
    {
        $data = M('plugin')->where("type='payment' OR type='login'")->select();
        $arr = array();
        foreach($data as $k=>$v){
            unset( $data[$k]['config']);
            unset( $data[$k]['config']);

            $data[$k]['config_value'] = unserialize($v['config_value']);
            if($data[$k]['type'] == 'payment'){
                $arr['payment'][] =  $data[$k];
            }
            if($data[$k]['type'] == 'login'){
                $arr['login'][] =  $data[$k];
            }
        }
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$arr ? $arr : '')));
    }
}