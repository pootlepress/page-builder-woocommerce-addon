(function ($) {

    $(document).ready(function () {
        $('#lazy-reg-form #lazy-reg-submit').click(function () {

            if ($('#lazy-reg-username').val() == '' ||
                $('#lazy-reg-email').val() == '' ||
                $('#lazy-reg-password').val() == ''
                ) {
                alert('Please enter required fields.');
                return false;
            }
        });
    });

})(jQuery);
