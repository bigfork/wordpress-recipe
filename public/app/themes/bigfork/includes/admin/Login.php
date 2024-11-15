<?php
add_action('login_enqueue_scripts', function () {
    ?>
    <style type="text/css">
        body {
            background-color: #FCFCFC !important;
        }

        #login h1 a, .login h1 a {
            /*background-image: url(*/<?php //= themedResourceURL(
            //    'dist/images/branding/logo.svg',
            //) ?>/*) !important;*/
            height:76px;
            width:235px;
            background-size: 100% 76px;
            background-repeat: no-repeat;
            padding-bottom: 0;
        }
    </style>
<?php
});

add_filter('login_headerurl', fn() => home_url());
add_filter('login_headertext', fn() => get_bloginfo('name'));
