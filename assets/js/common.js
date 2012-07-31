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
            action: $(el).data("action"),
            onComplete: function(id,filename,respuesta){
                $parentDiv.find("input[type=hidden]").val(respuesta.file_name);
                $parentDiv.find("a").text(respuesta.file_name).attr("href",site_url+"uploader/datos_get/"+respuesta.file_name);
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
                        if(response.redirect){
                            window.location=response.redirect;
                        }else{
                            var f=window[$(form).data("onsuccess")];
                            f(form);
                        }
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
    
    //Para manejar los input dependientes en dynaforms
    function prepareDynaForm(form){
        $(form).find(".campo[data-dependiente-campo][data-dependiente-valor]").each(function(i,el){
            var disabledElements=$(form).find(":input:disabled");
            $(disabledElements).prop("disabled",false);
            var items=$(form).find(":input").serializeArray();
            $(disabledElements).prop("disabled",true);
            var existe=false;
            for(var i in items){
                if(items[i].name==$(el).data("dependiente-campo") && items[i].value==$(el).data("dependiente-valor")){
                    existe=true;
                    break;
                }
            }
            if(existe)
                $(el).show();
            else
                $(el).hide();
        });
    }
    prepareDynaForm(".dynaForm");
    $(".dynaForm").on("change",":input",function(event){
        prepareDynaForm($(event.target).closest(".dynaForm"))
    });
    
    
    
});