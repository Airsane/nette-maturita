$('document').ready(function () {

    $("#show_hide_password a").on('click', function (event) {
        event.preventDefault();
        if ($('#show_hide_password input').attr("type") == "text") {
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass("fa-eye-slash");
            $('#show_hide_password i').removeClass("fa-eye");
        } else if ($('#show_hide_password input').attr("type") == "password") {
            $('#show_hide_password input').attr('type', 'text');
            $('#show_hide_password i').removeClass("fa-eye-slash");
            $('#show_hide_password i').addClass("fa-eye");
        }
    });

    $('input[name=username]').on('blur', function () {
        let username = $(this).val();
        console.log(username);
        if (username == '') {
            return;
        }
        $.ajax({
            url: 'request.php',
            type: 'post',
            data: {
                'type': "username_check",
                'name': username,
            },
            success: function (response) {
                console.log(response);
                if (response == 'taken') {
                    $('input[name=username]').removeClass('is-valid');
                    $('input[name=username]').addClass("is-invalid");
                    $('input[name=username]').siblings("span.invalid-feedback").text('Uživatelské jméno je zabrané!');
                } else if (response == 'not_taken') {
                    $('input[name=username]').removeClass('is-invalid');
                    $('input[name=username]').addClass("is-valid");
                    $('input[name=username]').siblings("span.valid-feedback").text('Uživatelské jméno je dostupné!');
                }
            }
        });
    })

    $('input[name=email]').on('blur', function () {
        let val = $(this).val();
        if (val == '') {
            return;
        }
        $.ajax({
            url: 'request.php',
            type: 'post',
            data: {
                'type': "email_check",
                'email': val,
            },
            success: function (response) {
                console.log(response);
                if (response == 'taken') {
                    $('input[name=email]').removeClass('is-valid');
                    $('input[name=email]').addClass("is-invalid");
                    $('input[name=email]').siblings("span.invalid-feedback").text('Email je zabraný!');
                } else if (response == 'not_taken') {
                    $('input[name=email]').removeClass('is-invalid');
                    $('input[name=email]').addClass("is-valid");
                    $('input[name=email]').siblings("span.valid-feedback").text('Email je dostupný!');
                }
            }
        });
    })
})