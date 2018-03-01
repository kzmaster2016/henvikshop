$(document).ready(function(){
  /*图片懒加载*/
  $("img").lazyload({
  	 effect:"fadeIn",
  	 threshold:200,
  	 failurelimit:20    
  }); 
  
  //顶部广告关闭
  $(".icon-close").click(function(){
	  $(this).parent().remove();
  });

});

//#############导航菜单效果
  var mq = [],mp = [];
  $('.nav-list-warpper li').each(function(){
	  var i = parseInt($(this).attr('index'));
	  mp[i] = $(this);
  });
  
  $('.nav-list .menu-sub').each(function(){
	  var i = parseInt($(this).attr('index'));
	  mq[i] = $(this);
	  $(this).mouseenter(function(){
		  mp[i].mouseenter();
	  });
	  $(this).mouseleave(function(){
		  mp[i].mouseleave();
	  });
  });
   
  $('.nav-list-warpper li').hover(function(){
	    var i = parseInt($(this).attr('index'));
	    $('.nav-list .menu-sub').removeClass("menu-sub-show");
	    $('.nav-list-warpper li').removeClass('nav-item-hover');
	    $(this).addClass('nav-item-hover');
	    //导航广告图懒加载
	    mq[i].find(".nav-prod").each(function () {
           var u = $(this);
           if(u.hasClass("nav-prod")){
              u.attr("src", u.attr("data-images"));
              u.removeClass("nav-prod");
           }
	    });
	    
	    mq[i].stop().addClass("menu-sub-show").animate({
	        left: 190,
	        opacity: .95
	    }, 200);
	}, function(){
	  	var i = parseInt($(this).attr('index'));
	  	$(this).removeClass('nav-item-hover');
	  	mq[i].stop().animate({
	          left: 180,
	          opacity: .7
	      }, 200, function () {
	      	mq[i].removeClass("menu-sub-show");
	    });
  });
  
  
//##############购物车内容显示  
var header_cart_list_over = 0; 
$('#hd-my-cart').hover(function(){
	$('#show_minicart').show();
	$('.minicartContent div').eq(0).removeClass('fn-hide');
    if(header_cart_list_over == 1) 
		return false;
    header_cart_list_over = 1; 
	$.ajax({
		type : "GET",
		url:"/index.php?m=Home&c=Cart&a=header_cart_list",//+tab,
		success: function(data){								 
		 	$("#hd-my-cart > #show_minicart").html(data);
		 	get_cart_num();
		}
	});	
},function(){
	$('#show_minicart').hide();
	$('.minicartContent div').eq(0).addClass('fn-hide');
	 (typeof(t) == "undefined") || clearTimeout(t); 	 
	 t = setTimeout(function () { 
			header_cart_list_over = 0; /// 标识鼠标已经离开	        
	 }, 2000);	
});

$('#hd-my-cart').click(function(){
	top.location.href="/index.php?m=Home&c=Cart&a=cart";
})

// ajax 刷新购物车的商品
function header_cart_del(ids)
{
	$.ajax({
		type:"POST",
		url:"/index.php?m=Home&c=Cart&a=ajaxDelCart",
		data:{ids:ids},
	    dataType:'json',		
		success:function(data){
		   if(data.status == 1)				
			{
				header_cart_list_over = 0; /// 标识鼠标已经离开
				$("#hd-my-cart").trigger('mouseenter');	 // 无法触发 hover 改为 trigger('mouseenter');
			}
		}
	});
}
