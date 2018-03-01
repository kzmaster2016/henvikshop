<?php
/**
 * 
 */

namespace Admin\Controller;

use Think\Page;
use Think\AjaxPage;

class PickupController extends BaseController {

    public function index(){
		$p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
		$this->assign('province',$p);
        $this->display();
    }

	public function ajaxPickupList(){
		$province_id = I('post.province_id');
		$city_id = I('post.city_id');
		$district_id = I('post.district_id');
		$order_by_field = I('post.order_by_field','pickup_id');
		$order_by_mode = I('post.order_by_mode','desc');
		$key_word = I('post.key_word');
		if(!empty($province_id)){
			$pickup_where['p.province_id'] = $province_id;
		}
		if(!empty($city_id)){
			$pickup_where['p.city_id'] = $city_id;
		}
		if(!empty($district_id)){
			$pickup_where['p.district_id'] = $district_id;
		}
		if(!empty($key_word)){
			$map['p.pickup_name'] = array('like',$key_word);
		}

		$model = M('');
		$db_prefix = C('DB_PREFIX');
		$count = $model->table($db_prefix.'pick_up p')->where($pickup_where)->count();
		$Page  = new AjaxPage($count,10);
		$show = $Page->show();

		$pickupList = $model
				->field('p.*,r1.name as province_name,r2.name as city_name,r3.name as district_name,s.suppliers_name')
				->table($db_prefix.'pick_up p')
				->join('LEFT JOIN '.$db_prefix.'region AS r1 ON r1.id = p.province_id')
				->join('LEFT JOIN '.$db_prefix.'region AS r2 ON r2.id = p.city_id')
				->join('LEFT JOIN '.$db_prefix.'region AS r3 ON r3.id = p.district_id')
				->join('LEFT JOIN '.$db_prefix.'suppliers AS s ON s.suppliers_id = p.suppliersid')
				->where($pickup_where)
				->order($order_by_field.' '.$order_by_mode)
				->limit($Page->firstRow.','.$Page->listRows)
				->select();
		$this->assign('pickupList',$pickupList);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}

	public function add()
	{
		if (IS_POST) {
			$data = I('post.');
			$pickup_id = I('post.pickup_id');
			$model = M('pick_up');
			if (empty($pickup_id)) {
				//添加
				unset($pickup_id);
				$add_res = $model->add($data);
				if ($add_res === false) {
					$this->error('添加失败', U('Admin/Pickup/add'));
				} else {
					$this->error('添加成功', U('Admin/Pickup/index'));
				}
			} else {
				//修改
				$update_res = $model->where(array('pickup_id' => $pickup_id))->save($data);
				if ($update_res === false) {
					$this->error('更新失败', U('Admin/Pickup/edit_address', array('pickup_id' => $pickup_id)));
				} else {
					$this->error('更新成功', U('Admin/Pickup/index'));
				}
			}
		}
		$suppliers = M('suppliers')->where(array('is_check' => 1))->select();
		$p = M('region')->where(array('parent_id' => 0, 'level' => 1))->select();
		$this->assign('province', $p);
		$this->assign('suppliers', $suppliers);
		$this->display();
	}

	/**
    * 地址编辑
    */
	public function edit_address(){
		$id = I('get.pickup_id');
		$pickup = M('pick_up')->where(array('pickup_id'=>$id))->find();
		//获取省份
		$p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
		$c = M('region')->where(array('parent_id'=>$pickup['province_id'],'level'=> 2))->select();
		$d = M('region')->where(array('parent_id'=>$pickup['city_id'],'level'=> 3))->select();

		$suppliers = M('suppliers')->where(array('is_check'=>1))->select();

		$this->assign('province',$p);
		$this->assign('city',$c);
		$this->assign('district',$d);
		$this->assign('suppliers',$suppliers);
		$this->assign('pickup',$pickup);
		$this->display('add');
	}

	public function del()
	{
		$id = I('get.pickup_id');
		M('pick_up')->where(array('pickup_id'=>$id))->delete();
		$return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);
		$this->ajaxReturn(json_encode($return_arr));
	}
}