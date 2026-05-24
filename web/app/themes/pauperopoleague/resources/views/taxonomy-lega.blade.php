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

      {{-- Playoff Bracket --}}
      @if($playoffBracket)
        @php
          $quarti     = $playoffBracket['quarti'];
          $semifinali = $playoffBracket['semifinali'];
          $finale     = $playoffBracket['finale'];
          $campione   = $playoffBracket['campione'];
        @endphp

        <div style="margin-top:3rem;">
          <div class="inner-section-header">
            <p class="inner-section-header__label">Playoff Top 8</p>
            @if($campione)
              <span class="badge badge-gold">Campione: {{ $campione }}</span>
            @endif
          </div>

          <div class="bracket-wrap">

            {{-- Quarti --}}
            @if($quarti)
              <div class="bracket-round">
                <p class="bracket-round__label">Quarti di finale</p>
                <div class="bracket-round__matches">
                  @foreach($quarti as $m)
                    <div class="bracket-match{{ $m['vincitore'] ? ' bracket-match--done' : '' }}">
                      <div class="bracket-slot{{ $m['vincitore'] && $m['vincitore'] === $m['p1'] ? ' bracket-slot--winner' : ($m['vincitore'] ? ' bracket-slot--loser' : '') }}">
                        {{ $m['p1'] ?: '—' }}
                      </div>
                      <div class="bracket-slot{{ $m['vincitore'] && $m['vincitore'] === $m['p2'] ? ' bracket-slot--winner' : ($m['vincitore'] ? ' bracket-slot--loser' : '') }}">
                        {{ $m['p2'] ?: '—' }}
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Connector --}}
            @if($quarti && $semifinali)
              <div class="bracket-connector" aria-hidden="true">
                @foreach($semifinali as $i => $sf)
                  <div class="bracket-connector__group">
                    <div class="bracket-connector__line bracket-connector__line--top"></div>
                    <div class="bracket-connector__line bracket-connector__line--mid"></div>
                    <div class="bracket-connector__line bracket-connector__line--bottom"></div>
                  </div>
                @endforeach
              </div>
            @endif

            {{-- Semifinali --}}
            @if($semifinali)
              <div class="bracket-round">
                <p class="bracket-round__label">Semifinali</p>
                <div class="bracket-round__matches bracket-round__matches--sf">
                  @foreach($semifinali as $m)
                    <div class="bracket-match{{ $m['vincitore'] ? ' bracket-match--done' : '' }}">
                      <div class="bracket-slot{{ $m['vincitore'] && $m['vincitore'] === $m['p1'] ? ' bracket-slot--winner' : ($m['vincitore'] ? ' bracket-slot--loser' : '') }}">
                        {{ $m['p1'] ?: '—' }}
                      </div>
                      <div class="bracket-slot{{ $m['vincitore'] && $m['vincitore'] === $m['p2'] ? ' bracket-slot--winner' : ($m['vincitore'] ? ' bracket-slot--loser' : '') }}">
                        {{ $m['p2'] ?: '—' }}
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Connector SF → Finale --}}
            @if($semifinali && ($finale['p1'] || $finale['p2']))
              <div class="bracket-connector bracket-connector--sf" aria-hidden="true">
                <div class="bracket-connector__group">
                  <div class="bracket-connector__line bracket-connector__line--top"></div>
                  <div class="bracket-connector__line bracket-connector__line--mid"></div>
                  <div class="bracket-connector__line bracket-connector__line--bottom"></div>
                </div>
              </div>
            @endif

            {{-- Finale --}}
            @if($finale['p1'] || $finale['p2'])
              <div class="bracket-round bracket-round--finale">
                <p class="bracket-round__label">Finale</p>
                <div class="bracket-round__matches">
                  <div class="bracket-match bracket-match--finale{{ $finale['vincitore'] ? ' bracket-match--done' : '' }}">
                    <div class="bracket-slot{{ $finale['vincitore'] && $finale['vincitore'] === $finale['p1'] ? ' bracket-slot--winner' : ($finale['vincitore'] ? ' bracket-slot--loser' : '') }}">
                      {{ $finale['p1'] ?: '—' }}
                    </div>
                    <div class="bracket-slot{{ $finale['vincitore'] && $finale['vincitore'] === $finale['p2'] ? ' bracket-slot--winner' : ($finale['vincitore'] ? ' bracket-slot--loser' : '') }}">
                      {{ $finale['p2'] ?: '—' }}
                    </div>
                  </div>
                </div>
              </div>
            @endif

            {{-- Champion trophy --}}
            @if($campione)
              <div class="bracket-champion">
                <div class="bracket-champion__trophy" aria-hidden="true">🏆</div>
                <div class="bracket-champion__name">{{ $campione }}</div>
                <div class="bracket-champion__label">Campione</div>
              </div>
            @endif

          </div>
        </div>
      @endif

    </div>
  </div>
@endsection
