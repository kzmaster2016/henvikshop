<?php
/**
 * 
 */

namespace Home\Model;

use Think\Model\ViewModel ;

/**
 * Class UserAddressModel
 * @package Home\Model
 */
class UserMessageViewModel extends ViewModel
{
    public $viewFields = array(
        'user_message' => array('rec_id', 'user_id','category', 'message_id','status','_type'=>'LEFT'),
        'message' => array('message', 'send_time','type','_on'=>'user_message.message_id=message.message_id')
    );
}