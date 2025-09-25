$(document).ready(function(){
    $("#lofin_form").validate({
        rules: {
            user_email: {
                required: {
                    depends:function(){
                        $(this).val($.trim($(this).val()));
                        return true;
                    }
                },
                email: true
            },
            user_password: "required"
        },
        messages: {
            user_email: {
                required: "Please enter email Id",
                email: "Please enter valid email Id"
            },
            user_password: "Please enter password"
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
});
