$(document).ready(function(){
    $(".chosen").chosen();

    $(".preventDoubleRequest").one("click", function() {
        $(this).click(function () { return false; });
    });
    
    $(".datepicker:not([readonly])")
    .datepicker({
        format: "dd-mm-yyyy",
        weekStart: 1,
        autoclose: true,
        language: "es"
    });
    
    $("input[type=checkbox][readonly],input[type=radio][readonly]").click(function(e){e.preventDefault();});
    
    $("select[readonly]").focus(function(){$(this).blur();});
    
    $(".file-uploader").each(function(i,el){
        var $parentDiv=$(el).parent();
        new qq.FileUploader({
            element: el,
            action: $(el).data("action"),
            onComplete: function(id,filename,respuesta){
                if(!respuesta.error){
                    $parentDiv.find("input[type=hidden]").val(respuesta.file_name);
                    $parentDiv.find(".qq-upload-list").empty();
                    $parentDiv.find(".link").html("<a target='blank' href='"+site_url+"uploader/datos_get/"+respuesta.file_name+"?id="+respuesta.id+"&token="+respuesta.llave+"'>"+respuesta.file_name+"</a> (<a href='#' class='remove'>X</a>)")
                }
            }
        }); 
    });
    $(".file-uploader").parent().on("click","a.remove",function(){
        var $parentDiv=$(this).closest("div");
        $parentDiv.find("input[type=hidden]").val("");
        $parentDiv.find(".link").empty();
        $parentDiv.find(".qq-upload-list").empty();
    });
    

    $(".ajaxForm :submit").attr("disabled",false);
    $(document).on("submit",".ajaxForm",function(){
        var form=this;
        if(!form.submitting){
            form.submitting=true;
            $(form).find(":submit").attr("disabled",true);
            $(form).append("<div class='ajaxLoader'>Cargando</div>");
            var ajaxLoader=$(form).find(".ajaxLoader");
            $(ajaxLoader).css({
                left: ($(form).width()/2 - $(ajaxLoader).width()/2)+"px", 
                top: ($(form).height()/2 - $(ajaxLoader).height()/2)+"px"
                });
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
                        form.submitting=false;
                        $(ajaxLoader).remove();
                        $(form).find(":submit").attr("disabled",false);
                        
                        $(".validacion").html(response.errores);
                        $('html, body').animate({
                            scrollTop: $(".validacion").offset().top-10
                        });
                    }
                },
                error: function(){
                    form.submitting=false;
                    $(ajaxLoader).remove();                
                    $(form).find(":submit").attr("disabled",false);
                }
            });
        }
        return false;
    });
    
    //Para manejar los input dependientes en dynaforms
    function prepareDynaForm(form){
        $(form).find(".campo[data-dependiente-campo]").each(function(i,el){   
            var tipo=$(el).data("dependiente-tipo");
            var relacion=$(el).data("dependiente-relacion");
            var campo=$(el).data("dependiente-campo");
            var valor=$(el).data("dependiente-valor");
            
            var items=$(form).find(":input:not(:hidden)").serializeArray();
            
            var existe=false;
            for(var i in items){
                if(items[i].name==campo){
                    if(tipo=="regex"){
                        var regex=new RegExp(valor);
                        if(regex.test(items[i].value))
                            existe=true;  
                    }else{
                        if(items[i].value==valor)
                            existe=true;                       
                    }
                    if(relacion=="!=")
                        existe=!existe;
                }     
            }
            if(existe){
                if($(form).hasClass("debugForm"))
                    $(el).css("opacity","1.0");
                else
                    $(el).show();
                
                if(!$(el).data("readonly"))
                    $(el).find(":input").prop("disabled",false);
                
            }
            else{
                if($(form).hasClass("debugForm"))
                    $(el).css("opacity","0.5");
                else
                    $(el).hide();
                
                $(el).find(":input").prop("disabled",true);
            }
        });
    }
    prepareDynaForm(".dynaForm");
    $(".dynaForm").on("change",":input",function(event){
        prepareDynaForm($(event.target).closest(".dynaForm"))
    });
    
    
    
});