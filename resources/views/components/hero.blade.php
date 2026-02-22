@props([
    'sectionId' => 'home',
    'slides' => [
        [
            'title' => 'Daftar Sekarang, Tempat Terbatas!',
            'subtitle' => 'Wujudkan Impian Mudik Bersama Keluarga<br>Program Pemerintah Kabupaten Tangerang<br/> untuk Meringankan Beban Masyarakat',
            'buttonText' => 'Daftar Sekarang !',
            'buttonLink' => '#registration',
        ],
        [
            'title' => 'Program Mudik Kabupaten Tangerang 2026',
            'subtitle' => 'Komitmen Pemerintah dalam Memberikan Layanan Transportasi mudik yang Layak<br>Aman, Nyaman, dan Terpercaya',
            'buttonText' => 'Daftar Sekarang !',
            'buttonLink' => '#registration',
        ],
    ]
])

<!--Hero Section-->
<section class="bg-home home-height-half" id="{{ $sectionId }}">
  <div class="bg-overlay"></div>
  <div class="home-center">
    <div class="home-desc-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10 text-center">
            <div class="image-logo-mudik d-block d-md-none text-center position-absolute start-50 translate-middle" style="top: 35%; z-index: 3">
                <img src="{{ asset('assets/public/images/285x114.png') }}" alt="" class="logo-light" height="240" style="height: 240px; margin-bottom: 30px" />
            </div>
            <div class="d-block d-md-none" style="height: 180px"></div>
            <div class="main-slider">
              <ul class="slides">
                @foreach($slides as $slide)
                  <li>
                    <h6 class="home-title text-white">{{ $slide['title'] }}</h6>
                    <p class="pt-4 home-sub-title text-white mx-auto">
                      {!! $slide['subtitle'] !!}
                    </p>
                    <!-- Check condition button -->
                    @if(!empty($slide['buttonText']) && !empty($slide['buttonLink']))
                      <div class="watch-video pt-4">
                        <a href="{{ $slide['buttonLink'] }}" class="btn btn-custom">{{ $slide['buttonText'] }}</a>
                      </div>
                    @endif
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script type="text/javascript">
  $(".main-slider").flexslider({
    slideshowSpeed: 5000,
    directionNav: false,
    controlNav: true,
    autoplay: true,
    animation: "fade",
  });
</script>
@endpush
