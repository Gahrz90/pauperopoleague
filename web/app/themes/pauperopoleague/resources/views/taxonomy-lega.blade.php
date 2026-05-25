@extends('layouts.app')

@section('content')
  <div class="inner-dark">
    <div class="container mx-auto px-4">

      {{-- Archive header --}}
      <div class="inner-dark__header">
        <p class="inner-dark__eyebrow">Lega</p>
        <h1 class="inner-dark__title">{{ single_term_title('', false) }}</h1>
        @php $descrizione = term_description(); @endphp
        @if($descrizione)
          <div class="inner-dark__sub" style="margin-top:0.5rem;">{!! $descrizione !!}</div>
        @endif
      </div>

      {{-- Two-column layout: standings left, tappe right --}}
      <div class="lega-layout">

        {{-- Left: Classifica Lega --}}
        <div>
          <div class="inner-section-header">
            <p class="inner-section-header__label">Classifica Lega</p>
            @if($classificaLega)
              <span class="badge badge-standard">{{ count($classificaLega) }} giocatori</span>
            @endif
          </div>

          @if($classificaLega)
            <div style="overflow-x:auto;">
              <table class="standings-table">
                <thead>
                  <tr>
                    <th class="standings-table__th standings-table__th--pos">#</th>
                    <th class="standings-table__th standings-table__th--nome">Giocatore</th>
                    <th class="standings-table__th standings-table__th--num">Punti</th>
                    <th class="standings-table__th standings-table__th--num">V/S/P</th>
                    <th class="standings-table__th standings-table__th--num">Tappe</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($classificaLega as $player)
                    @php
                      if ($loop->index === 0)     { $medal_icon = '🥇'; $medal_class = 'standings-table__row--gold'; }
                      elseif ($loop->index === 1) { $medal_icon = '🥈'; $medal_class = 'standings-table__row--silver'; }
                      elseif ($loop->index === 2) { $medal_icon = '🥉'; $medal_class = 'standings-table__row--bronze'; }
                      else                        { $medal_icon = null;  $medal_class = ''; }
                    @endphp
                    <tr class="standings-table__row {{ $medal_class }}">
                      <td class="standings-table__td standings-table__td--pos">{{ $player['posizione'] }}</td>
                      <td class="standings-table__td standings-table__td--nome">
                        @if($medal_icon)<span aria-hidden="true" style="margin-right:0.3rem;">{{ $medal_icon }}</span>@endif{{ $player['nome'] }}
                      </td>
                      <td class="standings-table__td standings-table__td--num">{{ $player['punti'] }}</td>
                      <td class="standings-table__td standings-table__td--num">{{ $player['vsp'] }}</td>
                      <td class="standings-table__td standings-table__td--num">{{ $player['tappe'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p style="color:var(--color-neutral-500);margin:0;">Nessuna tappa conclusa.</p>
          @endif
        </div>

        {{-- Right: Tappe concluse --}}
        <div>
          <div class="inner-section-header">
            <p class="inner-section-header__label">Tappe Concluse</p>
            @if($tappeChiuse)
              <span class="badge badge-standard">{{ count($tappeChiuse) }}</span>
            @endif
          </div>

          @if($tappeChiuse)
            <div class="lega-tappe-list">
              @foreach($tappeChiuse as $tappa)
                <a href="{{ $tappa['permalink'] }}" class="hp-tcard hp-tcard--link">
                  <div class="hp-tcard__top">
                    <span class="badge badge-standard">Conclusa</span>
                    @if($tappa['data_label'])
                      <span class="hp-tcard__date">{{ $tappa['data_label'] }}</span>
                    @endif
                  </div>
                  <h3 class="hp-tcard__name">{{ $tappa['titolo'] }}</h3>
                  @if($tappa['n_giocatori'] !== null)
                    <p class="hp-tcard__players">{{ $tappa['n_giocatori'] }} giocatori partecipanti</p>
                  @endif
                </a>
              @endforeach
            </div>
          @else
            <p style="color:var(--color-neutral-500);margin:0;">Nessuna tappa conclusa.</p>
          @endif
        </div>

      </div>

      {{-- Playoff Bracket + Podio --}}
      @if($playoffBracket || $podio)
        @php
          $qf    = $playoffBracket ? $playoffBracket['quarti']     : [];
          $sf    = $playoffBracket ? $playoffBracket['semifinali']  : [];
          $fin   = $playoffBracket ? $playoffBracket['finale']      : null;
          $champ = $playoffBracket ? $playoffBracket['campione']    : null;

          $slotClass = function(array $match, string $player): string {
            if (!$match['vincitore']) return 'b-slot';
            if ($player === '?')      return 'b-slot b-slot--tbd';
            return $player === $match['vincitore'] ? 'b-slot b-slot--win' : 'b-slot b-slot--out';
          };
        @endphp

        <div class="b-bracket-section">

          <div class="inner-section-header">
            <p class="inner-section-header__label">Playoff Top 8</p>
            @if($champ)
              <span class="badge badge-gold">Campione: {{ $champ }}</span>
            @endif
          </div>

          <div class="b-bracket-section__body">

          @if($playoffBracket)
          <div class="b-bracket">

            {{-- ── Quarti ──────────────────────────────── --}}
            <div class="b-round b-round--qf">
              <p class="b-round__label">Quarti di finale</p>
              <div class="b-round__body">
                @foreach($qf as $m)
                  <div class="b-item">
                    <div class="b-match">
                      <div class="{{ $slotClass($m, $m['p1']) }}">{{ $m['p1'] }}</div>
                      <div class="{{ $slotClass($m, $m['p2']) }}">{{ $m['p2'] }}</div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            {{-- ── Connector QF→SF (2 groups) ────────── --}}
            <div class="b-conn-col" aria-hidden="true">
              <div class="b-conn b-conn--2"></div>
              <div class="b-conn b-conn--2"></div>
            </div>

            {{-- ── Semifinali ──────────────────────────── --}}
            <div class="b-round b-round--sf">
              <p class="b-round__label">Semifinali</p>
              <div class="b-round__body">
                @foreach($sf as $m)
                  <div class="b-item b-item--x2">
                    <div class="b-match">
                      <div class="{{ $slotClass($m, $m['p1']) }}">{{ $m['p1'] }}</div>
                      <div class="{{ $slotClass($m, $m['p2']) }}">{{ $m['p2'] }}</div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            {{-- ── Connector SF→Final (1 group) ─────── --}}
            <div class="b-conn-col" aria-hidden="true">
              <div class="b-conn b-conn--4"></div>
            </div>

            {{-- ── Finale ──────────────────────────────── --}}
            <div class="b-round b-round--final">
              <p class="b-round__label">Finale</p>
              <div class="b-round__body">
                <div class="b-item b-item--x4">
                  <div class="b-match b-match--final">
                    <div class="{{ $slotClass($fin, $fin['p1']) }}">{{ $fin['p1'] }}</div>
                    <div class="{{ $slotClass($fin, $fin['p2']) }}">{{ $fin['p2'] }}</div>
                  </div>
                </div>
              </div>
            </div>

            {{-- ── Champion ─────────────────────────────── --}}
            @if($champ)
              <div class="b-champion-col">
                <div class="b-champion">
                  <span class="b-champion__trophy" aria-hidden="true">🏆</span>
                  <span class="b-champion__name">{{ $champ }}</span>
                  <span class="b-champion__label">Campione</span>
                </div>
              </div>
            @endif

          </div>
          @endif

          {{-- Podio --}}
          @if($podio)
            <div class="b-podio" role="list" aria-label="Podio finale">

              <div class="b-podio__stage b-podio__stage--2" role="listitem">
                <p class="b-podio__name">{{ $podio['secondo'] ?: '—' }}</p>
                <div class="b-podio__block">
                  <span class="b-podio__pos" aria-label="Secondo classificato">2</span>
                </div>
              </div>

              <div class="b-podio__stage b-podio__stage--1" role="listitem">
                <p class="b-podio__name">{{ $podio['primo'] ?: '—' }}</p>
                <div class="b-podio__block">
                  <span class="b-podio__pos" aria-label="Primo classificato">1</span>
                </div>
              </div>

              <div class="b-podio__stage b-podio__stage--3" role="listitem">
                <p class="b-podio__name">{{ $podio['terzo'] ?: '—' }}</p>
                <div class="b-podio__block">
                  <span class="b-podio__pos" aria-label="Terzo classificato">3</span>
                </div>
              </div>

            </div>
          @endif

          </div>{{-- /.b-bracket-section__body --}}
        </div>
      @endif

    </div>
  </div>
@endsection
