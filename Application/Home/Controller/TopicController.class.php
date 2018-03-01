<?php
/**
 * 
 */ 
namespace Home\Controller;

class TopicController extends BaseController {
	/*
	 * 专题列表
	 */
	public function topicList(){
		$topicList = M('topic')->where("topic_state=2")->select();
		$this->assign('topicList',$topicList);
		$this->display();
	}
	
	/*
	 * 专题详情
	 */
	public function detail(){
		$topic_id = I('topic_id',1);
		$topic = D('topic')->where("topic_id=$topic_id")->find();
		$this->assign('topic',$topic);
		$this->display();
	}
	
	public function info(){
		$topic_id = I('topic_id',1);
		$topic = D('topic')->where("topic_id=$topic_id")->find();                
        echo htmlspecialchars_decode($topic['topic_content']);                
        exit;
	}
}