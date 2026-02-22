@extends('layouts.app')

@section('title', 'Mudik Gratis Pemerintah Kab. Tangerang 2026')

@push('styles')
<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 50%, #4CAF50 100%);
        position: relative;
        overflow: hidden;
    }

    .login-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255,255,255,0.08) 0%, transparent 50%);
    }

    .login-card {
        background: white;
        border-radius: 24px;
        padding: 50px 40px;
        box-shadow: 0 16px 48px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 450px;
        position: relative;
        z-index: 1;
    }

    .login-logo {
        text-align: center;
        margin-bottom: 40px;
    }

    .login-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary-dark);
        text-align: center;
        margin-bottom: 10px;
    }

    .login-subtitle {
        text-align: center;
        color: var(--gray-text);
        margin-bottom: 40px;
    }

    .form-floating > .form-control {
        border-radius: 12px;
    }

    .form-floating > label {
        padding-left: 20px;
    }

    @media (max-width: 576px) {
        .login-card {
            margin: 20px;
            padding: 40px 30px;
        }
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="bi bi-shield-lock-fill text-white" style="font-size: 2.5rem;"></i>
            </div>
        </div>

        <h1 class="login-title heading-font">ADMIN LOGIN</h1>
        <p class="login-subtitle">Mudik Gratis Pemerintah Kab. Tangerang 2026</p>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Login Gagal!</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form method="POST" action="{{ route('auth.login.process') }}">
            @csrf

            <div class="form-floating mb-3">
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    placeholder="name@example.com"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
                <label for="email">
                    <i class="bi bi-envelope-fill me-2"></i>Email Address
                </label>
            </div>

            <div class="form-floating mb-3">
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="Password"
                    required
                >
                <label for="password">
                    <i class="bi bi-lock-fill me-2"></i>Password
                </label>
            </div>

            <div class="form-check mb-4">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="remember"
                    name="remember"
                >
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 12px;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('public.landing') }}" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
