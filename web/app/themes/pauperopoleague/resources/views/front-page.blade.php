@extends('layouts.app')

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       HERO
  ═══════════════════════════════════════════════════════ --}}
  <section class="hp-hero">
    <div class="hp-hero__inner">
      <div class="hp-season-badge">
        STAGIONE 1 · WINTER / SUMMER 2026 · ITALIA
      </div>
      <h1 class="hp-hero__title">Pauperopoleague</h1>
      <p class="hp-hero__subtitle">
        Il punto di riferimento per i giocatori pauper di Empoli e non solo.<br>
        Tornei settimanali, classifiche, premi e tanto divertimento!
      </p>
      <div class="hp-hero__ctas">
        <a href="{{ get_permalink(get_page_by_path('registrazione')) ?: home_url('/registrazione/') }}" class="btn btn-gold btn-lg">Iscriviti alla Community</a>
        <a href="#" class="btn hp-btn-outline btn-lg">Vedi il Calendario</a>
      </div>
    </div>
  </section>

  {{-- ═══════════════════════════════════════════════════════
       STATS BAR
  ═══════════════════════════════════════════════════════ --}}
  <section class="hp-stats">
    <div class="container mx-auto px-4">
      <div class="hp-stats__grid">
        <div class="hp-stat">
          <span class="hp-stat__number">142</span>
          <span class="hp-stat__label">GIOCATORI</span>
        </div>
        <div class="hp-stat">
          <span class="hp-stat__number">{{ $legheCount }}</span>
          <span class="hp-stat__label">LEGHE ORGANIZZATE</span>
        </div>
        <div class="hp-stat">
          <span class="hp-stat__number">38</span>
          <span class="hp-stat__label">GIOCATORI MEDI A TAPPA</span>
        </div>
        <div class="hp-stat">
          <span class="hp-stat__number hp-stat__number--gold">€4.2k</span>
          <span class="hp-stat__label">PREMI ASSEGNATI</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ═══════════════════════════════════════════════════════
       NEXT EVENT + SEASON STANDINGS
  ═══════════════════════════════════════════════════════ --}}
  <section class="hp-two-col">
    <div class="container mx-auto px-4">
      <div class="hp-two-col__grid">

        {{-- Next Event --}}
        <div class="hp-card">
          <p class="hp-card__eyebrow">PROSSIMA TAPPA</p>

          @if ($prossimaTappa)
            <h2 class="hp-card__title">{{ $prossimaTappa['titolo'] }}</h2>
            <p class="hp-card__sub">{{ $prossimaTappa['lega_name'] }}</p>

            <div class="hp-countdown" data-event-date="{{ $prossimaTappa['data_iso'] }}">
              <div class="hp-countdown__item">
                <span class="hp-countdown__val" data-unit="days">--</span>
                <span class="hp-countdown__unit">GIORNI</span>
              </div>
              <div class="hp-countdown__item">
                <span class="hp-countdown__val" data-unit="hours">--</span>
                <span class="hp-countdown__unit">ORE</span>
              </div>
              <div class="hp-countdown__item">
                <span class="hp-countdown__val" data-unit="minutes">--</span>
                <span class="hp-countdown__unit">MIN</span>
              </div>
            </div>

            <p class="hp-card__meta">{{ $prossimaTappa['data_label'] }}</p>
            <a href="{{ $prossimaTappa['permalink'] }}" class="btn btn-gold btn-lg hp-card__cta">Vai alla tappa →</a>
          @else
            <p class="hp-card__sub" style="margin-top:0.5rem;">Nessuna tappa in programma al momento.</p>
          @endif
        </div>

        {{-- Season Standings --}}
        <div class="hp-card">
          <div class="hp-card__header">
            <p class="hp-card__eyebrow" style="margin-bottom:0;">CLASSIFICA LEGA</p>
            <a href="#" class="hp-card__viewall">Vedi tutto →</a>
          </div>

          <ol class="hp-standings">
            <li class="hp-standings__row">
              <span class="hp-standings__rank">1</span>
              <span class="hp-standings__avatar">MF</span>
              <span class="hp-standings__info">
                <span class="hp-standings__name">Marco Ferretti</span>
                <span class="hp-standings__record">31V · 8P</span>
              </span>
              <span class="hp-standings__pts">847</span>
            </li>
            <li class="hp-standings__row">
              <span class="hp-standings__rank">2</span>
              <span class="hp-standings__avatar">SB</span>
              <span class="hp-standings__info">
                <span class="hp-standings__name">Sofia Bianchi</span>
                <span class="hp-standings__record">28V · 10P</span>
              </span>
              <span class="hp-standings__pts">791</span>
            </li>
            <li class="hp-standings__row">
              <span class="hp-standings__rank">3</span>
              <span class="hp-standings__avatar">LR</span>
              <span class="hp-standings__info">
                <span class="hp-standings__name">Luca Romano</span>
                <span class="hp-standings__record">26V · 11P</span>
              </span>
              <span class="hp-standings__pts">744</span>
            </li>
            <li class="hp-standings__row">
              <span class="hp-standings__rank">4</span>
              <span class="hp-standings__avatar">GE</span>
              <span class="hp-standings__info">
                <span class="hp-standings__name">Giulia Esposito</span>
                <span class="hp-standings__record">24V · 13P</span>
              </span>
              <span class="hp-standings__pts">698</span>
            </li>
            <li class="hp-standings__row">
              <span class="hp-standings__rank">5</span>
              <span class="hp-standings__avatar">AR</span>
              <span class="hp-standings__info">
                <span class="hp-standings__name">Alessandro Ricci</span>
                <span class="hp-standings__record">22V · 14P</span>
              </span>
              <span class="hp-standings__pts">651</span>
            </li>
          </ol>
        </div>

      </div>
    </div>
  </section>

  {{-- ═══════════════════════════════════════════════════════
       RECENT TOURNAMENTS
  ═══════════════════════════════════════════════════════ --}}
  <section class="hp-tournaments">
    <div class="container mx-auto px-4">
      <div class="hp-tournaments__header">
        <h6 class="hp-section-label">TAPPE RECENTI</h6>
        <a href="#" class="hp-card__viewall">Storia completa →</a>
      </div>

      <div class="hp-tournaments__grid">

        @forelse ($tappeRecenti as $tappa)
          <a href="{{ $tappa['permalink'] }}" class="hp-tcard hp-tcard--link">
            <div class="hp-tcard__top">
              @if ($tappa['data_label'])
                <span class="hp-tcard__date">{{ $tappa['data_label'] }}</span>
              @endif
            </div>
            <h3 class="hp-tcard__name">{{ $tappa['titolo'] }}</h3>
            <p class="hp-tcard__players">{{ $tappa['n_giocatori'] }} giocatori</p>
          </a>
        @empty
          <p class="hp-tcard__players" style="color:var(--color-neutral-400);">Nessuna tappa conclusa.</p>
        @endforelse

      </div>
    </div>
  </section>

  {{-- ═══════════════════════════════════════════════════════
       BOTTOM CTA
  ═══════════════════════════════════════════════════════ --}}
  <section class="hp-cta-section">
    <div class="container mx-auto px-4">
      <div class="hp-cta-box">
        <h2 class="hp-cta-box__title">Pronto a competere?</h2>
        <p class="hp-cta-box__sub">
          Unisciti a 142 giocatori nel circuito Pauper più competitivo d'Italia.
        </p>
        <a href="{{ get_permalink(get_page_by_path('registrazione')) ?: home_url('/registrazione/') }}" class="btn btn-gold btn-lg">Iscriviti alla Community</a>
      </div>
    </div>
  </section>

@endsection
