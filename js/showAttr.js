$().ready(function(){
    $("#ent").change(function(){
        var idEnt = $("#ent").val();
        $.ajax({
 
    // The URL for the request
    url: "<?php bloginfo('url'); ?>/wp-admin/test.php",
 
    // The data to send (will be converted to a query string)
    data: {
        id: idEnt
    },
 
    // Whether this is a POST or GET request
    type: "GET",
 
    // The type of data we expect back
    dataType : "json",
    
    success: function(data) {
        alert("Sucesso");
    },
    error: function(xhr, desc, err) {
        alert(xhr + " details " + desc + " error: " + err);
    }

});
        //alert("O id e : " + id);
    });
});
