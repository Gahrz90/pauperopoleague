{{--
  Template Name: Leghe
--}}

@extends('layouts.app')

@section('content')
  <div class="inner-dark">
    <div class="container mx-auto px-4">

      {{-- Page header --}}
      <div class="inner-dark__header">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
          <div>
            <p class="inner-dark__eyebrow">Campionato Pauper</p>
            <h1 class="inner-dark__title">Le Leghe</h1>
            <p class="inner-dark__sub">Esplora le leghe attive e le tappe in programma.</p>
          </div>
          @php($legheAttive = array_filter($leghe, fn($l) => !empty($l['tappe'])))
          @if(count($legheAttive))
            <span class="badge badge-standard">
              {{ count($legheAttive) }} {{ count($legheAttive) === 1 ? 'lega' : 'leghe' }}
            </span>
          @endif
        </div>
      </div>

      {{-- Leagues grid --}}
      @if(empty($legheAttive))
        <div class="hp-card" style="text-align:center;padding:3rem 2rem;">
          <p style="color:var(--color-neutral-500);margin:0;">Nessuna lega disponibile al momento.</p>
        </div>
      @else
        <div class="hp-two-col__grid">
          @foreach($legheAttive as $lega)
            <div class="lega-card">

              <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:0.75rem;">
                <h2 class="lega-card__title">
                  <a href="{{ get_term_link($lega['term']) }}">{{ $lega['term']->name }}</a>
                </h2>
                <span class="badge badge-standard" style="flex-shrink:0;margin-top:0.1rem;">
                  {{ count($lega['tappe']) }} {{ count($lega['tappe']) === 1 ? 'tappa' : 'tappe' }}
                </span>
              </div>

              @if($lega['term']->description)
                <p style="font-size:0.875rem;color:var(--color-neutral-400);margin:0;line-height:1.5;">
                  {{ $lega['term']->description }}
                </p>
              @endif

              <ul class="lega-card__tappe">
                @foreach($lega['tappe'] as $tappa)
                  @php
                    $conclusa_val = get_field('tappa_conclusa', $tappa->ID);
                    $conclusa     = $conclusa_val === true || $conclusa_val === 1 || $conclusa_val === '1';
                    $data_raw     = get_field('data_inizio_tappa', $tappa->ID, false);
                    $dt           = $data_raw
                                    ? \DateTime::createFromFormat('Y-m-d H:i:s', $data_raw, wp_timezone())
                                    : null;
                    $n_giocatori  = $conclusa ? count(get_field('mazzi', $tappa->ID) ?: []) : null;
                  @endphp
                  <li class="lega-tappa-row">
                    <a href="{{ get_permalink($tappa->ID) }}">{{ get_the_title($tappa->ID) }}</a>
                    <div class="lega-tappa-row__meta">
                      @if($dt)
                        <span class="lega-tappa-row__date">{{ $dt->format('d/m/Y') }}</span>
                      @endif
                      @if($n_giocatori !== null)
                        <span class="lega-tappa-row__date">{{ $n_giocatori }} gioc.</span>
                      @endif
                      @if($conclusa)
                        <span class="badge badge-standard">Conclusa</span>
                      @else
                        <span class="badge badge-gold">Aperta</span>
                      @endif
                    </div>
                  </li>
                @endforeach
              </ul>

            </div>
          @endforeach
        </div>
      @endif

    </div>
  </div>
@endsection
