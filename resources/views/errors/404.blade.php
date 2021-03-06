<!DOCTYPE html>
<html lang="en" >
<!-- begin::Head -->
<head>
    <meta charset="utf-8" />
    <title>
        Parcelbd | 404 - Not Found
    </title>
    <meta name="description" content="Latest updates and statistic charts">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!--begin::Web font -->
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
    <script>
        WebFont.load({
            google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>
    <!--end::Web font -->
    <!--begin::Base Styles -->
    <link href="{!! asset('backend/ezzyr_assets/vendors/base/vendors.bundle.css') !!}" rel="stylesheet" type="text/css" />
    <link href="{!! asset('backend/ezzyr_assets/demo/default/base/style.bundle.css') !!}" rel="stylesheet" type="text/css" />
    <!--end::Base Styles -->
    <link rel="shortcut icon" href="#" />
</head>
<!-- end::Head -->
<!-- end::Body -->
<body class="m--skin- m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default"  >
<!-- begin:: Page -->
<div class="m-grid m-grid--hor m-grid--root m-page">
    <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor m-login m-login--singin m-login--2 m-login-2--skin-3" id="m_login" >
        <div class="m-grid__item m-grid__item--fluid    m-login__wrapper">
            <div class="m-login__container">
                <div class="m-login__logo">
                    <a href="#">
                        <h1></h1><img src="{!! asset('backend/ezzyr_assets/app/media/img/logos/parcelbd.png') !!}" width="300px">
                    </a>
                </div>
                <div class="m-login__signin text-center">
                    <div class="m-login__head">
                        <h1 class="m-login__title mb-3" style="color: #BC496C;!important; font-size: 90px;font-weight: bold;">
                            404
                        </h1>
                        <h3 class="m-login__title" style="color: #fff;!important;">
                            Oops!
                        </h3>
                        <p style="color: #fff;font-size: 18px;">You're looking for something that does't actually exist..</p>
                        <a href="{!! url('/') !!}" class="btn btn-danger text-white mb-5">Go Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end:: Page -->
<!--begin::Base Scripts -->
<script src="{!! asset('backend/ezzyr_assets/vendors/base/vendors.bundle.js') !!}" type="text/javascript"></script>
<script src="{!! asset('backend/ezzyr_assets/demo/default/base/scripts.bundle.js') !!}" type="text/javascript"></script>
<!--end::Base Scripts -->

<!-- Metronic 4 js -->
<script src="{!! asset('backend/ezzyr_assets/plugin/backstretch/jquery.backstretch.js') !!}" type="text/javascript"></script>

<!-- Bacground image slide -->
<script>
    $.backstretch([
            "{!! asset('backend/ezzyr_assets/app/media/img//bg/bg-2.jpg') !!}",
            "{!! asset('backend/ezzyr_assets/app/media/img//bg/bg-4.jpg') !!}",
            "{!! asset('backend/ezzyr_assets/app/media/img//bg/bg-5.jpg') !!}",
            "{!! asset('backend/ezzyr_assets/app/media/img//bg/bg-6.jpg') !!}",
            "{!! asset('backend/ezzyr_assets/app/media/img//bg/bg-7.jpg') !!}",
            "{!! asset('backend/ezzyr_assets/app/media/img//bg/bg-8.jpg') !!}"
        ], {
            fade: 1000,
            duration: 8000
        }
    );
</script>
<style>
    .m-login__signin {
        padding: 30px 30px 5px 30px;
        background-image: url({!! asset('backend/ezzyr_assets/app/media/img/bg-white-lock.png') !!});
        background-size: cover;
        background-repeat: no-repeat;
    }
    .m-login.m-login--2.m-login-2--skin-3 .m-login__container .m-login__form .form-control {
        color: #7668a4;
    }
</style>
</body>
<!-- end::Body -->
</html>