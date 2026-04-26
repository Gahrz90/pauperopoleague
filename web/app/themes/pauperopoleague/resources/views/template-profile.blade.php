{{--
  Template Name: Profilo
--}}

@extends('layouts.app')

@push('scripts')
  <script>
    window.pauperoProfile = {
      apiUrl:      '{{ rest_url('paupero/v1/profile') }}',
      passwordUrl: '{{ rest_url('paupero/v1/profile/password') }}',
      nonce:       '{{ wp_create_nonce('wp_rest') }}'
    };
  </script>
  @vite('resources/js/profile.js')
@endpush

@section('content')
  @php
    if (!is_user_logged_in()) {
        wp_safe_redirect(add_query_arg('login_status', 'required', get_permalink(get_page_by_path('login')) ?: wp_login_url()));
        exit;
    }
    $user          = wp_get_current_user();
    $nome          = $user->first_name;
    $cognome       = $user->last_name;
    $bio           = $user->description;
    $data_nascita  = get_user_meta($user->ID, 'paupero_data_nascita', true);
    $cellulare     = get_user_meta($user->ID, 'paupero_cellulare', true);
    $mazzi_giocati = get_user_meta($user->ID, 'paupero_mazzi_giocati', true);
    $initials      = strtoupper(
      substr($nome ?: $user->display_name, 0, 1) .
      substr($cognome, 0, 1)
    );
  @endphp

  <div class="reg-page">
    <div class="container mx-auto px-4">
      <div class="reg-card profile-card">

        <div id="profile-success" class="alert alert-success reg-alert" role="alert" hidden></div>
        <div id="profile-error" class="alert alert-error reg-alert" role="alert" hidden></div>

        {{-- Avatar + identity --}}
        <div class="profile-hero">
          <div class="profile-avatar-lg">{{ $initials }}</div>
          <div>
            <h1 class="profile-name">{{ $user->display_name }}</h1>
            <p class="profile-email">{{ $user->user_email }}</p>
          </div>
        </div>

        {{-- Profile data form --}}
        <form id="profile-form" class="reg-form" novalidate>

          <h2 class="profile-section-title">Dati personali</h2>

          <div class="reg-form__row">
            <div class="reg-form__group">
              <label class="form-label" for="prof-nome">Nome</label>
              <input type="text" id="prof-nome" name="nome" class="form-input"
                     autocomplete="given-name" value="{{ esc_attr($nome) }}">
            </div>
            <div class="reg-form__group">
              <label class="form-label" for="prof-cognome">Cognome</label>
              <input type="text" id="prof-cognome" name="cognome" class="form-input"
                     autocomplete="family-name" value="{{ esc_attr($cognome) }}">
            </div>
          </div>

          <div class="reg-form__row">
            <div class="reg-form__group">
              <label class="form-label" for="prof-data-nascita">Data di nascita</label>
              <input type="date" id="prof-data-nascita" name="data_nascita" class="form-input"
                     autocomplete="bday" value="{{ esc_attr($data_nascita) }}">
            </div>
            <div class="reg-form__group">
              <label class="form-label" for="prof-cellulare">Numero cellulare</label>
              <input type="tel" id="prof-cellulare" name="cellulare" class="form-input"
                     autocomplete="tel" placeholder="+39 xxx xxx xxxx"
                     value="{{ esc_attr($cellulare) }}">
            </div>
          </div>

          <div class="reg-form__group">
            <label class="form-label" for="prof-bio">Bio</label>
            <textarea id="prof-bio" name="bio" class="form-input reg-textarea" rows="3"
                      placeholder="Parlaci di te come giocatore Pauper...">{{ esc_textarea($bio) }}</textarea>
          </div>

          <div class="reg-form__group">
            <label class="form-label" for="prof-mazzi">Mazzi giocati</label>
            <textarea id="prof-mazzi" name="mazzi_giocati" class="form-input reg-textarea" rows="3"
                      placeholder="Es. Faeries, Burn, Affinity...">{{ esc_textarea($mazzi_giocati) }}</textarea>
            <span class="form-hint">Elenca gli archetipi che preferisci giocare</span>
          </div>

          <button type="submit" id="prof-submit" class="btn btn-gold btn-lg reg-submit">
            <span class="prof-submit__label">Salva modifiche</span>
            <span class="reg-submit__spinner" aria-hidden="true" hidden></span>
          </button>

        </form>

        <hr class="profile-divider">

        {{-- Password change form --}}
        <form id="profile-pw-form" class="reg-form" novalidate>

          <h2 class="profile-section-title">Cambia password</h2>

          <div id="pw-success" class="alert alert-success" role="alert" hidden></div>
          <div id="pw-error" class="alert alert-error" role="alert" hidden></div>

          <div class="reg-form__group">
            <label class="form-label" for="prof-current-pw">
              Password attuale <span class="reg-required" aria-hidden="true">*</span>
            </label>
            <input type="password" id="prof-current-pw" name="current_password" class="form-input"
                   autocomplete="current-password">
            <span class="form-error-msg" data-for="current_password"></span>
          </div>

          <div class="reg-form__row">
            <div class="reg-form__group">
              <label class="form-label" for="prof-new-pw">
                Nuova password <span class="reg-required" aria-hidden="true">*</span>
              </label>
              <input type="password" id="prof-new-pw" name="new_password" class="form-input"
                     autocomplete="new-password">
              <span class="form-hint">Minimo 8 caratteri</span>
              <span class="form-error-msg" data-for="new_password"></span>
            </div>
            <div class="reg-form__group">
              <label class="form-label" for="prof-confirm-pw">
                Conferma password <span class="reg-required" aria-hidden="true">*</span>
              </label>
              <input type="password" id="prof-confirm-pw" name="confirm_new_password" class="form-input"
                     autocomplete="new-password">
              <span class="form-error-msg" data-for="confirm_new_password"></span>
            </div>
          </div>

          <button type="submit" id="pw-submit" class="btn btn-gold btn-lg reg-submit">
            <span class="pw-submit__label">Aggiorna password</span>
            <span class="reg-submit__spinner" aria-hidden="true" hidden></span>
          </button>

        </form>

        <hr class="profile-divider">

        <div class="profile-logout">
          <a href="{{ wp_logout_url(add_query_arg('login_status', 'logged_out', get_permalink(get_page_by_path('login')) ?: home_url('/'))) }}"
             class="btn btn-danger btn-sm">
            Esci dall'account
          </a>
        </div>

      </div>
    </div>
  </div>
@endsection
