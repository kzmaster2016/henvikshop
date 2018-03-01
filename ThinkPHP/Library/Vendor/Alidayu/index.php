<?php
/**
 * Created by PhpStorm.
 * User: uxeix
 * Date: 2016/4/6
 * Time: 17:59
 * 阿里大鱼短信注册
 */
include('./TopClient.php'); //这个是你下面实例化的类
include('./ResultSet.php'); //这个是topClient 里面需要实例化一个类所以我们也要加载 不然会报错
include('./RequestCheckUtil.php'); //这个是成功后返回的信息文件
include('./TopLogger.php');   //这个是错误信息返回的一个php文件
include('./AlibabaAliqinFcSmsNumSendRequest.php'); //这个也是你下面示例的类

$c = new TopClient;
$c->appkey = "23302950";  //App Key的值 这个在开发者控制台的应用管理点击你添加过的应用就有了
$c->secretKey = "0cb345ffeb990e6db06a513b7c293259"; //App Secret的值也是在哪里一起的 你点击查看就有了
$req = new AlibabaAliqinFcSmsNumSendRequest;
$req->setExtend("123456"); //这个是用户名记录那个用户操作
$req->setSmsType("normal"); //这个不用改你短信的话就默认这个就好了
$req->setSmsFreeSignName("注册验证"); //这个是签名
$req->setSmsParam("{'code':'1234','product':'孤独患者'}"); //这个是短信签名
$req->setRecNum("18670953667"); //这个是写手机号码
$req->setSmsTemplateCode("SMS_4780636"); //这个是模版ID 主要也是短信内容
$resp = $c->execute($req);
var_dump($resp);