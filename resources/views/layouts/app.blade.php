<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Mudik Gratis Lebaran 2026')</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2E7D32;
            --primary-dark: #1B5E20;
            --primary-light: #4CAF50;
            --secondary-color: #FF6F00;
            --secondary-dark: #E65100;
            --secondary-light: #FF8F00;
            --success-color: #388E3C;
            --danger-color: #D32F2F;
            --warning-color: #F57C00;
            --info-color: #0288D1;
            --light-bg: #F5F5F5;
            --dark-text: #212121;
            --gray-text: #757575;
            --border-color: #E0E0E0;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.16);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            background-color: #fff;
            overflow-x: hidden;
        }
        
        .heading-font {
            font-family: 'Bebas Neue', cursive;
            letter-spacing: 1px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            padding: 12px 32px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-dark) 100%);
            border: none;
            padding: 12px 32px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, var(--secondary-dark) 0%, var(--secondary-color) 100%);
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25);
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 8px;
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }
        
        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--gray-text);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--dark-text);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @yield('content')
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- CSRF Token Setup - MUST RUN FIRST -->
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
    
    <!-- Global Helper Functions -->
    <script>
        // Global error handler
        window.showError = function(message) {
            const alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Error!</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.getElementById('alert-container') || document.querySelector('.container');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                alert(message);
            }
        };
        
        // Global success handler
        window.showSuccess = function(message) {
            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Success!</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.getElementById('alert-container') || document.querySelector('.container');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                alert(message);
            }
        };
    </script>
    
    @stack('scripts')
</body>
</html>