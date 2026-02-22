@props([
    'menuItems' => [
        ['label' => 'Beranda', 'href' => '#home', 'active' => true],
        ['label' => 'Persyaratan', 'href' => '#terms'],
        ['label' => 'Cara Daftar', 'href' => '#how-to'],
        ['label' => 'Daftar', 'href' => '#registration'],
    ]
])

<!-- Navigation Bar-->
<header id="topnav" class="defaultscroll fixed-top sticky">
  <div class="container">
    <!-- Logo container-->
    <div>
      <a href="{{ route('public.landing') }}" class="logo text-uppercase">
        <img src="{{ asset('assets/public/images/285x114.png') }}" alt="" class="logo-light" height="135" style="height: 135px; margin-top: 10px" />
        <img src="{{ asset('assets/public/images/285x114x2.png') }}" alt="" class="logo-dark" height="65" style="height: 65px" />
      </a>
    </div>
    <!-- End Logo container-->
    <div class="menu-extras">
      <div class="menu-item">
        <!-- Mobile menu toggle-->
        <a class="navbar-toggle">
          <div class="lines">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </a>
        <!-- End mobile menu toggle-->
      </div>
    </div>
    <div id="navigation">
      <!-- Navigation Menu-->
      <ul class="navigation-menu">
        @foreach($menuItems as $item)
          <li class="{{ $item['active'] ?? false ? 'active' : '' }}">
            <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</header>
