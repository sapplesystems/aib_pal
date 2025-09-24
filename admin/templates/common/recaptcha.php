<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" async defer></script>

<script>
    var onloadCallback = function() {
        grecaptcha.execute();
    };

    function setResponse(response) { 
        //document.getElementById('captcha-response').value = response; 
		$('.captcha-response').val(response);
    }
</script>