{{--
  Template Name: Login
--}}

@extends('layouts.app')

@push('scripts')
  <script>
    window.pauperoLogin = {
      apiUrl:      '{{ rest_url('paupero/v1/login') }}',
      nonce:       '{{ wp_create_nonce('wp_rest') }}',
      redirectTo:  '{{ esc_js(wp_validate_redirect(esc_url_raw($_GET['redirect_to'] ?? ''), home_url('/'))) }}',
      registerUrl: '{{ esc_js(get_permalink(get_page_by_path('registrazione')) ?: home_url('/registrazione/')) }}'
    };
  </script>
  @vite('resources/js/login.js')
@endpush

@section('content')
  @php
    if (is_user_logged_in()) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
    $login_status = sanitize_text_field($_GET['login_status'] ?? '');
  @endphp

  <div class="reg-page">
    <div class="container mx-auto px-4">
      <div class="reg-card">

        @if ($login_status === 'logged_out')
          <div class="alert alert-info reg-alert" role="alert">
            Hai effettuato il logout con successo.
          </div>
        @elseif ($login_status === 'required')
          <div class="alert alert-warning reg-alert" role="alert">
            Devi effettuare il login per accedere a questa pagina.
          </div>
        @endif

        <form id="login-form" class="reg-form" novalidate>

          <h1 class="reg-title">Accedi</h1>
          <p class="reg-subtitle">Bentornato nella community Pauperopoleague</p>

          <div id="login-error" class="alert alert-error" role="alert" hidden></div>

          <div class="reg-form__group">
            <label class="form-label" for="login-email">
              Email <span class="reg-required" aria-hidden="true">*</span>
            </label>
            <input
              type="email" id="login-email" name="email"
              class="form-input" autocomplete="email" required
            >
            <span class="form-error-msg" data-for="email"></span>
          </div>

          <div class="reg-form__group">
            <label class="form-label" for="login-password">
              Password <span class="reg-required" aria-hidden="true">*</span>
            </label>
            <input
              type="password" id="login-password" name="password"
              class="form-input" autocomplete="current-password" required
            >
            <span class="form-error-msg" data-for="password"></span>
          </div>

          <div class="reg-form__group" style="flex-direction:row;align-items:center;gap:0.5rem;margin-bottom:1.5rem;">
            <input type="checkbox" id="login-remember" name="remember" value="1" style="width:1rem;height:1rem;accent-color:var(--color-accent);flex-shrink:0;">
            <label class="form-label" for="login-remember" style="margin:0;font-weight:400;color:var(--color-neutral-300);">
              Ricordami
            </label>
          </div>

          <button type="submit" id="login-submit" class="btn btn-gold btn-lg reg-submit">
            <span class="login-submit__label">Accedi</span>
            <span class="reg-submit__spinner" aria-hidden="true" hidden></span>
          </button>

          <p class="reg-login-link" style="margin-top:1.25rem;">
            <a href="{{ wp_lostpassword_url() }}">Password dimenticata?</a>
          </p>

          <p class="reg-login-link">
            Non hai un account?
            <a href="{{ get_permalink(get_page_by_path('registrazione')) ?: home_url('/registrazione/') }}">Registrati</a>
          </p>

        </form>

      </div>
    </div>
  </div>
@endsection
