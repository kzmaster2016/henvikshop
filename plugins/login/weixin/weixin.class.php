
<?php
/*
 * 微信扫码登陆插件
 */

class weixin extends \Think\Model\RelationModel{
	public $appid;
	public $secret;
	public $return_url;

	public function __construct($config){
		$this->appid = $config['appid'];
		$this->secret = $config['secret'];
		//$this->return_url = "http://".$_SERVER['HTTP_HOST']."/index.php/Home/ThirdLogin/callback/oauth/weixin";
		$this->return_url = "http://".$_SERVER['HTTP_HOST'].U('LoginApi/callback',array('oauth'=>'weixin'));
		
	}
	//构造要请求的参数数组，无需改动
	public function login(){
		$url = "https://open.weixin.qq.com/connect/qrconnect?appid={$this->appid}&redirect_uri=".urlencode($this->return_url)."&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect";
		echo("<script> top.location.href='" . $url . "'</script>");
		exit;
	}

	public function respon(){
		$code = I('get.code');
		$access_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->secret.'&code='.$code.'&grant_type=authorization_code';
		if($code){
			$this->code = $_GET['code'];
			$result = $this->get_wx_contents($access_token_url);

			$result = json_decode($result,true);
			$access_token = $result['access_token'];
			$openid = $result['openid'];
			$user_info = $this->get_user_info($access_token,$openid);
			return array(
				'openid'=>$openid,//支付宝用户号
				'oauth'=>'weixin',
				'nickname'=>'微信用户',
			);
//			return $user_info;
		}else{
			exit("No code");
		}
	}


	private function get_wx_contents($url){
		$ch = curl_init();
		$timeout = 5;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		return $file_contents;
	}

}


?>
