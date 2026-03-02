<!-- COPYRIGHT -->
<div class="footer-alt bg-dark py-3">
    <div class="container text-center text-white">

        <div class="mb-2">
            <a href="{{ route('public.terms-privacy') }}"
               class="text-white text-decoration-underline">
                Syarat & Ketentuan
            </a>
            <span class="mx-2">|</span>
            <a href="{{ route('public.terms-privacy') }}"
               class="text-white text-decoration-underline">
                Kebijakan Privasi
            </a>
        </div>

        <p class="copy-rights mb-0">
            © {{ date('Y') }} {{ config('mudik.website.name') }}.
            Seluruh Hak Dilindungi.
        </p>

    </div>
</div>
