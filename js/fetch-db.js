$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("p span").html();
        $(this).balloon({ position: "null",  contents: x});
        //alert(x);mouseover
   });
});