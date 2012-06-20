$(document).ready(function(){
    $(".chosen").chosen();
    
    $(".datepicker")
        .datepicker({
            format: "dd/mm/yyyy",
            weekStart: 1,
            autoclose: true,
            language: "es"
        })
        .on("changeDate",function(event){
            var fecha=event.date.getFullYear()+"-"+(event.date.getMonth()+1)+"-"+event.date.getDate();
            $(this).next("input:hidden").val(fecha);
        });
    
    $(".file-uploader").each(function(i,el){
        var $parentDiv=$(el).parent();
        new qq.FileUploader({
            element: el,
            action: site_url+'uploader/datos',
            onComplete: function(id,filename,respuesta){
                $parentDiv.find("input[type=hidden]").val(respuesta.file_name);
                $parentDiv.find("a").text(respuesta.file_name).attr("href",site_url+"uploader/datos_get/"+respuesta.filename);
                //$parentDiv.append();
            }
        }); 
    });
    
    $(document).on("submit",".ajaxForm",function(){
        var form=this;
        if(!form.submitting){
            form.submitting=true;
            $(form).append("<div class='ajaxLoader'>Cargando</div>");
            var ajaxLoader=$(form).find(".ajaxLoader");
            $(ajaxLoader).css({left: ($(form).width()/2 - $(ajaxLoader).width()/2)+"px", top: ($(form).height()/2 - $(ajaxLoader).height()/2)+"px"});
            $.ajax({
                url: form.action,
                data: $(form).serialize(),
                type: form.method,
                dataType: "json",
                success: function(response){
                    if(response.validacion){
                        if(response.redirect)
                            window.location=response.redirect;
                        var f=window[$(form).data("onsuccess")];
                        f(form);
                    }
                    else{
                        $(".validacion").html(response.errores);
                        $('html, body').animate({
                            scrollTop: $(".validacion").offset().top-10
                        });
                    }
                },
                complete: function(){
                    $(ajaxLoader).remove();
                    form.submitting=false;
                }
            });
        }
        return false;
    });
    
});