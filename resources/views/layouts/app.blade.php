<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Essential Meta Tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- CSRF Token for AJAX requests -->
<meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Kenswed Technical and Vocational Training College (KTVTC) | Best College in Ngong')</title>
  <meta name="description" content="@yield('meta_description', 'Kenswed Technical and Vocational Training College (KTVTC) in Ngong, Kenya offers hands-on training in ICT, Tailoring, Hair & Beauty, Solar Electrical, Entrepreneurship, Welding and more. Empowering youth through skills and innovation.')" />
  <meta name="keywords" content="@yield('meta_keywords', 'Kenswed College, Kenswed Technical College, KTVTC, Ngong College, Best College in Ngong, Coding College in Ngong, Technical Training Kenya, Vocational Courses, TVET Ngong')" />
  <meta name="author" content="Kenswed Technical and Vocational Training College" />
  <link rel="canonical" href="@yield('canonical', 'https://ktvtc.ac.ke/')" />

  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('Assets/images/Kenswed_logo.png') }}" />
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('Assets/images/Kenswed_logo.png') }}" />
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('Assets/images/Kenswed_logo.png') }}" />
  <link rel="shortcut icon" href="{{ asset('Assets/images/Kenswed_logo.png') }}" />
  <meta name="theme-color" content="#0b3d91" />

  <!-- Social Media Meta -->
  <meta property="og:locale" content="en_US" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="@yield('og_title', 'Kenswed Technical and Vocational Training College (KTVTC) | Ngong, Kenya')" />
  <meta property="og:description" content="@yield('og_description', 'Empowering youth through technical and vocational training in Ngong — offering ICT, entrepreneurship, tailoring, beauty, solar electrical, and more.')" />
  <meta property="og:url" content="@yield('og_url', 'https://ktvtc.ac.ke/')" />
  <meta property="og:site_name" content="Kenswed Technical and Vocational Training College" />
  <meta property="og:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}" />
  <meta property="og:image:alt" content="Kenswed Technical and Vocational Training College Logo" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="@yield('twitter_title', 'Kenswed Technical and Vocational Training College (KTVTC)')" />
  <meta name="twitter:description" content="@yield('twitter_description', 'KTVTC in Ngong, Kenya — training youth in ICT, tailoring, beauty, entrepreneurship, solar electrical and more.')" />
  <meta name="twitter:image" content="{{ asset('Assets/images/Kenswed_logo.png') }}" />
  <meta name="twitter:image:alt" content="Kenswed College Logo" />
  <meta name="twitter:site" content="@kenswed_tvtc" />
  <meta name="twitter:creator" content="@kenswed_tvtc" />

  <!-- Styles & Frameworks -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- For better mobile experience -->
<meta name="format-detection" content="telephone=no">
<!-- For iOS -->
<meta name="apple-mobile-web-app-capable" content="yes">
  <!-- Fonts & Custom Styles -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7fafc;
    }
  </style>
  @stack('styles')
</head>

<body class="bg-gray-100">

    <!-- Information Bar -->
    <div class="bg-[#B91C1C] text-white py-2">
        <div class="container mx-auto px-4 flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">

            <!-- Support button (hidden on mobile, visible md+) -->
            <div class="hidden md:flex items-center space-x-2">
            <span>Have any questions?</span>
            <button class="bg-white text-[#B91C1C] font-semibold px-4 py-1  transition-colors duration-200">
                Talk to support now!
            </button>
            </div>

            <!-- Phone & Email -->
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <!-- Phone icon visible only on desktop -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="hidden md:inline h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.774a11.037 11.037 0 006.103 6.103l.774-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.292 18 4.381 15.688 2.593 12.553 1.258 10.261 1 8.794 1 7.333V5a1 1 0 011-1z"
                        clip-rule="evenodd" />
                    </svg>
                    <span>+254 790 148 509</span>
                </div>

                <div class="flex items-center">
                    <!-- Email icon visible only on desktop -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="hidden md:inline h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    <span>info@ktvtc.ac.ke</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="bg-white shadow-lg relative z-50">
        <div class="container mx-auto px-4 flex justify-between items-center py-2">
            <!-- Logo -->
            <div class="flex items-center">
            <img src="{{asset ('Assets/images/Kenswed_logo.png') }}" alt="Kenswed College Logo"
                class="w-18 h-18 sm:w-18 sm:h-18"
                onerror="this.onerror=null; this.src='https://placehold.co/48x48/B91C1C/ffffff?text=Logo';">
            </div>

            <!-- Desktop Nav Links -->
            <div class="hidden md:flex items-center space-x-6">
            <a href="/" class="text-black hover:text-red-700">Home</a>

            <div class="relative group">
              <!-- Simple About Us link without dropdown -->
<a href="{{ route ("aboutus.index") }}" class="text-black hover:text-red-700">
    About Us
</a>
            </div>



            <div class="relative group">
    <button class="flex items-center gap-1 text-black hover:text-red-700">
        Departments <span class="text-xs">▼</span>
    </button>
    <div class="absolute left-0 mt-2 w-44 bg-white shadow-lg rounded-md hidden group-hover:block">
        @foreach($departments as $department)
            <a href="{{ route('departments.show', $department->slug) }}" class="block px-4 py-2 hover:bg-gray-100">
                {{ $department->name }}
            </a>
        @endforeach
    </div>
</div>
   <div class="relative group">
        <button class="flex items-center gap-1 text-black hover:text-red-700">
            Student Life <span class="text-xs">▼</span>
        </button>
        <div class="absolute left-0 mt-2 w-44 bg-white shadow-lg rounded-md hidden group-hover:block z-50">
            <a href="{{ route('public.cafeteria.index') }}" class="block px-4 py-2 hover:bg-gray-100">
                <i class="fas fa-utensils mr-2 text-gray-500"></i> Cafeteria
            </a>
            <!-- Library option commented out since it's not ready -->
            <!--
            <a href="#" class="block px-4 py-2 text-gray-400 cursor-not-allowed">
                <i class="fas fa-book mr-2"></i> Library (Coming Soon)
            </a>
            -->
        </div>
    </div>

            <a href="{{ route('event.index') }}" class="text-black hover:text-red-700">Events</a>
            <a href="{{route ('blog.index') }}" class="text-black hover:text-red-700">News</a>
            <a href="{{ route('contact') }}" class="text-black hover:text-red-700">Contact Us</a>
            </div>

        <!-- Desktop Social Media -->
        <div class="hidden md:flex bg-[#BF1F30] px-3 py-2 space-x-3">
            <!-- Facebook -->
            <a href="https://www.facebook.com/KENSWEDVTC"   target="_blank">
                <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/facebook.svg"
                    class="w-5 h-5 filter invert" alt="Facebook">
            </a>

            <!-- WhatsApp -->
            <a href="wa.me/254790148509" target="_blank">
                <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/whatsapp.svg"
                    class="w-5 h-5 filter invert" alt="WhatsApp">
            </a>

            <!-- Instagram -->
            <a href="https://www.instagram.com/kenswed_technical_college/" target="_blank">
                <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/instagram.svg"
                    class="w-5 h-5 filter invert" alt="Instagram">
            </a>

            <!-- LinkedIn -->
            <a href="https://www.linkedin.com/company/kenswed-organization/" target="_blank">
                <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/linkedin.svg"
                    class="w-5 h-5 filter invert" alt="LinkedIn">
            </a>

            <!-- TikTok -->
            <a href="https://www.tiktok.com/@kenswedtechnicalcollege" target="_blank">
                <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/tiktok.svg"
                    class="w-5 h-5 filter invert" alt="TikTok">
            </a>
        </div>

            <!-- Mobile Menu Icon -->
            <div class="md:hidden">
            <button id="menu-btn" class="text-black hover:text-red-700 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            </div>
        </div>
    </nav>

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

    <!-- Side Drawer Menu -->
    <div id="side-drawer"
    class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg transform -translate-x-full transition-transform duration-300 z-50 flex flex-col">

    <!-- Header -->
    <div class="flex items-center p-4 border-b">
        <img src="Assets/images/Kenswed_logo.png" alt="Logo" class="w-18 h-18 rounded-full">
        <button id="close-btn" class="ml-auto text-[#BF1F30] hover:text-red-700">✕</button>
    </div>

    <!-- Menu Links -->
    <ul class="p-4 space-y-1 flex-1 overflow-y-auto">
    <li>
        <a href="/" class="flex items-center px-3 py-2 rounded-lg text-gray-800 hover:bg-red-50 hover:text-red-700 transition">
        <i class="fas fa-home mr-2 text-red-600"></i> Home
        </a>
    </li>

    <li>
    <button class="w-full flex justify-between items-center px-3 py-2 rounded-lg text-gray-800 hover:bg-red-50 hover:text-red-700 transition dropdown-btn">
        <span><i class="fas fa-info-circle mr-2 text-red-600"></i> About Us</span>
        <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
    </button>
    <ul class="ml-6 mt-1 hidden space-y-1 dropdown-menu">
        <li><a href="{{ route('aboutus.index') }}" class="block px-3 py-1.5 rounded text-gray-600 hover:bg-gray-100">Our Story</a></li>
        <li><a href="{{ route('aboutus.index') }}" class="block px-3 py-1.5 rounded text-gray-600 hover:bg-gray-100">Mission</a></li>
    </ul>
</li>




  <li>
    <button class="w-full flex justify-between items-center px-3 py-2 rounded-lg text-gray-800 hover:bg-red-50 hover:text-red-700 transition dropdown-btn">
        <span><i class="fas fa-building mr-2 text-red-600"></i> Departments</span>
        <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
    </button>
    <ul class="ml-6 mt-1 hidden space-y-1 dropdown-menu">
        @foreach(\App\Models\Department::where('is_active', true)->orderBy('name', 'asc')->get() as $department)
            <li>
                <a href="{{ route('departments.show', $department->slug) }}" class="block px-3 py-1.5 rounded text-gray-600 hover:bg-gray-100">
                    {{ $department->name }}
                </a>
            </li>
        @endforeach
    </ul>
</li>
   <li>
        <button class="w-full flex justify-between items-center px-3 py-2 rounded-lg text-gray-800 hover:bg-red-50 hover:text-red-700 transition dropdown-btn">
            <span><i class="fas fa-user-graduate mr-2 text-red-600"></i> Student Life</span>
            <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
        </button>
        <ul class="ml-6 mt-1 hidden space-y-1 dropdown-menu">
            <li>
                <a href="{{ route('public.cafeteria.index') }}" class="flex items-center px-3 py-1.5 rounded text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-utensils mr-2 text-gray-500"></i> Cafeteria
                </a>
            </li>
            <!-- Library option commented out since it's not ready -->
            <!--
            <li>
                <a href="#" class="flex items-center px-3 py-1.5 rounded text-gray-400 hover:bg-gray-100 cursor-not-allowed">
                    <i class="fas fa-book mr-2"></i> Library (Coming Soon)
                </a>
            </li>
            -->
        </ul>
    </li>
    <li>
        <a href="{{route ('event.index') }}" class="flex items-center px-3 py-2 rounded-lg text-gray-800 hover:bg-red-50 hover:text-red-700 transition">
        <i class="fas fa-users mr-2 text-red-600"></i> Events
        </a>
    </li>
    <li>
        <a href="{{ route('blog.index') }}" class="flex items-center px-3 py-2 rounded-lg text-gray-800 hover:bg-red-50 hover:text-red-700 transition">
        <i class="fas fa-clipboard-list mr-2 text-red-600"></i> News
        </a>
    </li>
    <li>
        <a href="{{ route('contact') }}" class="flex items-center px-3 py-2 rounded-lg text-gray-800 hover:bg-red-50 hover:text-red-700 transition">
        <i class="fas fa-envelope mr-2 text-red-600"></i> Contact Us
        </a>
    </li>
    </ul>


    <!-- Footer with Social Icons & Credit -->
    <div class="p-4 border-t mt-2">
        <div class="flex justify-center space-x-4 mb-3 text-red-700">
                <a href="https://www.facebook.com/KENSWEDVTC"   target="_blank"><i class="fab fa-facebook-f text-xl text-[#BF1F30] hover:text-red-700"></i></a>
                <a href="wa.me/254790148509" target="_blank"><i class="fab fa-whatsapp text-xl text-[#BF1F30] hover:text-red-700"></i></a>
                <a href="https://www.instagram.com/kenswed_technical_college/" target="_blank"><i class="fab fa-instagram text-xl text-[#BF1F30] hover:text-red-700"></i></a>
                <a href="https://www.linkedin.com/company/kenswed-organization/" target="_blank"><i class="fab fa-linkedin-in text-xl text-[#BF1F30] hover:text-red-700"></i></a>
                <a href="https://www.tiktok.com/@kenswedtechnicalcollege" target="_blank"><i class="fab fa-tiktok text-xl text-[#BF1F30] hover:text-red-700"></i></a>
            </div>
            <p class="text-center text-xs text-gray-500">
            Developed by <a href="https://quickofficepointe.co.ke" target="_blank" class="hover:underline text-red-700">Quick Office Pointe</a>
            </p>
        </div>
    </div>

        <main >
            @yield('content')
        </main>



    <!-- Footer -->
    <footer class="bg-red-700 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <!-- Logo and Contact Info -->
                <div class="col-span-1 lg:col-span-2">
                    <img class="h-18 mb-4" src="{{asset ('Assets/images/Footer_logo.png') }}" alt="Kenswed College Logo">
                    <p class="flex items-center mb-2">
                        <span class="mr-2"><img src="{{asset ('Assets/images/phone-svg.svg') }}"
                                class="w-5 h-5 filter invert" alt="Facebook"></span> +254 790 148 509
                    </p>
                    <p class="flex items-center mb-4">
                        <span class="mr-2"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/gmail.svg"
                                class="w-5 h-5 filter invert" alt="Facebook"></span>  <span>info@ktvtc.ac.ke</span>
                    </p>
                    <div class="flex space-x-4 bg">
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/KENSWEDVTC"   target="_blank">
                            <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/facebook.svg"
                                class="w-5 h-5 filter invert" alt="Facebook">
                        </a>

                        <!-- WhatsApp -->
                        <a href="wa.me/254790148509" target="_blank">
                            <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/whatsapp.svg"
                                class="w-5 h-5 filter invert" alt="WhatsApp">
                        </a>

                        <!-- Instagram -->
                        <a href="https://www.instagram.com/kenswed_technical_college/" target="_blank">
                            <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/instagram.svg"
                                class="w-5 h-5 filter invert" alt="Instagram">
                        </a>

                        <!-- LinkedIn -->
                        <a href="https://www.linkedin.com/company/kenswed-organization/" target="_blank">
                            <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/linkedin.svg"
                                class="w-5 h-5 filter invert" alt="LinkedIn">
                        </a>

                        <!-- TikTok -->
                        <a href="https://www.tiktok.com/@kenswedtechnicalcollege" target="_blank">
                            <img src="https://cdn.jsdelivr.net/npm/simple-icons@v9/icons/tiktok.svg"
                                class="w-5 h-5 filter invert" alt="TikTok">
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="font-bold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('mobile-schools.index') }}" class="hover:underline">M-School</a></li>
                        <li><a href="{{ route('aboutus.index') }}" class="hover:underline">About Us</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:underline">Contact Us</a></li>
                        <li><a href="{{ route('policie.index') }}" class="hover:underline">Privacy Policy</a></li>
                        <li><a href="{{ route('policie.index') }}" class="hover:underline">Terms & Conditions</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div>
                    <h3 class="font-bold text-lg mb-4">Resources</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('course.index') }}" class="hover:underline">Our Courses</a></li>
                        <li><a href="{{ route('faq.index') }}" class="hover:underline">FAQS</a></li>
                        <li><a href="{{ route('gallerie.index') }}" class="hover:underline">Gallery</a></li>
                        <li><a href="{{ route('download.index') }}" class="hover:underline">Downloads</a></li>
                        <li><a href="{{ route('event.index') }}" class="hover:underline">Events</a></li>
                    </ul>
                </div>

                <!-- News & Media -->
                <div>
                    <h3 class="font-bold text-lg mb-4">News & Media</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:underline">Our Alumni</a></li>
                        <li><a href="{{ route('partner.index') }}" class="hover:underline">Our partners</a></li>
                        <li><a href="{{ route('blog.index')  }}" class="hover:underline">News & Blogs</a></li>
                        <li><a href="{{ route('courseintake.show') }}" class="hover:underline">Our Intakes</a></li>
                    </ul>
                </div>

            </div>

            <!-- Footer Bottom -->
            <div class="mt-12 pt-8 relative">
                <!-- Line -->
                <div class="w-full h-20 overflow-hidden">
                    <img src="Assets/images/footer_HR.svg" class="w-full" alt="">
                </div>

                <!-- Copyright -->
                <p class="text-sm text-center mt-8">
                    &copy; 2025 Copyright: KENSWED COLLEGE | Developed By
                    <a href="https://quickofficepointe.co.ke" target="_blank" class="underline hover:text-gray-300">
                        Quick office Pointe
                    </a>
                </p>
            </div>
        </div>
    </footer>

    <script src="Assets/JS/drawer.js"></script>
<script>
    // Initialize Swiper
    document.addEventListener('DOMContentLoaded', function() {
        const swiper = new Swiper('.bannerSwiper', {
            // Optional parameters
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            speed: 1000,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },

            // If we need pagination
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },

            // Navigation arrows
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    });
</script>
</body>
</html>
