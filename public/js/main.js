$( document ).ready(function() {
    $( "div.modal-content" ).fadeIn( "slow" );
    $( "input#uploadFile" ).change(function() {
        $("div.errormsg").hide();
        var length = 13;
        var allowedFormats = ["image/jpeg", "image/png", "image/gif"];
        try {
            $("span#uploadFileName").text("Filename here");
            var file = ( $( "input#uploadFile" ) )[0].files[0];
            if(jQuery.inArray( file.type , allowedFormats ) < 0){
                throw new Error("Only following formats allowed: " + allowedFormats.join(" , "));
            }else if(file.size / 1024 / 1024 > 10){
                throw new Error("File max. size is 10 MB");
            }
            var f_name = file.name;
            if(f_name.length > length){
                f_name = f_name.substring(0, length - 3) + "..."
            }
            $("span#uploadFileName").text( f_name );
            $("a#uploadButton").attr("disabled", false);
        } catch (e) {
            $("input#uploadFile").attr("disabled", true);
            $("div.errormsg span.text").text(e);
            $("div.errormsg").toggle();
        }
        console.log(file);
    });
});
$("div.errormsg button.delete").click(function() {
    $("div.errormsg").toggle("slow");
});
$("a#uploadButton").click(function() {
    $("input#uploadFile").attr( "disabled", false );
    $("a#uploadButton").addClass("is-loading");
    $("form#formUpload").submit();
});
$("a#copy").click(function() {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($("input#urllink").val()).select();
    document.execCommand("copy");
    $temp.remove();
    alert("URL copied");
});





