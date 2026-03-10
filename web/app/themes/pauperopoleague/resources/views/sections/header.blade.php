<header class="banner relative bg-white border-b border-gray-200 z-50">
  <div class="container mx-auto flex items-center justify-between py-4 px-4 md:px-0">

    {{-- Logo --}}
    <a class="brand" href="{{ home_url('/') }}">
      <img src="@asset('resources/images/logo_pauperopoleague.png')" alt="{{ $siteName }}" class="h-32 w-32 object-contain">
    </a>

    {{-- Desktop Menu --}}
    @if (has_nav_menu('primary_navigation'))
      <nav class="nav-primary hidden md:flex space-x-6" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'flex space-x-6',
            'echo' => false,
        ]) !!}
      </nav>
    @endif

    {{-- Hamburger Button --}}
    <button class="hamburger md:hidden flex flex-col justify-between w-6 h-5 focus:outline-none z-50" aria-label="Toggle Menu" aria-expanded="false">
      <span class="block h-0.5 w-full bg-gray-800 rounded"></span>
      <span class="block h-0.5 w-full bg-gray-800 rounded"></span>
      <span class="block h-0.5 w-full bg-gray-800 rounded"></span>
    </button>

  </div>

  {{-- Mobile Sidebar --}}
  <aside id="mobileSidebar" class="mobile-sidebar fixed top-0 right-0 h-full w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-6 flex justify-end">
      <button id="closeSidebar" class="text-2xl font-bold focus:outline-none">&times;</button>
    </div>
    @if (has_nav_menu('primary_navigation'))
      <nav class="flex flex-col space-y-4 px-6">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'flex flex-col space-y-4',
            'echo' => false,
        ]) !!}
      </nav>
    @endif
  </aside>

  {{-- Overlay --}}
  <div id="overlay" class="overlay fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>
</header>