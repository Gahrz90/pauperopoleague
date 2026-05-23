<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="@asset('resources/images/logo_pauperopoleague.png')">
    <script>
      (function(){
        var valid = ['dimir','azorius','boros','golgari','gruul','simic'];
        var t = localStorage.getItem('paupero_theme') || {!! json_encode(is_user_logged_in() && in_array(get_user_meta(get_current_user_id(), 'paupero_theme', true), ['dimir','azorius','boros','golgari','gruul','simic'], true) ? get_user_meta(get_current_user_id(), 'paupero_theme', true) : 'dimir') !!};
        if (!valid.includes(t)) t = 'dimir';
        document.documentElement.setAttribute('data-theme', t);
      }());
    </script>
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/decklist.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
      <a class="sr-only focus:not-sr-only" href="#main">
        {{ __('Skip to content', 'sage') }}
      </a>

      @include('sections.header')

      <main id="main" class="main">
        @yield('content')
      </main>

      @hasSection('sidebar')
        <aside class="sidebar">
          @yield('sidebar')
        </aside>
      @endif

      @include('sections.footer')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
    @stack('scripts')
  </body>
</html>
