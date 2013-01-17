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
                if(!respuesta.error){
                    $parentDiv.find("input[type=hidden]").val(respuesta.file_name);
                    $parentDiv.find(".qq-upload-list").empty();
                    $parentDiv.find(".link").html("<a href='"+site_url+"uploader/datos_get?filename="+respuesta.file_name+"'>"+respuesta.file_name+"</a> (<a href='#' class='remove'>X</a>)")
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
    
    
    $(document).on("submit",".ajaxForm",function(){
        var form=this;
        if(!form.submitting){
            form.submitting=true;
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
        $(form).find(".campo[data-dependiente-campo]").each(function(i,el){          
            var tipo=$(el).data("dependiente-tipo");
            var campo=$(el).data("dependiente-campo");
            var valor=$(el).data("dependiente-valor");
            
            //Obtenemos el arreglo de inputs del sistema. Hacemos un hack para incluir los disabled elements ya que estos nos sirven
            //para obtener los campos que son de solo visualizacion y armar el formulario acorde a ellos.
            var disabledElements=$(form).find(":input:disabled");
            $(disabledElements).prop("disabled",false);
            var items=$(form).find(":input:not(:hidden)").serializeArray();
            $(disabledElements).prop("disabled",true);
            
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
                }     
            }
            if(existe){
                if($(form).hasClass("debugForm"))
                    $(el).css("opacity","1.0");
                else
                    $(el).show();
                //$(el).find(":input").prop("disabled",false);
            }
            else{
                if($(form).hasClass("debugForm"))
                    $(el).css("opacity","0.5");
                else
                    $(el).hide();
                //$(el).find(":input").prop("disabled",true);
            }
        });
    }
    prepareDynaForm(".dynaForm");
    $(".dynaForm").on("change",":input",function(event){
        prepareDynaForm($(event.target).closest(".dynaForm"))
    });
    
    
    
});