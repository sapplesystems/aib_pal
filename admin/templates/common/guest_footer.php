<?php $loginJsArray = array('jquery.min.js','bootstrap.min.js','icheck.min.js','jquery-form-validate.min.js','site.js'); ?>
<?php foreach($loginJsArray as $key=>$fileName){ ?>
    <script src="<?php echo JS_PATH.$fileName; ?>"></script>
<?php } ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23911814-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'UA-23911814-1');
</script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
    // Allow only a-z, 0-9, hyphen, period, apostrophe, comma, and spaces
jQuery.validator.addMethod("validAnswer", function(value, element) {
    return this.optional(element) || /^[a-z0-9\-.,'\s]+$/i.test(value);
}, "Only alphabets, numbers, hyphens, periods, apostrophes, commas are allowed.");
</script>
</body>
</html>
