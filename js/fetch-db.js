$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("p span").text();
        $("[data-showHidden] [hidden]").balloon({ position: "null", content: x });
        //alert(x);mouseover
   });
});