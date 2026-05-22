{{--
  Template Name: Logout
--}}

@extends('layouts.app')

@section('content')
  @php
    if (!is_user_logged_in()) {
        wp_safe_redirect(home_url('/'));
        exit;
    }

    $logout_url = home_url('/') . '?paupero_logout=1&_wpnonce=' . wp_create_nonce('paupero_logout');
  @endphp

  <div class="reg-page">
    <div class="container mx-auto px-4">
      <div class="reg-card" style="text-align:center;">

        <div style="font-size:3rem;margin-bottom:1rem;">👋</div>
        <h1 class="reg-title">Vuoi disconnetterti?</h1>
        <p class="reg-subtitle" style="margin-bottom:2rem;">
          Stai per uscire dal tuo account Pauperopoleague.
        </p>

        <div style="display:flex;flex-direction:column;gap:0.75rem;">
          <a href="{{ $logout_url }}" class="btn btn-gold btn-lg" style="justify-content:center;">
            Disconnetti
          </a>
          <a href="{{ home_url('/') }}" class="btn btn-secondary btn-sm" style="justify-content:center;">
            Annulla
          </a>
        </div>

      </div>
    </div>
  </div>
@endsection
