$().ready(function(){
    $("#ent").change(function(e){
        e.preventDefault(); 
        var url = $("#ent").val();
        $.ajax({
 
        // The URL for the request
        url: url
}).done(function( html ) {
        var x = $(html).find("#results").text();
            //alert(x);
            $("#showAttr").text(x).fadeIn("slow");
            });
        });


});
