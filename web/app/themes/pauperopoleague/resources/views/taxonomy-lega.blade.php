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
    </div>
  </div>
@endsection
