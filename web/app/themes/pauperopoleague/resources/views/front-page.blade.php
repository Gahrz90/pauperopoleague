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
          <span class="hp-stat__number">{{ $giocatoriCount }}</span>
          <span class="hp-stat__label">GIOCATORI</span>
        </div>
        <div class="hp-stat">
          <span class="hp-stat__number">{{ $tappeCount }}</span>
          <span class="hp-stat__label">TAPPE ORGANIZZATE</span>
        </div>
        <div class="hp-stat">
          <span class="hp-stat__number">{{ $giocatoriMedi }}</span>
          <span class="hp-stat__label">GIOCATORI MEDI A TAPPA</span>
        </div>
        <div class="hp-stat">
          <span class="hp-stat__number hp-stat__number--gold">41</span>
          <span class="hp-stat__label">GIOCATORI IN UNA SINGOLA TAPPA</span>
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
            <p class="hp-card__eyebrow" style="margin-bottom:0;">CLASSIFICA LEGA TOP 10</p>
            <a href="{{ $legaUrl }}" class="hp-card__viewall">Vedi tutto →</a>
          </div>

          <ol class="hp-standings">
            @forelse ($classificaLega as $player)
              <li class="hp-standings__row">
                <span class="hp-standings__rank">{{ $player['posizione'] }}</span>
                <span class="hp-standings__avatar">{{ $player['iniziali'] }}</span>
                <span class="hp-standings__info">
                  <span class="hp-standings__name">{{ $player['nome'] }}</span>
                  <span class="hp-standings__record">{{ $player['record'] }}</span>
                </span>
                <span class="hp-standings__pts">{{ $player['punti'] }}</span>
              </li>
            @empty
              <li class="hp-standings__row" style="justify-content:center;">
                <span class="hp-standings__record">Nessuna tappa conclusa.</span>
              </li>
            @endforelse
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
        <a href="{{ $legaUrl }}" class="hp-card__viewall">Storia completa →</a>
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
            @if ($tappa['n_giocatori'] !== null)
              <p class="hp-tcard__players">{{ $tappa['n_giocatori'] }} giocatori</p>
            @endif
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
          Unisciti alla community di giocatori nel circuito Pauper più competitivo d'Italia!
        </p>
        <a href="{{ get_permalink(get_page_by_path('registrazione')) ?: home_url('/registrazione/') }}" class="btn btn-gold btn-lg">Iscriviti alla Community</a>
      </div>
    </div>
  </section>

@endsection
