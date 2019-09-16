var city = new Object;
city.getcity = function(pname,cname,oname,vprovince,vcity,vcounty,def,string){
	city.pname = pname;
	city.cname = cname;
	city.oname = oname;	
	city.vprovince = vprovince;
	city.vcity = vcity;
	city.vcounty = vcounty;	
	city.def = def;	
	city.string = string;
    city.setProvince();
    $("#"+city.pname).change(function(){city.setCity()});
    $("#"+city.cname).change(function(){city.setCounty()});  	
}
city.setProvince = function(){
	if(city.def==1) $("#"+city.pname).append("<option value='' aid='-100' selected>选择省份</option>");	
	if(city.string!=null){
	  if(city.string==city.vprovince){
	    $("#"+city.pname).append("<option value='"+city.string+"' aid='-10000' selected>"+city.string+"</option>");	
	  }else{
        $("#"+city.pname).append("<option value='"+city.string+"' aid='-10000'>"+city.string+"</option>");	 
	  }
	} 
    $.each(dsy['parent_id_0'], function(i, item) {
	 if(item['area_name']==city.vprovince){
	  $("#"+city.pname).append("<option value='"+item['area_name']+"' aid='"+item['area_id']+"' selected>"+item['area_name']+"</option>");
	 }else{
      $("#"+city.pname).append("<option value='"+item['area_name']+"' aid='"+item['area_id']+"'>"+item['area_name']+"</option>"); 
	 }
	});
    city.setCity();
}
city.setCity = function(){
   $("#"+city.cname).empty();
   if(city.def==1) $("#"+city.cname).append("<option value='' aid='-100' selected>选择城市</option>");
   if($("#"+city.pname).find("option:selected").attr('aid')>0){
     $.each(dsy['parent_id_'+$("#"+city.pname).find("option:selected").attr('aid')], function(i, item) {
	   if(item['area_name']==city.vcity){
        $("#"+city.cname).append("<option value='"+item['area_name']+"' aid='"+item['area_id']+"' selected>"+item['area_name']+"</option>"); 
	   }else{
        $("#"+city.cname).append("<option value='"+item['area_name']+"' aid='"+item['area_id']+"'>"+item['area_name']+"</option>"); 
	   }		 		
     });
   }
   city.setCounty();
}
city.setCounty = function(){
   $("#"+city.oname).empty();
   if(city.def==1) $("#"+city.oname).append("<option value='' aid='-100' selected>选择区县</option>");	
   if($("#"+city.cname).find("option:selected").attr('aid')>0){
     $.each(dsy['parent_id_'+$("#"+city.cname).find("option:selected").attr('aid')], function(i, item) {
	   if(item['area_name']==city.vcounty){
        $("#"+city.oname).append("<option value='"+item['area_name']+"' aid='"+item['area_id']+"' selected>"+item['area_name']+"</option>"); 
	   }else{
        $("#"+city.oname).append("<option value='"+item['area_name']+"' aid='"+item['area_id']+"'>"+item['area_name']+"</option>"); 
	   }	     	  
     });
   }
}