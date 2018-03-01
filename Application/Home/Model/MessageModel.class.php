<?php
/**
 * 
 */

namespace Home\Model;

use Think\Model;

/**
 * @package Home\Model
 */
class MessageModel extends Model
{
    protected $tableName = 'message';
    protected $_validate = array();

    /**
     * 获取用户的系统消息
     * @return array
     */
    public function getUserMessageNotice()
    {
        $this->checkPublicMessage();
        $user_info = session('user');
        $user_system_message_no_read_where = array('user_id' => $user_info['user_id'], 'status' => 0, 'category' => 0);
        $user_system_message_no_read = D('UserMessageView')
            ->field('rec_id,message_id,message,send_time')
            ->where($user_system_message_no_read_where)
            ->select();
        return $user_system_message_no_read;
    }

    /**
     * 查询系统全体消息，如有将其插入用户信息表
     * @author dyr
     * @time 2016/09/01
     */
    public function checkPublicMessage()
    {
        $user_info = session('user');
        $model_message = M('message');
        $model_uses_message = M('user_message');

        $user_message = $model_uses_message->where(array('user_id' => $user_info['user_id'], 'category' => 0))->select();
        $time_date = date('Y-m-d H:i:s', $user_info['reg_time']);
        $message_where = array(
            'category' => 0,
            'type' => 1,
            'send_time' => array('gt', $time_date),
        );
        if (!empty($user_message)) {
            $user_id_array = get_arr_column($user_message, 'message_id');
            $message_where['message_id'] = array('NOT IN', $user_id_array);
        }
        $user_system_public_no_read = $model_message->field('message_id')->where($message_where)->select();
        foreach($user_system_public_no_read as $key){
            M('user_message')->data(array('user_id'=>$user_info['user_id'],'message_id'=>$key['message_id'],'category'=>0,'status'=>0))->add();
        }
    }
}