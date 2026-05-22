{{--
  Template Name: Registrazione
--}}

@extends('layouts.app')

@push('scripts')
  <script>
    window.pauperoRegister = {
      apiUrl: '{{ rest_url('paupero/v1/register') }}',
      nonce:  '{{ wp_create_nonce('wp_rest') }}'
    };
  </script>
  @vite('resources/js/register.js')
@endpush

@section('content')
  @php
    $reg_status = sanitize_text_field($_GET['reg_status'] ?? '');
  @endphp

  <div class="reg-page">
    <div class="container mx-auto px-4">
      <div class="reg-card">

        {{-- Status messages from email verification redirect --}}
        @if ($reg_status === 'verified')
          <div class="alert alert-success reg-alert" role="alert">
            <strong>Email confermata!</strong> Il tuo account è attivo. Puoi ora effettuare il login
          </div>
        @elseif ($reg_status === 'token_expired')
          <div class="alert alert-error reg-alert" role="alert">
            <strong>Link scaduto.</strong> Il link di conferma è valido solo 24 ore.
            Compila di nuovo il modulo per ricevere un nuovo link.
          </div>
        @elseif ($reg_status === 'invalid_token')
          <div class="alert alert-error reg-alert" role="alert">
            <strong>Link non valido.</strong> Il link di conferma non è riconosciuto.
          </div>
        @endif

        {{-- Success state shown after form submission --}}
        <div id="reg-success" class="reg-success" hidden>
          <div class="reg-success__icon">✓</div>
          <h2 class="reg-success__title">Controlla la tua email!</h2>
          <p class="reg-success__msg">
            Abbiamo inviato un link di conferma a <strong id="reg-success-email"></strong>.<br>
            Clicca il link entro <strong>24 ore</strong> per attivare il tuo account.
          </p>
          <p class="reg-success__hint">Non trovi l'email? Controlla la cartella spam.</p>
        </div>

        {{-- Registration form --}}
        <form id="reg-form" class="reg-form" novalidate>

          <h1 class="reg-title">Registrati</h1>
          <p class="reg-subtitle">Unisciti alla community Pauperopoleague</p>

          <div id="reg-error" class="alert alert-error" role="alert" hidden></div>

          <div class="reg-form__row">
            <div class="reg-form__group">
              <label class="form-label" for="reg-nome">
                Nome <span class="reg-required" aria-hidden="true">*</span>
              </label>
              <input
                type="text" id="reg-nome" name="nome"
                class="form-input" autocomplete="given-name" required
              >
              <span class="form-error-msg" data-for="nome"></span>
            </div>

            <div class="reg-form__group">
              <label class="form-label" for="reg-cognome">
                Cognome <span class="reg-required" aria-hidden="true">*</span>
              </label>
              <input
                type="text" id="reg-cognome" name="cognome"
                class="form-input" autocomplete="family-name" required
              >
              <span class="form-error-msg" data-for="cognome"></span>
            </div>
          </div>

          <div class="reg-form__group">
            <label class="form-label" for="reg-email">
              Email <span class="reg-required" aria-hidden="true">*</span>
            </label>
            <input
              type="email" id="reg-email" name="email"
              class="form-input" autocomplete="email" required
            >
            <span class="form-error-msg" data-for="email"></span>
          </div>

          <div class="reg-form__row">
            <div class="reg-form__group">
              <label class="form-label" for="reg-password">
                Password <span class="reg-required" aria-hidden="true">*</span>
              </label>
              <input
                type="password" id="reg-password" name="password"
                class="form-input" autocomplete="new-password" required
              >
              <span class="form-hint">Minimo 8 caratteri</span>
              <span class="form-error-msg" data-for="password"></span>
            </div>

            <div class="reg-form__group">
              <label class="form-label" for="reg-confirm-password">
                Conferma Password <span class="reg-required" aria-hidden="true">*</span>
              </label>
              <input
                type="password" id="reg-confirm-password" name="confirm_password"
                class="form-input" autocomplete="new-password" required
              >
              <span class="form-error-msg" data-for="confirm_password"></span>
            </div>
          </div>

          <div class="reg-form__row">
            <div class="reg-form__group">
              <label class="form-label" for="reg-data-nascita">Data di nascita</label>
              <input
                type="date" id="reg-data-nascita" name="data_nascita"
                class="form-input" autocomplete="bday"
              >
            </div>

            <div class="reg-form__group">
              <label class="form-label" for="reg-cellulare">Numero cellulare</label>
              <input
                type="tel" id="reg-cellulare" name="cellulare"
                class="form-input" autocomplete="tel"
                placeholder="+39 xxx xxx xxxx"
              >
            </div>
          </div>

          <div class="reg-form__group">
            <label class="form-label" for="reg-bio">Bio</label>
            <textarea
              id="reg-bio" name="bio"
              class="form-input reg-textarea" rows="3"
              placeholder="Parlaci di te come giocatore Pauper..."
            ></textarea>
          </div>

          <div class="reg-form__group">
            <label class="form-label" for="reg-mazzi">Mazzi giocati</label>
            <textarea
              id="reg-mazzi" name="mazzi_giocati"
              class="form-input reg-textarea" rows="3"
              placeholder="Es. Faeries, Burn, Affinity..."
            ></textarea>
            <span class="form-hint">Elenca gli archetipi che preferisci giocare</span>
          </div>

          <button type="submit" id="reg-submit" class="btn btn-gold btn-lg reg-submit">
            <span class="reg-submit__label">Registrati</span>
            <span class="reg-submit__spinner" aria-hidden="true" hidden></span>
          </button>

          <p class="reg-login-link">
            Hai già un account? <a href="{{ wp_login_url() }}">Accedi</a>
          </p>

        </form>

      </div>
    </div>
  </div>
@endsection
