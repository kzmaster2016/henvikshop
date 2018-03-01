/**
 * 获取省份
 */
function get_province(){
    var url = '/index.php?m=Admin&c=Api&a=getRegion&level=1&parent_id=0';
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option value="0">选择省份</option>'+ v;          
            $('#province').empty().html(v);
        }
    });
}


/**
 * 获取城市
 * @param t  省份select对象
 */
function get_city(t){
    var parent_id = $(t).val();
    if(!parent_id > 0){
        return;
    }
    $('#twon').empty().css('display','none');
    var url = '/index.php?m=Home&c=Api&a=getRegion&level=2&parent_id='+ parent_id;
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option value="0">选择城市</option>'+ v;          
            $('#city').empty().html(v);
        }
    });
}

/**
 * 获取地区
 * @param t  城市select对象
 */
function get_area(t){
    var parent_id = $(t).val();
    if(!parent_id > 0){
        return;
    }
    var url = '/index.php?m=Home&c=Api&a=getRegion&level=3&parent_id='+ parent_id;
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option>选择区域</option>'+ v;
            $('#district').empty().html(v);
        }
    });
}
// 获取最后一级乡镇
function get_twon(obj){
    var parent_id = $(obj).val();
    var url = '/index.php?m=Home&c=Api&a=getTwon&parent_id='+ parent_id;
    $.ajax({
        type : "GET",
        url  : url,
        success: function(res) {
        	if(parseInt(res) == 0){
        		$('#twon').empty().css('display','none');
        	}else{
        		$('#twon').css('display','block');
        		$('#twon').empty().html(res);
        	}
        }
    });
}


/**
 * 输入为空检查
 * @param name '#id' '.id'  (name模式直接写名称)
 * @param type 类型  0 默认是id或者class方式 1 name='X'模式
 */
function is_empty(name,type){
    if(type == 1){
        if($('input[name="'+name+'"]').val() == ''){
            return true;
        }
    }else{
        if($(name).val() == ''){
            return true;
        }
    }
    return false;
}

/**
 * 邮箱格式判断
 * @param str
 */
function checkEmail(str){
    var reg = /^[a-z0-9]([a-z0-9\\.]*[-_]{0,4}?[a-z0-9-_\\.]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+([\.][\w_-]+){1,5}$/i;
    if(reg.test(str)){
        return true;
    }else{
        return false;
    }
}
/**
 * 手机号码格式判断
 * @param tel
 * @returns {boolean}
 */
function checkMobile(tel) {
    var reg = /(^1[3|4|5|7|8][0-9]{9}$)/;
    if (reg.test(tel)) {
        return true;
    }else{
        return false;
    };
}

/*
 * 上传图片 后台专用
 * @access  public
 * @null int 一次上传图片张图
 * @elementid string 上传成功后返回路径插入指定ID元素内
 * @path  string 指定上传保存文件夹,默认存在Public/upload/temp/目录
 * @callback string  回调函数(单张图片返回保存路径字符串，多张则为路径数组 )
 */
function GetUploadify(num,elementid,path,callback)
{	   	
	var upurl ='/index.php?m=Admin&c=Uploadify&a=upload&num='+num+'&input='+elementid+'&path='+path+'&func='+callback;
	var iframe_str='<iframe frameborder="0" ';
	iframe_str=iframe_str+'id=uploadify ';   		
	iframe_str=iframe_str+' src='+upurl;
	iframe_str=iframe_str+' allowtransparency="true" class="uploadframe" scrolling="no"> ';
	iframe_str=iframe_str+'</iframe>';    	    		
	$("body").append(iframe_str);	
	$("iframe.uploadframe").css("height",$(document).height()).css("width","100%").css("position","absolute").css("left","0px").css("top","0px").css("z-index","999999").show();
	$(window).resize(function(){
		$("iframe.uploadframe").css("height",$(document).height()).show();
	});
}

/*
 * 上传图片 前台专用
 * @access  public
 * @null int 一次上传图片张图
 * @elementid string 上传成功后返回路径插入指定ID元素内
 * @path  string 指定上传保存文件夹,默认存在Public/upload/temp/目录
 * @callback string  回调函数(单张图片返回保存路径字符串，多张则为路径数组 )
 */
function GetUploadify2(num,elementid,path,callback)
{	   	
	var upurl ='/index.php?m=Home&c=Uploadify&a=upload&num='+num+'&input='+elementid+'&path='+path+'&func='+callback;
	var iframe_str='<iframe frameborder="0" ';
	iframe_str=iframe_str+'id=uploadify ';   		
	iframe_str=iframe_str+' src='+upurl;
	iframe_str=iframe_str+' allowtransparency="true" class="uploadframe" scrolling="no"> ';
	iframe_str=iframe_str+'</iframe>';    	    		
	$("body").append(iframe_str);	
	$("iframe.uploadframe").css("height",$(document).height()).css("width","100%").css("position","absolute").css("left","0px").css("top","0px").css("z-index","999999").show();
	$(window).resize(function(){
		$("iframe.uploadframe").css("height",$(document).height()).show();
	});
}
/*
 * 删除组图input
 * @access   public
 * @val  string  删除的图片input
 */
function ClearPicArr(val)
{
	$("li[rel='"+ val +"']").remove();
	$.get(
		"{:U('Admin/Uploadify/delupload')}",{action:"del", filename:val},function(){}
	);
}
/*
 * 删除组图input
 * @access   public
 * @val  string  删除的图片input
 */
function ClearPicArr2(val)
{
    $("li[rel='"+ val +"']").remove();
    $.get(
        "{:U('Home/Uploadify/delupload')}",{action:"del", filename:val},function(){}
    );
}


/**
 * addcart 将商品加入购物车
 * @goods_id  商品id
 * @num   商品数量
 * @form_id  商品详情页所在的 form表单
 * @to_catr 加入购物车后再跳转到 购物车页面 默认不跳转 1 为跳转
 */
function AjaxAddCart(goods_id,num,to_catr)
{                                                
        // 如果有商品规格 说明是商品详情页提交
        if($("#buy_goods_form").length > 0){        
                $.ajax({
                        type : "POST",
                        url:"/index.php?m=Home&c=Cart&a=ajaxAddCart",
                        data : $('#buy_goods_form').serialize(),// 你的formid 搜索表单 序列化提交                        
						dataType:'json',
                        success: function(data){	
							   // 加入购物车后再跳转到 购物车页面
							   if(to_catr == 1)  //直接购买
							   {
								   location.href = "/index.php?m=Home&c=Cart&a=cart";   
							   }
							   else
							   {
								    if(data.status < 0)
									{
										layer.alert(data.msg, {icon: 2});
										return false;
									}
								    cart_num = parseInt($('#cart_quantity').html())+parseInt($('input[name="goods_num"]').val());
								    $('#cart_quantity').html(cart_num)
									layer.open({
										  type: 2,
										  title: 'Tips',
										  skin: 'layui-layer-rim', //加上边框
										  area: ['490px', '386px'], //宽高
										  content:"/index.php?m=Home&c=Goods&a=open_add_cart"
									});
							   }
                        }
                });     
        }else{ // 否则可能是商品列表页 收藏页 等点击加入购物车的
                $.ajax({
                        type : "POST",
                        url:"/index.php?m=Home&c=Cart&a=ajaxAddCart",
                        data :{goods_id:goods_id,goods_num:num} ,
						dataType:'json',
                        success: function(data){   							   							   
							   if(data.status == -1)
							   {
									location.href = "/index.php?m=Home&c=Goods&a=goodsInfo&id="+goods_id;   
							   }
							   else
							   {
								    // 加入购物车有误
								    if(data.status < 0)
									{
										layer.alert(data.msg, {icon: 2});
										return false;
									}
								    cart_num = parseInt($('#cart_quantity').html())+parseInt(num);
								    $('#cart_quantity').html(cart_num)
									layer.open({
										  type: 2,
										  title: 'Tips',
										  skin: 'layui-layer-rim', //加上边框
										  area: ['490px', '386px'], //宽高
										  content:"/index.php?m=Home&c=Goods&a=open_add_cart"
									});							   
							   }							   							   
                        }
                });            
        }
}


// 点击收藏商品
function collect_goods(goods_id){
	$.ajax({
		type : "GET",
		dataType: "json",
		url:"/index.php?m=Home&c=goods&a=collect_goods&goods_id="+goods_id,//+tab,
		success: function(data){
			alert(data.msg);
		}
	});
}	


// 获取活动剩余天数 小时 分钟
//倒计时js代码精确到时分秒，使用方法：注意 var EndTime= new Date('2013/05/1 10:00:00'); //截止时间 这一句，特别是 '2013/05/1 10:00:00' 这个js日期格式一定要注意，否则在IE6、7下工作计算不正确哦。
//js代码如下：
function GetRTime(end_time){
      // var EndTime= new Date('2016/05/1 10:00:00'); //截止时间 前端路上 http://www.51xuediannao.com/qd63/
	   var EndTime= new Date(end_time); //截止时间 前端路上 http://www.51xuediannao.com/qd63/
       var NowTime = new Date();
       var t =EndTime.getTime() - NowTime.getTime();
       /*var d=Math.floor(t/1000/60/60/24);
       t-=d*(1000*60*60*24);
       var h=Math.floor(t/1000/60/60);
       t-=h*60*60*1000;
       var m=Math.floor(t/1000/60);
       t-=m*60*1000;
       var s=Math.floor(t/1000);*/

       var d=Math.floor(t/1000/60/60/24);
       var h=Math.floor(t/1000/60/60%24);
       var m=Math.floor(t/1000/60%60);
       var s=Math.floor(t/1000%60);
	   if(s >= 0){
	       return d + 'days' + h + 'hours' + m + 'mins' +s+'seconds';
       }
   }
   
   
/**
 * 获取多级联动的商品分类
 */
function get_category(id,next,select_id){
    var url = '/index.php?m=Home&c=api&a=get_category&parent_id='+ id;
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
			v = "<option value='0'>请选择商品分类</option>" + v;
            $('#'+next).empty().html(v);
			(select_id > 0) && $('#'+next).val(select_id);//默认选中
        }
    });
}   