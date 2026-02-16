<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ config('mudik.website.tagline') }}" />
    <meta name="author" content="{{ config('mudik.website.name') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/public/images/favicon.ico') }}" />

    <title>@yield('title', config('mudik.website.name'))</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Quattrocento+Sans:400,700|Roboto:400,500,700" rel="stylesheet" />

    <!-- Bootstrap core CSS -->
    <link href="{{ asset('assets/public/css/bootstrap.min.css') }}" rel="stylesheet" />

    <!-- SweetAlert2 with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4@5/bootstrap-4.min.css" rel="stylesheet" />

    <!-- Gijgo Datepicker CSS -->
    <link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />

    <!-- Materialdesign icons css -->
    <link href="{{ asset('assets/public/css/materialdesignicons.min.css') }}" rel="stylesheet" />

    <!-- Mobirise icons css -->
    <link href="{{ asset('assets/public/css/mobiriseicons.css') }}" rel="stylesheet" />

    <!-- flex slider css -->
    <link href="{{ asset('assets/public/css/flexslider.css') }}" rel="stylesheet" type="text/css" media="screen" />

    <!-- Custom styles for this template -->
    <link href="{{ asset('assets/public/css/menu.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/public/css/style.css') }}" rel="stylesheet" />

    <!--Template Color-->
    <link href="{{ asset('assets/public/css/colors/yellow.css') }}" rel="stylesheet" />

    @stack('styles')
  </head>
  <body>
    <!-- Navigation Bar-->
    @yield('header')

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    @include('components.footer')

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="{{ asset('assets/public/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/public/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Gijgo Datepicker JS -->
    <script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>

    <!-- Axios for AJAX requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- CSRF Token setup for Axios -->
    <script>
        // Setup Axios with CSRF token - runs immediately when script loads
        (function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            if (!csrfToken) {
                console.error('CSRF token meta tag not found');
                return;
            }

            // Set default headers for all axios requests
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');

            // Intercept responses to handle CSRF token expiration
            axios.interceptors.response.use(
                response => response,
                error => {
                    if (error.response && error.response.status === 419) {
                        console.error('CSRF token mismatch - session may have expired');
                        alert('Session telah kadaluarsa. Halaman akan di-refresh.');
                        window.location.reload();
                    }
                    return Promise.reject(error);
                }
            );

            console.log('Axios configured with CSRF token');
        })();
    </script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="{{ asset('assets/public/js/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/public/js/scrollspy.min.js') }}" type="text/javascript"></script>

    <!--flex slider plugin-->
    <script src="{{ asset('assets/public/js/jquery.flexslider-min.js') }}" type="text/javascript"></script>
    <!--common script for all pages-->
    <script src="{{ asset('assets/public/js/jquery.app.js') }}"></script>

    @stack('scripts')
  </body>
</html>
