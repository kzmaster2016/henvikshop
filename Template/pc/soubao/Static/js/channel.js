/*************************************************
 *  Function  channel
 *  Copyright frontEnd http://www.tp-shop.cn/
 *  Designed and built by frontEnd  @yangyang.li
 *  Date 2016/03/14
 ************************************************/

/**
 ** all plug-in
 ** @@1 移除边框边距
 **/ 

;(function($){

	// @@1
	$.fn.removeBorMar = function(options){  

		var defaults = {},

		    ops= $.extend({},defaults,options),

		    $ts = $(this);

		if($ts.length){

			// 清除边框
			$ts.css("border","none");

		}
	}

	function rmbr(cls){

		var cls = cls || ".g-rmb";

		$(cls).find("li:last").css("border","none");

	}

	return rmbr();

})(jQuery);


/**
 懒加载
 **/

 $(function(){
    //延迟加载
    $(".fn_img_lazy").lazyload({

        placeholder: "",

        threshold: 0,

        effect: "show",

        skip_invisible: false
    }); 

 });


var indexEffect = {

	init:function(){

		var $self = this;

        if($(".m-flr").length){

            $self.floors();

        }
			
		$self.navShow(".m-lst li",".m-dtl .g-snv","svn");        

	},

	navShow:function(){

		var arg1 = arguments[0],

			arg2 = arguments[1],

			arg3 = arguments[2] || undefined;

		$(arg1).mouseenter(function() {

			var ts = $(this);

			tm = setTimeout(function() {

				var idx = ts.index();

				$(arg1).removeClass('s-select').eq(idx).addClass('s-select');

				if(arg3 != "undefined"){

					$(arg2).eq(idx).css("margin-top",idx*32+40);

				}
				
				$(arg2).hide().eq(idx).show();

			}, 50);

		}).mouseleave(function() {
			/* Act on the event */
			clearTimeout(tm);

		});

		if(arg3 != "undefined"){

			$(".m-sld-lst").on("mouseleave",function(){

				$(arg1).removeClass('s-select');

				$(arg2).hide();

			});

		}


	},

	// floor
	floors:function(){

		var offsettop = $(".m-flr:first").offset().top,

			yCenter = $(window).height() / 2;
		
		// console.log(document.body.scrollTop)

		// 判断楼层显示
		$(window).scroll(function(event) {

			var scrolltop = $(window).scrollTop();

			if(offsettop - scrolltop <= 150) {

				$(".g-ab-fx").show();

				$(".g-flr").css({"opacity":1,"transform":"scale(1)","visibility":"visible"});

			}else{

				$(".g-flr").css({"opacity":0,"transform":"scale(1.2)","visibility":"hidden"});

				$(".g-ab-fx").removeAttr("style");

			}	

			// 右侧楼层挂件联动左侧楼层
			var $lf_flr = $(".m-flr");

			$lf_flr.each(function(idx,el){

				var $ts = $(this),

					off_top = $ts.offset().top,

					f_bottom = $ts.offset().top + $ts.outerHeight();

				if (scrolltop + yCenter > off_top && scrolltop + yCenter < f_bottom) { 

					$(".g-flr li").eq(idx).addClass('s-select').siblings().removeClass('s-select');

				}

			});


		});

		// 锚点点击跳转到相应楼层
		$(".g-flr").on( "click" , "li" , function(){

            if(!$(this).hasClass('backtop')){

    			var $index = $(this).index(),	

    				$hei = $(".m-flr").eq($index).offset().top - 150;

    			$(".g-flr li").removeClass('s-select').eq($index).addClass('s-select');

    			$("html,body").stop(true,true).animate({"scrollTop":$hei},400);

            }

		});
	}
}  

// removeborder 
function removeborder(){
    // 移除边框
    $(".m-lst li:last").find("dl").removeBorMar();
}

window.onload = removeborder;

var main = {
    init:function(){
        // 绑定方法
        this.bindEvent();
        // floor tab
        this.floorTab();
    },

    bindEvent:function(){
        // TO DO
        // 主大图轮播
        $('#slideMain').fn_slide({

            has_num: false,

            type: "fadeIn",

            time: 5000,

            auto: true,

            hide_page_btn: true

        });

        // 右侧广告
        $('#slideAd').fn_slide({

            has_num: false,

            type: "move",

            flip: false
        });

        // 判断右侧广告图是否存在设定宽
        var sId = $("#slideAd"),

            sSlide = $("#slideMain"),

            nWid = 1000,

            nWid2 = 714,

            nMar = 440;

        if(sId.find(".slider-main li").length === 0){

            sSlide.find(".slider-nav").css({

                "width": nWid +"px"

            });

             sSlide.find(".J_page_box .slider-next").css({

                "margin-left": nMar +"px"

            });

        }else{

            sSlide.find(".slider-nav").css({

                "width": nWid2 +"px"

            });

        }

        // floor slide 
        $('.J-f-sld').fn_slide({

            has_num: false,

            type: "move",

            auto: true,

            time: 5000,

            hide_page_btn: true
        });

        // 下期预告
        $(".J_pictureArea").each(function() {

            var self = $(this);

            self.find("li img[data-src]").each(function(key,item) {

                $(item).attr("src", $(item).attr("data-src")).removeAttr("data-src");

            });

        });
        // 默认展示轮播图背景
        this.bgChange(0);
    },

    floorTab:function(){

        var sTab = ".J-tab li",

            sTabCnt = ".J-tabConent .J-hide",

            sflr = ".m-flr",

            fTime = null;

        $(sflr).each(function(){

            $(this).find(sTab).eq(0).addClass('z_select');

            $(this).find(sTabCnt).hide().eq(0).show();
            
        });   

        $(document).on({
            
            "mouseenter":function(){

                var oTs = $(this);

                fTime = setTimeout(function(){

                    var sIndex = oTs.index();

                    oTs.addClass('z_select').siblings().removeClass('z_select');

                    oTs.parents(".J-parent").find(sTabCnt).hide().eq(sIndex).show();

                },300);

            },

            "mouseleave":function(){

                clearTimeout(fTime);

            }

        },sTab);

    },


    bgChange:function(index){
        // TO DO
        var sBgpic = $("#slideMain").find(".slider-main li").eq(index).attr("data-bgimage"),

            sBgColor = $("#slideMain").find(".slider-main li").eq(index).attr("data-bgcolor");

        if(typeof sBgpic === "undefined" && typeof sBgColor === "undefined") {

            $("#bgcolor").removeAttr("style");

        }
        else if(typeof sBgpic !== "undefined" && typeof sBgColor === "undefined"){

            $("#bgcolor").css("background","url("+sBgpic+") no-repeat center top");

        }
        else if(typeof sBgpic === "undefined" && typeof sBgColor !== "undefined"){

            $("#bgcolor").css("background",sBgColor);

        }
    }
}

$(function(){
    main.init();
    indexEffect.init();
});

;(function($) {
	
    $.fn.fn_slide = function(options) {
        // build main options before element iteration
        var opts = $.extend({}, $.fn.fn_slide.defaults, options);
        // iterate and reformat each matched element
        return this.each(function() {

            var $this = $(this);

            // build element specific options
            var o = $.meta ? $.extend({}, opts, $this.data()) : opts;

            var active = opts.active || "z-select",
                time = null;

            var ele = {

                main: "slider-main",

                num: "slider-nav",

                num_li: "slider-item",

                next_btn: "slider-next",

                prev_btn: "slider-prev",

                page_box:"J_page_box"

            };

            //get img contains
            var _ul = $this.children('.' + ele.main);
            //get li's length
            var _len = _ul.children("li").length;
            //create slide Ctrl
            var str = "";
            //init _index
            var _index = 0;

            var _height = _ul.children("li").height();

            var _width = $this.width();

            //set ul's width
            _ul.width(_width * _len);
            //set li's width
            //_ul.children("li").width(_width);

            var obj = {

                is_flip: o.flip,

                is_fade: (o.type === "fadeIn"),

                is_move: (o.type === "move"),

                is_up: (o.type === "up"),

                throttle_t: 200,

                init: function() {

                    //ctrl
                    o.has_ctrl && $this.append('<ul class="s-ctr ' + ele.num + '">' + showCtrl() + '</ul>');

                    // 当前图片为1 圆点隐藏
                    if(_len <= 1) {
                        $this.find("." + ele.num).hide();
                    }

                    //是否需要上一页下一页
                    obj.is_flip && showFlip();

                    //初始化渐变还是左右
                    if (obj.is_fade) {

                       _ul.children("li:first").addClass('z-select').css({
                            "position": "absolute",
                            "zIndex": 1,
                            "opacity": 1
                        })
                            .siblings().removeClass('z-select').css({
                                "position": "absolute",
                                "zIndex": 0,
                                "opacity": 0
                            });

                        //todo
                        $this.on('mouseenter', '.' + ele.num_li, function() {

                            var $this = $(this);

                            clearTimeout(time);

                            time = setTimeout(function(){

                                _index = $this.index();

                                _ul.children("li").eq(_index).addClass('z-select').css("zIndex", 1).siblings().removeClass('z-select').css("zIndex", 0);

                                _ul.children("li").eq(_index).stop(true).animate({

                                    "opacity": 1

                                }, {
                                    duration: o.duration,

                                    complete: function() {

                                        _ul.children("li").eq(_index).siblings().css({
                                            "opacity": 0
                                        });

                                    }

                                });

                               $this.addClass(active).siblings().removeClass(active);

                               // roll 大背景底
                               if($this.parents("#slideMain").length){

                                    main.bgChange(_index);

                               }                               

                            },obj.throttle_t);


                        }).on('mouseleave', '.' + ele.num_li, function() {

                            clearTimeout(time);

                        });


                    } else if (obj.is_move) {

                        //bind event on <li>
                        $this.on('mouseenter', '.' + ele.num_li, function() {

                            var $this = $(this);

                            clearTimeout(time);

                            time = setTimeout(function(){

                                _index = $this.index();

                                _ul.stop(true).animate({

                                    "marginLeft": -(_width * _index)

                                }, o.duration);

                                $this.addClass(active).siblings().removeClass(active);

                            },obj.throttle_t);


                        }).on('mouseleave', '.' + ele.num_li, function() {

                            clearTimeout(time);

                        });


                    } else if (obj.is_up) {//晒单向上滚动

                        _ul.width(_width);

                        _ul.height(_height * _len);

                    }

                    //如果上下页切换按钮是隐藏的话

                    o.hide_page_btn && $this.on({

                        mouseenter:function(){

                            if($(this).find("."+ele.page_box).is(":hidden") && $(this).find("."+ele.main+" >li").length >1){

                                $(this).find("."+ele.page_box).show();

                            }

                        },

                        mouseleave:function(){

                            $(this).find("."+ele.page_box).hide();

                        }

                    });

                    //定时器
                    obj.timeFuc();

                },

                timeFuc: function() {

                    //auto 为true 表示可自动

                    if(o.auto && _len >1){

                        //setInterval
                        time = setInterval(showEffect, o.time);

                        // clearInterval
                        $this.on({

                            mouseenter: function() {

                                clearInterval(time);

                            },
                            mouseleave: function() {

                                clearInterval(time);

                                time = setInterval(showEffect, o.time);

                            }

                        });

                    }

                }

            };

            //初始化
            obj.init();

            //1->next 0->prev
            function showEffect() {


                if (o.type === "fadeIn") {

                    arguments.length ? _index-- : _index++;

                    if (_index === -1) {

                        _index = _len - 1;

                    } else if (_index === _len) {

                        _index = 0;

                    }

                    _ul.children("li").eq(_index).addClass('z-select').css("zIndex", 1).siblings().removeClass('z-select').css("zIndex", 0);
                    _ul.children("li").eq(_index).stop(true).animate({

                        "opacity": 1

                    }, {
                        duration: o.duration,

                        complete: function() {

                            _ul.children("li").eq(_index).siblings().css({
                                "opacity": 0
                            });

                        }

                    });

                    $this.find("." + ele.num_li).eq(_index).addClass(active).siblings().removeClass(active);

                    // roll 大背景底
                    if($this.length){

                        main.bgChange(_index);

                    }

                } else if (o.type === "move") {

                    if (arguments.length) {

                        if (_index === 0) {

                            _index--;

                            _ul.children("li:last").css({

                                "position": "relative",

                                "left": -_len * _width

                            });

                            _ul.stop(true).animate({

                                "marginLeft": -_index * _width

                            }, {
                                duration: o.duration,

                                complete: function() {

                                    _ul.children("li:last").attr("style", "width:" + _width + "px");

                                    _ul.css("marginLeft", -_width * (_len - 1));
                                }

                            });

                            $this.find("." + ele.num_li).eq(_len - 1).addClass(active).siblings().removeClass(active);

                            _index = _len - 1;

                        } else {

                            _index--;

                            _ul.stop(true).animate({

                                "marginLeft": -_width * _index

                            }, o.duration);

                            $this.find("." + ele.num_li).eq(_index).addClass(active).siblings().removeClass(active);
                        }

                    } else {

                        if (_index === (_len - 1)) {

                            _index++;

                            _ul.children("li:first").css({

                                "position": "relative",

                                "left": _index * _width

                            });

                            _ul.stop(true).animate({
                                "marginLeft": -_width * _index
                            }, {
                                duration: o.duration,
                                complete: function() {

                                    _ul.children("li:first").attr("style", "width:" + _width + "px");

                                    _ul.css("marginLeft", 0);
                                }

                            });

                            $this.find("." + ele.num_li).eq(0).addClass(active).siblings().removeClass(active);

                            _index = 0;

                        } else {

                            _index++;

                            _ul.stop(true).animate({

                                "marginLeft": -_width * _index

                            }, o.duration);

                            $this.find("." + ele.num_li).eq(_index).addClass(active).siblings().removeClass(active);
                        }

                    }

                } else if (o.type === "up") {

                    if (_len > 2) {

                        if (arguments.length) {

                            if (_index === 0) {

                                _index--;

                                _ul.children("li:last").css({

                                    "position": "relative",

                                    "top": -_len * _height

                                });

                                _ul.stop(true).animate({

                                    "marginTop": -_index * _height

                                }, {
                                    duration: o.duration,

                                    complete: function() {

                                        _ul.children("li:last").attr("style", "width:" + _width + "px");

                                        _ul.css("marginTop", -_height * (_len - 1));
                                    }

                                });

                                $this.find("." + ele.num_li).eq(_len - 1).addClass(active).siblings().removeClass(active);

                                _index = _len - 1;

                            } else {

                                _index--;

                                _ul.stop(true).animate({

                                    "marginTop": -_height * _index

                                }, o.duration);

                                $this.find("." + ele.num_li).eq(_index).addClass(active).siblings().removeClass(active);
                            }

                        } else {

                            if (_index === (_len - 2)) {

                                _index++;

                                _ul.children("li:first").css({

                                    "position": "relative",

                                    "top": _len * _height

                                });

                                _ul.children("li").eq(1).css({

                                    "position": "relative",

                                    "top": (_len) * _height

                                });

                                _ul.stop(true).animate({
                                    "marginTop": -_height * _index
                                }, o.duration);

                                $this.find("." + ele.num_li).eq(0).addClass(active).siblings().removeClass(active);

                            } else if (_index === (_len - 1)) {

                                _index++;

                                _ul.stop(true).animate({
                                    "marginTop": -_height * _len
                                }, {
                                    duration: o.duration,
                                    complete: function() {

                                        _ul.children("li:first").attr("style", "width:" + _width + "px");

                                        _ul.children("li").eq(1).attr("style", "width:" + _width + "px");

                                        _ul.css("marginTop", 0);
                                    }

                                });

                                $this.find("." + ele.num_li).eq(0).addClass(active).siblings().removeClass(active);

                                _index = 0;

                            } else {

                                _index++;

                                _ul.stop(true).animate({

                                    "marginTop": -_height * _index

                                }, o.duration);

                                $this.find("." + ele.num_li).eq(_index).addClass(active).siblings().removeClass(active);
                            }

                        }

                    }

                }

            }

            //function - foreach slideCtrl
            function showCtrl() {

                var c_name = null;

                for (var i = 0, j = _len; i < j; i++) {

                    c_name = ele.num_li + (i === 0 ? " " + active + "" : "");

                    str += "<li class='" + c_name + "'>" + (o.has_num ? (i + 1) : "") + "</li>";

                }

                return str;
            }

            //is or not flip
            function showFlip() {

                $this.append('<div class="s-pg '+ele.page_box+'"><a href="javascript:;" class="s-prev ' + ele.prev_btn + '"><</a><a href="javascript:;" class="s-next ' + ele.next_btn + '">></a></div>');

                if(o.hide_page_btn){

                    $("."+ele.page_box).hide();

                }

                $this.on("click", "." + ele.prev_btn, throttle(function() {

                    clearInterval(time);

                    showEffect("prev");

                }, obj.throttle_t));

  
                $this.on("click", "." + ele.next_btn, throttle(function() {

                    clearInterval(time);

                    showEffect();

                }, obj.throttle_t));

            }

            //event 节流
            function throttle(fn, interval) {
                var doing = false;

                return function() {
                    if (doing) {
                        return;
                    }
                    doing = true;
                    fn.apply(this, arguments);
                    setTimeout(function() {
                        doing = false;
                    }, interval);
                };
            }

        });
    };

    //this defaults
    $.fn.fn_slide.defaults = {
        //滚动间隔时间
        time: 3000,
        //过渡间隔
        duration: 300,
        //方向
        direction: 'left',
        //是否有数字
        has_num: true,
        //动画的效果
        type: "fadeIn",  //move
        //是否有分页
        flip: true,
        //是否自动播放
        auto:false,
        //是否有数字控制
        has_ctrl:true,
        //是否需要隐藏左右点击按钮
        hide_page_btn:false
    };

})(jQuery);