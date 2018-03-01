<?php
/**
 * 
 */
namespace Admin\Model;
use Think\Model;
class GoodsCategoryModel extends Model {
    //protected $tablePrefix = 'tp_'; 
    protected $patchValidate = true; // 系统支持数据的批量验证功能，
    protected $_validate = array(
        array('name','require','分类名称必须填写！',1 ,'',3),  // 1 必须验证  
        //array('name','','分类名称已经存在！',1,'unique',1), // 在新增的时候验证name字段是否唯一               
        array('sort_order','number','排序必须为数字！',2,'',3), //        
     );    
}
