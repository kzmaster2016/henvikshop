<?php
/**
 * 
 */
namespace Admin\Model;
use Think\Model;
class OrderModel extends Model {
    //protected $tablePrefix = 'tp_';
    protected $patchValidate = true; // 系统支持数据的批量验证功能，
    protected $_validate = array(
        array('consignee','require','收货人称必须填写！',1 ,'',3),  // 1 必须验证
        array('address','require','地址必须填写',1,'',3), // 在新增的时候验证name字段是否唯一
    );
}
