$(document).ready(function(){
    $(".chosen").chosen();
    
    $(".file-uploader").each(function(i,el){
        var $parentDiv=$(el).parent();
        new qq.FileUploader({
            element: el,
            action: site_url+'uploader/datos',
            onComplete: function(id,filename,respuesta){
                $parentDiv.find("input[type=hidden]").val(respuesta.file_name);
                $parentDiv.find("a").text(respuesta.file_name).attr("href",site_url+"uploader/datos_get/"+respuesta.id);
                //$parentDiv.append();
            }
        }); 
    });
    
    $(document).on("submit",".ajaxForm",function(){
        var form=this;
        if(!form.submitting){
            form.submitting=true;
            $.ajax({
                url: form.action,
                data: $(form).serialize(),
                type: form.method,
                dataType: "json",
                success: function(response){
                    if(response.validacion){
                        window.location=response.redirect;
                    }
                    else{
                        $(".validacion").html(response.errores);
                        $('html, body').animate({
                            scrollTop: $(".validacion").offset().top-10
                        });
                    }
                },
                complete: function(){
                    form.submitting=false;
                }
            });
        }
        return false;
    });
    
});