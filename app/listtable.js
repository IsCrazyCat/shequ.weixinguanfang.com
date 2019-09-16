/* $Id: listtable.js 14980 2008-10-22 05:01:19Z testyang $ */
if (typeof Utils != 'object')
{
  alert('Utils object doesn\'t exists.');
}
var _database = new Object;
var _databasetime;
var listTable = new Object;

listTable.query = "query";
listTable.filter = new Object;
listTable.url = location.href;
listTable.url += location.href.lastIndexOf("?") == -1 ? "?re=ajax" : "&re=ajax";
/**
 * 创建一个可编辑区
 */
listTable.edit = function(obj, act, id)
{
  var tag = obj.firstChild.tagName;

  if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
  {
    return;
  }
  if(obj.innerHTML.indexOf('loading') != -1){
	return;  
  }
  

  /* 保存原始的内容 */
  var org = obj.innerHTML;
  var val = Browser.isIE ? obj.innerText : obj.textContent;

  /* 创建一个输入框 */
  var txt = document.createElement("INPUT");
  txt.value = (val == 'N/A') ? '' : val;
  txt.style.width = (obj.offsetWidth + 12) + "px" ;

  /* 隐藏对象中的内容，并将输入框加入到对象中 */
  obj.innerHTML = "";
  obj.appendChild(txt);
  txt.focus();

  /* 编辑区输入事件处理函数 */
  txt.onkeypress = function(e){
    var evt = Utils.fixEvent(e);
    var obj = Utils.srcElement(e);

    if (evt.keyCode == 13){
      obj.blur();
      return false;
    }
    if (evt.keyCode == 27){
      obj.parentNode.innerHTML = org;
    }
  }
  /* 编辑区失去焦点的处理函数 */
  txt.onblur = function(e){
    if (Utils.trim(txt.value).length > 0){
	  obj.innerHTML = '<img src="'+tempdir+'images/loading.gif" />';
      $.ajax({url:listTable.url+'&do='+act+'&name='+encodeURIComponent(txt.value)+'&id='+id+'&floor='+getRandom(5),
		type:'GET',
		success:function(res){
	      if(res.error==0){
		    obj.innerHTML =  res.name;
	      }else{
		    alert(res.error);
            obj.innerHTML = org;
	      }
		},
		dataType:"json"
	  });
    }else{
      obj.innerHTML = org;
    }
  }
  
}

listTable.upgroup = function(groupid){
  showhandle({
    html:'<table class="lotab"><tr><th><label>支付密码：</label></th><td><input id="repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'upgroup',
    title:'会员升级'
  },function(){
	$("#controlLoad").show();
	removetip("upgroup");
    $.getJSON(get_path_url("?mod=member&act=vocational&type=upgroup&re=ajax&repass="+$("#repass").val()+"&groupid="+groupid),function(res){
	  $("#controlLoad").hide();																		 
	  if(res.error=='0'){
		hidebox('upgroup',true);
		Right('会员升级成功',{},function(){
          location.href = res.url;				
		});
	  }else{
		addtip("upgroup",res.error);
	  }							  
    });
  });
}

listTable.register = function(){
  showhandle({
    html:'<table class="lotab"><tr><th><label>支付密码：</label></th><td><input id="repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'register',
    title:'注册会员'
  },function(){
	$("#controlLoad").show();
	removetip("register");
	var queryString = $('#ajaxformbox').formSerialize();
    $.ajax({url:get_path_url("?mod=member&act=vocational&type=register&repass="+$("#repass").val()),
	  type:'POST',
      dataType:"json",
	  data:queryString,
      success:function(res){
		$("#controlLoad").hide();
	    if(res.error=='0'){
		  hidebox('register',true);
		  Right('注册会员成功',{},function(){
            location.href = res.url;				
		  });
	    }else{
		  addtip("register",res.error);
	    }	
      }
	});
  });
}
listTable.customs = function(name,address){
  var par = "&name="+encodeURIComponent(name)+"&address="+encodeURIComponent(address);
  showhandle({
    html:'<table class="lotab"><tr><th><label>支付密码：</label></th><td><input id="repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:140,
    id:'customs',
    title:'申请报单中心'
  },function(){
	$("#controlLoad").show();
	removetip("customs");
    $.getJSON(get_path_url("?mod=member&act=vocational&type=customs&re=ajax"+par+"&repass="+$("#repass").val()),function(res){
	  $("#controlLoad").hide();																		 
	  if(res.error=='0'){
		hidebox('customs',true);
		Right('报单中心申请成功',{},function(){
          location.href = res.url;				
		});
	  }else{
		addtip("customs",res.error);
	  }							  
    });
  });
}

listTable.inmember = function(){
  showhandle({
    html:'<table class="lotab"><tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'register',
    title:'注册会员'
  },function(){
	$("#controlLoad").show();
	removetip("register");
	var queryString = $('#ajaxformbox').formSerialize();
    $.ajax({url:get_path_url("?mod=admin&act=user&get=control&re=add&repass="+$("#_repass").val()),
	  type:'POST',
      dataType:"json",
	  data:queryString,
      success:function(res){
		$("#controlLoad").hide();
	    if(res.error=='0'){
		  hidebox('register',true);
		  Right('注册会员成功',{},function(){
            location.href = res.url;				
		  });
	    }else{
		  addtip("register",res.error);
	    }	
      }
	});
  });
}

listTable.addmember = function(){
  showhandle({
    html:'<table class="lotab"><tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'register',
    title:'注册会员'
  },function(){
	$("#controlLoad").show();
	removetip("register");
	var queryString = $('#ajaxformbox').formSerialize();
    $.ajax({url:get_path_url("?mod=admin&act=user&get=control&re=add&repass="+$("#_repass").val()),
	  type:'POST',
      dataType:"json",
	  data:queryString,
      success:function(res){
		$("#controlLoad").hide();
	    if(res.error=='0'){
		  hidebox('register',true);
		  Right('注册会员成功',{},function(){
            location.href = res.url;				
		  });
	    }else{
		  addtip("register",res.error);
	    }	
      }
	});
  });
}


listTable.status = function(obj,uid,admin){
  var title = admin==null ? '确定要开通会员，将扣除你'+obj.attr('buymoney') :  '确定要开通会员，将结算奖金';
  var mess = admin==null ? '支付密码' : '管理密码';
  showhandle({
    html:'<table class="lotab"><tr style="display:none"><th><label>安排上线：<br>(改变关系)&nbsp;&nbsp;</label></th><td><input name="myreferee" type="text" id="myreferee" value="0" class="myinput repass2" placeholder="用户名" /></td></tr><tr style="display:none"><th><label>市>市由上线↑省代给奖励</label></th><td><label><input name="sfshengdaizjl" type="radio" id="sfshengdaizjl_0" value="0" checked>否(平台给)</label><label><input type="radio" name="sfshengdaizjl" value="1" id="sfshengdaizjl_1">是(↑省代给)</label></td></tr><tr><th><label>'+mess+'：</label></th><td><input id="repass" class="myinput repass" type="password"></td></tr></table>',
    //html:'<table class="lotab"><tr><th><label>安排上线：<br>(改变关系)&nbsp;&nbsp;</label></th><td><input name="myreferee" type="text" id="myreferee" value="0" class="myinput repass2" placeholder="用户名" /></td></tr><tr><th><label>市>市由上线↑省代给奖励</label></th><td><label><input name="sfshengdaizjl" type="radio" id="sfshengdaizjl_0" value="0" checked>否(平台给)</label><label><input type="radio" name="sfshengdaizjl" value="1" id="sfshengdaizjl_1">是(↑省代给)</label></td></tr><tr><th><label>'+mess+'：</label></th><td><input id="repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:320,
    id:'toggle',
    title:title
  },function(){
	$("#controlLoad").show();
	removetip("toggle");
	
    if(admin==null){
	   var url = get_path_url("?mod=member&act=vocational&type=ajax&repass="+$("#repass").val()+"&myreferee="+$("#myreferee").val()+"&sfshengdaizjl="+$("#sfshengdaizjl").val()+"&uid="+uid);
    }else{
	   var url = get_path_url("?mod=admin&act=user&get=control&re=ajax&do=status&repass="+$("#repass").val()+"&myreferee="+$("#myreferee").val()+"&sfshengdaizjl="+$("#sfshengdaizjl").val()+"&uid="+uid);
    }
    $.getJSON(url,function(res){
	  $("#controlLoad").hide();																		 
	  if(res.error=='0'){
		hidebox('toggle',true);
		if(admin==null){
		  obj.html('已开通');
		  document.location.reload();
		}else{
		  obj.html('<p><em>开通时间：</em></p><p style="padding-left:5px;"><em></em>'+res.opentime+'</p>');
		  document.location.reload();
		}
	  }else{
		addtip("toggle",res.error);
	  }							  
    });
  });
}
listTable.change = function(obj, act, id, typename)
{
  var tag = obj.firstChild.tagName;
  var url = siteurl+'index.php?mod=tools&act=getType&typename='+typename;
  
  if (typeof(tag) != "undefined" && tag.toLowerCase() == "select")
  {
    return;
  }

  /* 保存原始的内容 */
  var org = obj.innerHTML;
  var val = Browser.isIE ? obj.innerText : obj.textContent;

  /* 创建一个输入框 */
  var txt = document.createElement("select");
  txt.id = 'typeid';
  txt.style.width = (obj.offsetWidth + 32) + "px" ;
  obj.innerHTML = "";
  obj.appendChild(txt);  
  $.getJSON(url,function(data){
    $.each(data, function(i, item) {
	 if(item['typename']==org){
	  $("#typeid").append("<option value='"+item['id']+"' selected>"+item['typename']+"</option>");
	 }else{
      $("#typeid").append("<option value='"+item['id']+"'>"+item['typename']+"</option>"); 
	 }
	});
  });
  txt.focus();
  
  /* 编辑区输入事件处理函数 */
  txt.onkeypress = function(e){
    var evt = Utils.fixEvent(e);
    var obj = Utils.srcElement(e);

    if (evt.keyCode == 13){
      obj.blur();
      return false;
    }
    if (evt.keyCode == 27){
      obj.parentNode.innerHTML = org;
    }
  }

  /* 编辑区失去焦点的处理函数 */
  txt.onblur = function(e){
    if (Utils.trim($('#typeid option:selected').text()).length > 0){
		
	  showAsk({Msg:"输入管理密码再次确认：<input value='' style='padding-left:0;' id='adminpassword' name='adminpassword' type='password'>"},function(){
        $.ajax({url:get_path_url("?mod=admin&act=adminSend&password="+$("#adminpassword").val()),
          type:'GET',
          dataType:"json",
          success:function(res){
	        if(res.error==1){
	  var res = ajax(listTable.url+'&do='+act+'&typeid='+$('#typeid').val()+'&id='+id);
	  if(res.error==0){
		obj.innerHTML = $('#typeid option:selected').text();
	  }else{
		alert(res.error);
        obj.innerHTML = org;
	  }
			 Close();
	        }else{ 
	          alert('您输入的管理密码有误');
	        }	 
          }
        });																														 
      },function(){
		obj.innerHTML = org;
		Close();  
	  });
    }else{
      obj.innerHTML = org;
    }
  }
  
}

/**
 * 切换状态
 */
listTable.toggle = function(obj, act, id){
  var val = (obj.src.match(/yes.gif/i)) ? 0 : 1;
  showhandle({
    html:'<table class="lotab"><tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'toggle',
    title:'切换状态'
  },function(){
	$("#controlLoad").show();
	removetip("toggle");
    $.getJSON(listTable.url+'&do='+act+'&val='+val+'&id='+id+"&repass="+$("#_repass").val(),function(res){
	  $("#controlLoad").hide();																		 
	  if(res.error=='0'){
		hidebox('toggle',true);
		obj.src = val ? obj.src.replace('no.gif',"")+'yes.gif' : obj.src.replace('yes.gif',"")+'no.gif';
	  }else{
		addtip("toggle",res.error);
	  }							  
    });
  });
}


listTable._toggle = function(obj,act,id,title){
  var value = $("#"+obj).attr('value');
  var val = value=='1' ? "0" : '1';
  showhandle({
    html:'<table class="lotab"><tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'toggle',
    title:title
  },function(){
	$("#controlLoad").show();
	removetip("toggle");
    $.getJSON(listTable.url+'&do='+act+'&val='+val+'&id='+id+"&repass="+$("#_repass").val(),function(res){
	  $("#controlLoad").hide();																		 
	  if(res.error=='0'){
		hidebox('toggle',true);
		$("#"+obj).html(res.message).attr("value",val);
	  }else{
		addtip("toggle",res.error);
	  }							  
    });
  });
}

/**
 * 删除列表中的一个记录
 */
listTable.remove = function(id, cfm, opt){
  if(opt == null) opt = "remove";
  if(opt == '') opt = "remove";
  if(cfm == null) cfm = "";
  showhandle({
    html:'<table class="lotab"><tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'_remove',
    title:cfm
  },function(){
	$("#controlLoad").show();
	removetip("_remove");
    $.getJSON(listTable.url+'&do='+opt+'&id='+id+"&repass="+$("#_repass").val(),function(res){
	  $("#controlLoad").hide();																		 
	  if(res.error=='0'){
		hidebox('_remove',true);
		$("#remove_"+id).remove();
	  }else{
		addtip("_remove",res.error);
	  }							  
    });
  });
}

listTable.myremove = function(id, cfm, opt){
  if(opt == null) opt = "remove";
  if(opt == '') opt = "remove";
  if(cfm == null) cfm = "";
  showhandle({
    html:'<table class="lotab"><tr><th><label>支付密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:136,
    id:'_remove',
    title:cfm
  },function(){
	$("#controlLoad").show();
	removetip("_remove");
    $.getJSON(listTable.url+'&do='+opt+'&id='+id+"&repass="+$("#_repass").val(),function(res){
	  $("#controlLoad").hide();																		 
	  if(res.error=='0'){
		hidebox('_remove',true);
		$("#remove_"+id).remove();
		location.reload(); 
	  }else{
		addtip("_remove",res.error);
	  }							  
    });
  });
}

listTable.memberRemove = function(id, cfm, opt){
  if(opt == null) opt = "remove";
  if(opt == '') opt = "remove";
  if(cfm == null) cfm = "";
  showAsk({Msg:cfm},function(){
    $.ajax({url:listTable.url+'&do='+opt+'&id='+id,
      type:'GET',
      dataType:"json",
      success:function(res){ 
	    if(res.error=='0'){
		  Close();
		  $("#remove_"+id).remove();
	    }else{
		  Wrong(res.error);
	    }	
      }
    });																														 
  },function(){
	Close();  
  });
}
listTable.selectAll = function(obj, chk)
{
  if (chk == null) chk = 'checkboxes';
  var elems = obj.form.getElementsByTagName("input");

  for (var i=0; i < elems.length; i++)
  {
    if (elems[i].name == chk || elems[i].name == chk + "[]")
    {
      elems[i].checked = obj.checked;
    }
  }
}
listTable.memberfrom = function(title,obj,url){
  showhandle({
    html:'<table class="lotab"><tr><th><label>支付密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:138,
    id:'ajaxform',
    title:title
  },function(){
	$("#controlLoad").show();
	removetip("ajaxform");
	var queryString = $('#'+obj).formSerialize();
    $.ajax({url:location.href+url+"&repass="+$("#_repass").val(),
	  type:'POST',
      dataType:"json",
	  data:queryString,
      success:function(res){
		$("#controlLoad").hide();
	    if(res.error=='0'){
		  $(".ok").hide();
		  hidebox('ajaxform',true);
		  Right(res.message,{},function(){
			if(res.url){
			  location.href = res._url=='1' ? eval(res.url) : res.url;	
			}else{
			  Close();  
			}
		  });  
	    }else{
		  addtip("ajaxform",res.error);
	    }
      }
	});
  });
}


listTable.ajaxform = function(title,obj,url){
  url = url==null ? "" : url;
  showhandle({
    html:'<table class="lotab"><tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:128,
    id:'ajaxform',
    title:title
  },function(){
	$("#controlLoad").show();
	removetip("ajaxform");
	var queryString = $('#'+obj).formSerialize();
    $.ajax({url:location.href+url+"&repass="+$("#_repass").val(),
	  type:'POST',
      dataType:"json",
	  data:queryString,
      success:function(res){
		$("#controlLoad").hide();
	    if(res.error=='0'){
		  $(".ok").hide();
		  if(res.message==null){
			if(res._message){
			  $(".lotab").html("<tr><td style='height:26px;font-size:12px;'>"+res._message+"</td></tr>");
			  $("#controlLoad").show().html("备份中，不要刷新或关闭该窗口");
			  resizebox("ajaxform",320,$(".lotab").height()+80);
			  _database = res;
			  _databasetime = window.setInterval("ajaxdatabase(_database)",1000);
			}  
		  }else{
			hidebox('ajaxform',true);
			Right(res.message,{},function(){
			  if(res.url){
				location.href = res._url=='1' ? eval(res.url) : res.url;	
			  }else{
				Close();  
			  }
			});  
		  }
	    }else{
		  addtip("ajaxform",res.error);
	    }
      }
	});
  });
}
listTable.unback = function(shell,filename){
  showhandle({
    html:'<table class="lotab"><tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput repass" type="password"></td></tr></table>',
    width:320,
    height:128,
    id:'ajaxform',
    title:'数据恢复'
  },function(){
	$("#controlLoad").show();
	removetip("ajaxform");
    $.ajax({url:get_path_url("?mod=admin&act=unbackdatabase&mypath="+filename+"&shell="+shell+"&repass="+$("#_repass").val()),
      dataType:"json",
      success:function(res){
		$("#controlLoad").hide();
	    if(res.error=='0'){
		  if(res._message){
			$(".lotab").html("<tr><td style='height:26px;font-size:12px;'>"+res._message+"</td></tr>");
			$("#controlLoad").show().html("恢复中，不要刷新或关闭该窗口");
		    resizebox("ajaxform",320,$(".lotab").height()+80);
			_database = res;
			_databasetime = window.setInterval("ajaxdatabase(_database)",1000);
		  }  
	    }else{
		  addtip("ajaxform",res.error);
	    }
      }
	});
  });
}
function ajaxdatabase(data){
  $.ajax({url:data.url,type:'GET',success:function(res){																
    _database = res;
    $(".lotab").html("<tr><td style='height:26px;font-size:12px;'>"+res._message+"</td></tr>");
	resizebox("ajaxform",320,$(".lotab").height()+80);
	if(res.url==null){
	  clearInterval(_databasetime);
	  hidebox('ajaxform',true);
	  Right(res.message,{},function(){
		location.href = res.gourl;						 
	  });
	}
  },dataType:"json"});
}

listTable.message = function(id,admin){
  var html = '',type='';
  opts = {title:'查看信件',handle:'imessage',act:'id',width:380,show:true};
  var url = admin==null ? 
    get_path_url("?mod=member&act=imessage&method=ajax&id="+id) : 
	get_path_url("?mod=admin&act=main&get=guestbook&re=ajax&do=read&id="+id);
  $.ajax({url:url,
    type:'GET',
	async:false,
	dataType:"json",
    success:function(res){
	  if(res.error=='0'){
		 if(admin==null){
		   if(res.type=='2'){
			 $("#read_"+res.id).html('已读');
			 $('#mess_'+id).attr('src',$('#mess_'+id).attr('src').replace('message_0',"message_1"));
		   }
		   if(res.read>0){
		     $("#myread").show().html('('+res.read+')');
		   }else{
		     $("#myread").hide().html('');
		   }
		 }else{
		   if($('#toggle_'+id).val().indexOf('未读')!=-1&&res.type=='1'){
			 $('#toggle_'+id).val($('#toggle_'+id).val.replace('未读',"已读"));
		   }
	     }
		 html += "<div class='lookcontent'><h1>"+res.title+"</h1><p>"+res.content+"</p>";
		 if(res.type=='1'&&admin!=null){
		    html += '<table class="lotab"><form name="queryString" id="queryString">';
			html += '<tr><th><label>回复内容：</label></th><td><textarea name="content" id="content" class="textarea"></textarea></td></tr>';
			html += '<tr><th><label>管理密码：</label></th><td><input id="_repass" class="myinput textarea" type="password"></td></tr>';
			html += '</form></table>'; 
		 }
		 html += "</div>";
		 type = res.type;
	  }else{
		 Wrong(res.error);
	  }
    }
  });
  showhandle({
    html:html,
    width:opts.width,
    height:0,
    id:opts.handle+"_box",
    title:opts.title
  },function(){
	if(admin==null||type=='2'){
	  hidebox(opts.handle+"_box",true);
	}else{
	  $("#controlLoad").show();
	  removetip(opts.handle+"_box");
	  var queryString = $('#queryString').formSerialize();
      $.ajax({url:get_path_url("?mod=admin&act=main&get=guestbook&re=ajax&do=reply&id="+id+"&repass="+$("#_repass").val()),
	    type:'POST',
        dataType:"json",
	    data:queryString,
        success:function(res){
		  $("#controlLoad").hide();
	      if(res.error=='0'){
			hidebox(opts.handle+"_box",true);
			Right("回复信件成功");  
	      }else{
		    addtip(opts.handle+"_box",res.error);
	      }
        }
	  });
	}
  });
  resizebox(opts.handle+"_box",opts.width,$('.lookcontent').height()+80);
}
