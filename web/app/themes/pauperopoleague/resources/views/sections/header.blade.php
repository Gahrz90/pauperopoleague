<header class="banner">
  <div class="container mx-auto flex items-center justify-between px-4 md:px-0" style="height:4rem;">

    {{-- Logo + site name --}}
    <a class="brand flex items-center gap-2.5 no-underline!" href="{{ home_url('/') }}">
      <img src="@asset('resources/images/logo_pauperopoleague.png')" alt="{{ $siteName }}" class="h-12 w-12 object-contain rounded-full">
      <span class="brand-name">PAUPEROPOLEAGUE</span>
    </a>

    {{-- Desktop nav + CTA --}}
    <div class="hidden md:flex items-center gap-6">
      @if (has_nav_menu('primary_navigation'))
        <nav class="nav-primary" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
          {!! wp_nav_menu([
              'theme_location' => 'primary_navigation',
              'menu_class'     => 'flex items-center space-x-6',
              'echo'           => false,
          ]) !!}
        </nav>
      @endif
      @if (is_user_logged_in())
        @php $currentUser = wp_get_current_user(); @endphp
        <a href="{{ get_permalink(get_page_by_path('profilo')) ?: home_url('/profilo/') }}"
           class="user-avatar" aria-label="Il tuo profilo">
          {{ strtoupper(substr($currentUser->first_name ?: $currentUser->display_name, 0, 1)) }}
        </a>
        <a href="{{ wp_logout_url(add_query_arg('login_status', 'logged_out', get_permalink(get_page_by_path('login')) ?: home_url('/'))) }}" class="btn btn-gold btn-sm">Esci</a>
      @else
        <a href="{{ get_permalink(get_page_by_path('login')) ?: wp_login_url() }}" class="btn btn-secondary btn-sm">Accedi</a>
        <a href="{{ get_permalink(get_page_by_path('registrazione')) ?: home_url('/registrazione/') }}" class="btn btn-gold btn-sm">Iscriviti</a>
      @endif
    </div>

    {{-- Hamburger --}}
    <button class="hamburger md:hidden flex flex-col justify-between w-6 h-5 focus:outline-none z-50" aria-label="Toggle Menu" aria-expanded="false">
      <span class="block h-0.5 w-full rounded"></span>
      <span class="block h-0.5 w-full rounded"></span>
      <span class="block h-0.5 w-full rounded"></span>
    </button>

  </div>

  {{-- Mobile Sidebar --}}
  <aside id="mobileSidebar" class="mobile-sidebar fixed top-0 right-0 h-full w-64 shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-6 flex justify-end">
      <button id="closeSidebar" class="text-2xl font-bold focus:outline-none">&times;</button>
    </div>
    @if (has_nav_menu('primary_navigation'))
      <nav class="flex flex-col space-y-4 px-6">
        {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class'     => 'flex flex-col space-y-4',
            'echo'           => false,
        ]) !!}
      </nav>
    @endif
    <div class="px-6 mt-6 flex flex-col gap-3">
      @if (is_user_logged_in())
        @php $currentUser ??= wp_get_current_user(); @endphp
        <div class="flex items-center gap-3 mb-2">
          <a href="{{ get_permalink(get_page_by_path('profilo')) ?: home_url('/profilo/') }}"
             class="user-avatar" aria-label="Il tuo profilo">
            {{ strtoupper(substr($currentUser->first_name ?: $currentUser->display_name, 0, 1)) }}
          </a>
          <span style="font-size:0.875rem;color:var(--color-neutral-300);font-weight:500;">{{ $currentUser->display_name }}</span>
        </div>
        <a href="{{ wp_logout_url(add_query_arg('login_status', 'logged_out', get_permalink(get_page_by_path('login')) ?: home_url('/'))) }}" class="btn btn-gold btn-sm" style="justify-content:center;">Esci</a>
      @else
        <a href="{{ get_permalink(get_page_by_path('login')) ?: wp_login_url() }}" class="btn btn-secondary btn-sm" style="justify-content:center;">Accedi</a>
        <a href="{{ get_permalink(get_page_by_path('registrazione')) ?: home_url('/registrazione/') }}" class="btn btn-gold btn-sm" style="justify-content:center;">Iscriviti</a>
      @endif
    </div>
  </aside>

  {{-- Overlay --}}
  <div id="overlay" class="overlay fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>
</header>
