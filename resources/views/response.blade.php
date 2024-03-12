<html>
	<head>
		<title>Email verification response</title>
        <meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link href='https://fonts.googleapis.com/css?family=Lato:300,400|Montserrat:700' rel='stylesheet' type='text/css'>
	<style>
		@import url(//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.1/normalize.min.css);
		@import url(//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css);
	</style>
	<link rel="stylesheet" href="https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/default_thank_you.css">
	<script src="https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/jquery-1.9.1.min.js"></script>
	<script src="https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/html5shiv.js"></script>
	</head>
	<body>

            @if(Session::has('flash_message_error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{!! session('flash_message_error') !!}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            @if(Session::has('flash_message_success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{!! session('flash_message_success') !!}</strong>
                <!--<button type="button" class="close" data-dismiss="alert" aria-label="Close">-->
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            <header class="site-header" id="header">
                <h1 class="site-header__title" data-lead-id="site-header-title">THANK YOU!</h1>
            </header>

            <div class="main-content">
                <i class="fa fa-check main-content__checkmark" id="checkmark"></i>
                <p class="main-content__body" data-lead-id="main-content-body">Thanks for registration your account is activated. Now you can download our latest template using our platform.</p><br><br>
                <a href="https://artaux.io/user-login" class="btn btn-success mt-5" style="padding:10px; border:1px solid #00c2a8;border-radius:3px;margin-top:150px">Login</a>
            </div>

            <footer class="site-footer" id="footer">
                <p class="site-footer__fineprint" id="fineprint">Copyright ©2021 | All Rights Reserved </p>
            </footer>
	</body>
    <script>
        $(document).ready(function () {
    // Handler for .ready() called.
    window.setTimeout(function () {
        location.href = "https://artaux.io/";
    }, 10000);
});
    </script>
</html>
