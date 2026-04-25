@extends('layouts.app')

@section('content')
  <div class="inner-dark">
    <div class="container mx-auto px-4">

      {{-- Archive header --}}
      <div class="inner-dark__header">
        <p class="inner-dark__eyebrow">Lega</p>
        <h1 class="inner-dark__title">{{ single_term_title('', false) }}</h1>
        @php($descrizione = term_description())
        @if($descrizione)
          <div class="inner-dark__sub" style="margin-top:0.5rem;">{!! $descrizione !!}</div>
        @endif
      </div>

      {{-- Tappe grid --}}
      @if(!have_posts())
        <div class="hp-card" style="text-align:center;padding:3rem 2rem;">
          <p style="color:var(--color-neutral-500);margin:0;">Nessuna tappa disponibile per questa lega.</p>
        </div>
      @else
        <div class="hp-tournaments__grid">
          @while(have_posts()) @php(the_post())
            @php
              $conclusa_val = get_field('tappa_conclusa');
              $conclusa     = $conclusa_val === true || $conclusa_val === 1 || $conclusa_val === '1';
              $data_raw     = get_field('data_inizio_tappa', null, false);
              $dt           = $data_raw
                              ? \DateTime::createFromFormat('Y-m-d H:i:s', $data_raw, wp_timezone())
                              : null;
              $mesi         = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
              $data_label   = $dt
                              ? ($dt->format('d') . ' ' . $mesi[(int)$dt->format('n') - 1] . ' · ' . $dt->format('H:i'))
                              : null;
              $n_giocatori  = $conclusa ? count(get_field('mazzi') ?: []) : null;
            @endphp
            <a href="{{ get_permalink() }}" class="hp-tcard hp-tcard--link">
              <div class="hp-tcard__top">
                @if($conclusa)
                  <span class="badge badge-standard">Conclusa</span>
                @else
                  <span class="badge badge-gold">Aperta</span>
                @endif
                @if($data_label)
                  <span class="hp-tcard__date">{{ $data_label }}</span>
                @endif
              </div>
              <p class="hp-tcard__name">{{ get_the_title() }}</p>
              @if($n_giocatori !== null)
                <p class="hp-tcard__players">{{ $n_giocatori }} giocatori partecipanti</p>
              @else
                <p class="hp-tcard__players">Iscrizioni aperte</p>
              @endif
            </a>
          @endwhile
        </div>
      @endif

    </div>
  </div>
@endsection
