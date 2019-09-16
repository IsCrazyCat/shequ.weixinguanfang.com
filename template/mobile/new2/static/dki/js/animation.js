// JavaScript Document
$(function(){
    $("#indexBanner .img li:not(:first)").hide();
    var j=$("#indexBanner .img li").length;
    for(i=1;i<=j;i++){
        $("#indexBanner .dot").append("<span>"+i+"</span>");
        }
	$("#indexBanner .dot span:nth-child(1)").addClass("active");
    var c=$("#indexBanner .img li").eq(0).css('background-color');
    $(".indexTop").css('background-color',c);	
    $("#indexBanner .dot span").mouseover(function(){
        $(this).addClass("active").siblings("span").removeClass("active");
        var a=$(this).index();
        var color=$("#indexBanner .img li").eq(a).css('background-color');
        $(".indexTop").css('background-color',color);
        $("#indexBanner .img li").eq(a).show().siblings("li").hide();
        })
    d=setInterval(function(){
        var b=$("#indexBanner .active").index()+1;
        c=$("#indexBanner .dot span").length;
        if(b==c){
            b=0;
            }
        $("#indexBanner .dot span").eq(b).trigger("mouseover");
        },2000)
    $("#indexBanner .img li").mouseover(function(){
        clearInterval(d);
        }).mouseout(function(){
            d=setInterval(function(){
        var b=$("#indexBanner .active").index()+1;
        c=$("#indexBanner .dot span").length;
        if(b==c){
            b=0;
            }
        $("#indexBanner .dot span").eq(b).trigger("mouseover");
        },2000)
            })
			
			
	 $(".index-video .box:not(:first)").hide();		
	 $(".index-video .tab li").click(function(){
        var a=$(this).index();
        $(".index-video .box").eq(a).show().siblings(".box").hide();
        })	
		
	$(".order-cnt .box:not(:first)").hide();		
	 $(".order-nav li").click(function(){
        var a=$(this).index();
		$(this).addClass("active").siblings("li").removeClass("active");
        $(".order-cnt .box").eq(a).show().siblings(".box").hide();
        })	
		
	$(".show .show-cnt .list:not(:first)").hide();		
	 $(".show .show-tab li").click(function(){
        var a=$(this).index();
		$(this).addClass("active").siblings("li").removeClass("active");
        $(".show .show-cnt .list").eq(a).show().siblings(".list").hide();
        })		

    //筛选
    $("#screenBut").click(function(){
        $(".filtrate").fadeIn(300);
        })
    $("#true").click(function(){
        $(".filtrate").hide();
        }) 
	$(".fltrate-close").click(function(){
        $(".filtrate").hide();
        }) 
	$(".filtrate-scroll dl dd span").click(function(){
        $(this).addClass("active").siblings("span").removeClass("active");
        }) 	
		
	//服务商
	$(".facilitator-cnt .facilitator-item:not(:first)").hide();		
	 $(".facilitator-tab li").click(function(){
        var a=$(this).index();
		$(this).addClass("active").siblings("li").removeClass("active");
        $(".facilitator-cnt .facilitator-item").eq(a).show().siblings(".facilitator-item").hide();
        })		
			   		
    })

function superOpen(){
	$(".super-popup").fadeIn();
	}

function superClose(){
	$(".super-popup").hide();
	}



function check(){
	var checkbox = document.getElementById('checkbox');
	checkbox.value==1?checkbox.value=2 : checkbox.value=1;
	var checkboxs = document.getElementsByName('box');
	for(var i=0; i<checkboxs.length;i++){
	if(checkbox.value==1){
		checkboxs[i].checked=false;//全不选
	}else{
		checkboxs[i].checked=true;//全选
	}
}}

//购物车数量代码
$(function() {  
	$(".prus").click(function() {  
			var n = $(this).prev().val(), //获取前面标签的值 
				num = parseInt(n) + 1;
			if (num == 0) {   
				alert("");
			}  
			$(this).prev().val(num);  
		});  
		$(".minis").click(function() {  
			var n = $(this).next().val(),  
				num = parseInt(n) - 1;      
			num = num < 1 ? 1 : num;  //小于1的时候，默认取值为1  
			$(this).next().val(num);  
		});  
    })









