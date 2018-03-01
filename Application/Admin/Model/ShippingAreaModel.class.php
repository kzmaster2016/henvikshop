<?php
/**
 * 
 */

namespace Admin\Model;
use Think\Model;
class ShippingAreaModel extends Model {

    /**
     * 获取配送区域
     * @return mixed
     */
    public function getShippingArea()
    {
        $shipping_areas = M('shipping_area')->where('')->select();
        foreach($shipping_areas as $key => $val){
            $shipping_areas[$key]['config'] = unserialize($shipping_areas[$key]['config']);
        }
        return $shipping_areas;
    }

}
