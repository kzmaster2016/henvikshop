<?php
/**
 * 
 */ 
namespace Home\Controller;
use Home\Logic\GoodsLogic;
use Home\Logic\CartLogic;
use Think\AjaxPage;
use Think\Page;
use Think\Verify;

class ActivityController extends BaseController {
    public function index(){      
        $this->display();
    }
   /**
    * 商品详情页
    */ 
    public function group(){
        //form表单提交
        C('TOKEN_ON',true);        
        
        $goodsLogic = new \Home\Logic\GoodsLogic();
        $goods_id = I("get.id");
        
        $group_buy_info = M('GroupBuy')->where("goods_id = $goods_id and ".time()." >= start_time and ".time()." <= end_time ")->find(); // 找出这个商品
        if(empty($group_buy_info)) 
        {
            $this->error("此商品没有团购活动",U('Home/Goods/goodsInfo',array('id'=>$goods_id)));
            exit;
        }
                    
        $goods = M('Goods')->where("goods_id = $goods_id")->find();
        $goods_images_list = M('GoodsImages')->where("goods_id = $goods_id")->select(); // 商品 图册
                
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
                        
        $Model = new \Think\Model();        
        // 商品规格 价钱 库存表 找出 所有 规格项id
        $keys = M('SpecGoodsPrice')->where("goods_id = $goods_id")->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");         
        if($keys)
        {
             $specImage =  M('SpecImage')->where("goods_id = $goods_id and src != '' ")->getField("spec_image_id,src");// 规格对应的 图片表， 例如颜色                
             $keys = str_replace('_',',',$keys);             
             $sql  = "SELECT a.name,a.order,b.* FROM __PREFIX__spec AS a INNER JOIN __PREFIX__spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY a.order";
             $filter_spec2 = $Model->query($sql);             
             foreach($filter_spec2 as $key => $val)
             {                                  
                 $filter_spec[$val['name']][] = array(
                     'item_id'=> $val['id'],
                     'item'=> $val['item'],
                     'src'=>$specImage[$val['id']],
                     );                 
             }            
        }                
        $spec_goods_price  = M('spec_goods_price')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        M('Goods')->where("goods_id=$goods_id")->save(array('click_count'=>$goods['click_count']+1 )); // 统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        $navigate_goods = navigate_goods($goods_id,1); // 面包屑导航        
                
        $this->assign('group_buy_info',$group_buy_info);
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        $this->assign('navigate_goods',$navigate_goods);
        $this->assign('commentStatistics',$commentStatistics);
        $this->assign('goods_attribute',$goods_attribute);       
        $this->assign('goods_attr_list',$goods_attr_list);
        $this->assign('filter_spec',$filter_spec);
        $this->assign('goods_images_list',$goods_images_list);
        $this->assign('goods',$goods);
        $this->display();
    }     
    /**
     * 团购活动列表
     */
    public function group_list()
    {
    	$count =  M('GroupBuy')->where(time()." >= start_time and ".time()." <= end_time ")->count();// 查询满足要求的总记录数
    	$Page = new \Think\Page($count,20);// 实例化分页类 传入总记录数和每页显示的记录数  	
    	$show = $Page->show();// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
        $list = M('GroupBuy')->where(time()." >= start_time and ".time()." <= end_time ")->limit($Page->firstRow.','.$Page->listRows)->select(); // 找出这个商品        
        $this->assign('list', $list);
        $this->display();
    }

    /*
    *New Arrival
    */
    public function new_arrival(){
        $filter_param = array(); // 帅选数组                          
        $sort = I('get.sort','goods_id'); // 排序
        $sort_asc = I('get.sort_asc','asc'); // 排序
        $price = I('get.price',''); // 价钱
        $start_price = trim(I('post.start_price','0')); // 输入框价钱
        $end_price = trim(I('post.end_price','0')); // 输入框价钱        
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
                         
        $price  && ($filter_param['price'] = $price); //加入帅选条件中
                
        $goodsLogic = new \Home\Logic\GoodsLogic(); // 前台商品操作逻辑类  

        // 帅选 品牌 规格 属性 价格
        $filter_goods_id = M('goods')->where("is_on_sale=1 and is_new=1")->cache(true)->getField("goods_id",true);                 
        $filter_menu  = $goodsLogic->get_filter_menu($filter_param,'goodsList'); // 获取显示的帅选菜单
        $filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'goodsList'); // 帅选的价格期间     
                              
        $count = count($filter_goods_id);
        $page = new Page($count,40);
        if($count > 0)
        {
            $goods_list = M('goods')->where("goods_id in (".  implode(',', $filter_goods_id).")")->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
            foreach ($goods_list as $key => $value) {
                if ($value['prom_type'] > 0) {
                    $goods_list[$key]['goods_promotion'] = get_goods_promotion($value['goods_id']); 
                }
            }

            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');

            if($filter_goods_id2){
                $goods_images = M('goods_images')->where("goods_id in (".  implode(',', $filter_goods_id2).")")->cache(true)->select(); 
            }
        }
      
        $this->assign('goods_list',$goods_list);       
        $this->assign('goods_images',$goods_images);  // 相册图片
        $this->assign('filter_menu',$filter_menu);  // 帅选菜单
        $this->assign('filter_price',$filter_price);// 帅选的价格期间
        $this->assign('filter_param',$filter_param); // 帅选条件
        $this->assign('page',$page);// 赋值分页输出
        $this->display();
    }

    /*
    *Flash Sale
    */
    public function flash_sale(){
        $model = M('flash_sale');
        $count = $model->where(time()." >= start_time and ".time()." <= end_time ")->count();
        $Page  = new \Think\Page($count,12); 
        $show = $Page->show();
        $this->assign('page',$show);// 赋值分页输出         

        $flash_list = $model->where(time()." >= start_time and ".time()." <= end_time ")->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($flash_list as $k => $v) {
            $origin_price = M('goods')->where('goods_id = '.$v['goods_id'])->getField('shop_price');
            $flash_list[$k]['goods_price']=$origin_price;
        }
        $this->assign('list',$flash_list);
        $this->display();
    }

    /**
    *Promotions 促销活动
    */
    public function promotion_list(){
        $prom_model = M('prom_goods');
        $promotion_list = $prom_model->where("is_close = 0")->order("id asc")->limit(3)->select();

        $last_chance_list = $this->get_promotion_goods(1);
        $on_going_list    = $this->get_promotion_goods(2);
        $will_come_list   = $this->get_promotion_goods(3);

        $this->assign('promotion_list',$promotion_list);

        $this->assign('last_chance_list',$last_chance_list);
        $this->assign('on_going_list',$on_going_list);
        $this->assign('will_come_list',$will_come_list);
        $this->display();
    }

    protected function get_promotion_goods($prom_id){
        $count = M('goods')->where("prom_id=$prom_id and prom_type=3")->count();
        $page  = new \Think\Page($count,10);
        $list  = M('goods')->where("prom_id=$prom_id and prom_type=3")->order('goods_id DESC')->limit($page->firstRow.','.$page->listRows)->select();

        foreach ($list as $key => $value) {
            if ($value['prom_type'] > 0) {
                $list[$key]['goods_promotion'] = get_goods_promotion($value['goods_id']); 
            }
        }
        return $list;
    }
}