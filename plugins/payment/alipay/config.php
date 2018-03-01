<?php
return array(
    'code'=> 'alipay',
    'name' => 'PC端支付宝',
    'version' => '1.0',
    'author' => 'jy_pwn',
    'desc' => 'PC端支付宝插件 ',
    'scene' =>2,  // 使用场景 0 PC+手机 1 手机 2 PC
    'icon' => 'logo.jpg',
    'config' => array(
        array('name' => 'alipay_account','label'=>'支付宝帐户',           'type' => 'text',   'value' => ''),
        array('name' => 'alipay_key','label'=>'交易安全校验码',               'type' => 'text',   'value' => ''),
        array('name' => 'alipay_partner','label'=>'合作者身份ID',           'type' => 'text',   'value' => ''),
        array('name' => 'alipay_private_key','label'=>'秘钥',           'type' => 'textarea',   'value' => ''),
        array('name' => 'alipay_pay_method','label'=>' 选择接口类型',        'type' => 'select', 'option' => array(
          '0' =>  '使用担保交易接口',
          '1' => '使用即时到帐交易接口',
        )),
        array('name' => 'is_bank','label'=>'是否开通网银',        'type' => 'select', 'option' => array(
            '0' => '否',
            '1' =>  '是',
        )),
    ),
    'bank_code'=>array(
            '招商银行'=>'CMB-DEBIT',
            '中国工商银行'=>'ICBC-DEBIT',
            '交通银行'=>'COMM-DEBIT',
            '中国建设银行'=>'CCB-DEBIT',
            '中国民生银行'=>'CMBC',
            '中国银行'=>'BOC-DEBIT',
            '中国农业银行'=>'ABC',        
            '上海银行'=>'SHBANK',                                           
    )
);