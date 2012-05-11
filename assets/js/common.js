$(document).ready(function(){
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