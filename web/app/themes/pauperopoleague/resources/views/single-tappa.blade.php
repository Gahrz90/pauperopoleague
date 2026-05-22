@extends('layouts.app')

@section('content')
  @php
    $leghe      = get_the_terms(get_the_ID(), 'lega');
    $lega_name  = (!is_wp_error($leghe) && !empty($leghe)) ? $leghe[0]->name : null;
    $lega_url   = (!is_wp_error($leghe) && !empty($leghe)) ? get_term_link($leghe[0]) : null;
  @endphp

  <article class="{!! implode(' ', get_post_class('inner-dark')) !!}">
    <div class="container mx-auto px-4">

      {{-- Page header --}}
      <div class="inner-dark__header">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
          <div>
            @if($lega_name)
              <p class="inner-dark__eyebrow">
                @if($lega_url)
                  <a href="{{ $lega_url }}" style="color:inherit;text-decoration:none;transition:color 0.15s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color=''">{{ $lega_name }}</a>
                @else
                  {{ $lega_name }}
                @endif
              </p>
            @endif
            <h1 class="inner-dark__title">{{ $titolo }}</h1>
            @if($data_inizio_tappa)
              <p class="inner-dark__sub" style="margin-top:0.375rem;">
                @if($tappa_conclusa)
                  Conclusa il {{ $data_inizio_tappa }}
                @else
                  Orario d'inizio tappa: {{ $data_inizio_tappa }}
                @endif
              </p>
            @endif
          </div>
          <div style="display:flex;gap:0.5rem;align-items:flex-start;flex-shrink:0;">
            @if($tappa_conclusa)
              <span class="badge badge-standard">Conclusa</span>
            @elseif($tappa_aperta)
              <span class="badge badge-gold">Aperta</span>
            @else
              <span class="badge badge-draft">In attesa</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Optional WordPress content --}}
      @php $content = get_the_content(); @endphp
      @if($content)
        <div style="color:var(--color-neutral-300);font-size:0.9375rem;line-height:1.7;margin-bottom:2.5rem;max-width:640px;">
          {!! apply_filters('the_content', $content) !!}
        </div>
      @endif

      @if($tappa_conclusa)

        {{-- ── Classifica finale ──────────────────────────── --}}
        @if($classifica_finale)
          <div style="margin-bottom:3rem;">
            <div class="inner-section-header">
              <p class="inner-section-header__label">Classifica finale</p>
              @if($numero_partecipanti)
                <span class="badge badge-standard">{{ $numero_partecipanti }} giocatori</span>
              @endif
            </div>

            <div style="overflow-x:auto;">
              <table class="standings-table">
                <thead>
                  <tr>
                    <th class="standings-table__th standings-table__th--pos">#</th>
                    <th class="standings-table__th standings-table__th--nome">Giocatore</th>
                    <th class="standings-table__th standings-table__th--num">Punti</th>
                    <th class="standings-table__th standings-table__th--num">V/S/P</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($classifica_finale as $row)
                    @php
                      if ($loop->index === 0)      { $medal_icon = '🥇'; $medal_class = 'standings-table__row--gold'; }
                      elseif ($loop->index === 1)  { $medal_icon = '🥈'; $medal_class = 'standings-table__row--silver'; }
                      elseif ($loop->index === 2)  { $medal_icon = '🥉'; $medal_class = 'standings-table__row--bronze'; }
                      else                         { $medal_icon = null;  $medal_class = ''; }
                    @endphp
                    <tr class="standings-table__row {{ $medal_class }}">
                      <td class="standings-table__td standings-table__td--pos">{{ $row['posizione'] }}</td>
                      <td class="standings-table__td standings-table__td--nome">
                        @if($medal_icon)<span aria-hidden="true" style="margin-right:0.3rem;">{{ $medal_icon }}</span>@endif{{ $row['nome'] }}
                      </td>
                      <td class="standings-table__td standings-table__td--num">{{ $row['punti'] }}</td>
                      <td class="standings-table__td standings-table__td--num">{{ $row['vsp'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif

        {{-- ── Top 8 ──────────────────────────────────────── --}}
        @if($mazzi_top8)
          <div style="margin-bottom:3rem;">
            <div class="inner-section-header">
              <p class="inner-section-header__label">Top 8</p>
              <span class="badge badge-gold">{{ count($mazzi_top8) }} mazzi</span>
            </div>

            <div class="hp-two-col__grid">
              @foreach($mazzi_top8 as $i => $deck)
                <details class="deck-card">
                  <summary class="deck-card__summary">
                    <span class="deck-card__player">
                      <span style="font-family:var(--font-mono);font-size:0.6875rem;color:var(--color-neutral-500);margin-right:0.375rem;">#{{ $i + 1 }}</span>
                      {{ $deck['nome_giocatore'] }}
                    </span>
                    <span class="deck-card__archetype">{{ $deck['archetipo'] }}</span>
                    <span class="deck-card__toggle">Vedi lista</span>
                    @if($deck['titolo'])
                      <span class="deck-card__name">{{ $deck['titolo'] }}</span>
                    @endif
                  </summary>

                  <div class="deck-card__list">
                    @foreach(\App\View\Composers\SingleTappa::parseMazzoLines($deck['mazzo']) as $line)
                      @if($line['type'] === 'card')
                        <div class="deck-card__line">
                          <span class="deck-card__qty">{{ $line['qty'] }}</span>
                          <span class="deck-card__card-name" data-card="{{ $line['name'] }}">{{ $line['name'] }}</span>
                        </div>
                      @elseif($line['type'] === 'section')
                        <div class="deck-card__section-label">{{ $line['text'] }}</div>
                      @endif
                    @endforeach
                  </div>
                </details>
              @endforeach
            </div>
          </div>
        @endif

        {{-- ── Metagame ────────────────────────────────────── --}}
        @if($archetype_stats || $card_stats)
          <div>
            <div class="inner-section-header">
              <p class="inner-section-header__label">Metagame</p>
            </div>

            <div class="hp-two-col__grid">

              @if($archetype_stats)
                @php $max_arch = max(array_values($archetype_stats)); @endphp
                <div class="metagame-panel">
                  <p class="metagame-panel__title">Archetipi</p>
                  @foreach($archetype_stats as $name => $count)
                    <div class="meta-bar">
                      <span class="meta-bar__label" title="{{ $name }}">{{ $name }}</span>
                      <div class="meta-bar__track">
                        <div class="meta-bar__fill" style="width:{{ round($count / $max_arch * 100) }}%"></div>
                      </div>
                      <span class="meta-bar__value">{{ $count }} {{ $count === 1 ? 'gioc.' : 'gioc.' }}</span>
                    </div>
                  @endforeach
                </div>
              @endif

              @if($card_stats)
                @php $max_avg = max(array_values($card_stats)); @endphp
                <div class="metagame-panel">
                  <p class="metagame-panel__title">Carte più giocate</p>
                  @foreach($card_stats as $name => $avg)
                    <div class="meta-bar">
                      <span class="meta-bar__label" title="{{ $name }}">{{ $name }}</span>
                      <div class="meta-bar__track">
                        <div class="meta-bar__fill" style="width:{{ round($avg / $max_avg * 100) }}%"></div>
                      </div>
                      <span class="meta-bar__value">{{ number_format($avg, 1) }}x</span>
                    </div>
                  @endforeach
                </div>
              @endif

            </div>
          </div>
        @endif

      @else

        {{-- ── Decklist submission form ────────────────────── --}}
        <section
          class="decklist-form-wrap"
          id="decklist-app"
          data-tappa-id="{{ $tappa_id }}"
          data-rest-url="{{ rest_url('paupero/v1/decklist') }}"
          data-rest-nonce="{{ wp_create_nonce('wp_rest') }}"
          data-user-nome="{{ is_user_logged_in() ? wp_get_current_user()->display_name : '' }}"
        >

          {{-- Step 1: verify code --}}
          <div id="step-codice">
            <div class="inner-section-header" style="margin-bottom:1.5rem;">
              <p class="inner-section-header__label">Invia la tua Decklist</p>
            </div>

            <p style="color:var(--color-neutral-400);font-size:0.9375rem;margin:0 0 1.5rem;line-height:1.6;">
              Inserisci il codice tappa per accedere al form di invio decklist.
            </p>

            <div class="reg-form__group">
              <label class="form-label" for="codice-tappa" style="color:#fff;">Codice Tappa</label>
              <input
                type="text" id="codice-tappa"
                class="form-input" placeholder="Es. TAPPA2024A"
                autocomplete="off"
              >
            </div>

            <button type="button" id="btn-verifica-codice" class="btn btn-gold btn-lg reg-submit">
              Verifica codice
            </button>

            <p class="alert alert-error" id="errore-codice" style="margin-top:1rem;" hidden></p>
          </div>

          {{-- Step 2: decklist form --}}
          <div id="step-form" hidden>
            <div class="inner-section-header" style="margin-bottom:1.5rem;">
              <p class="inner-section-header__label">Inserisci la tua Decklist</p>
            </div>

            <div class="reg-form__row">
              <div class="reg-form__group">
                <label class="form-label" for="nome-giocatore" style="color:#fff;">
                  Nome Giocatore <span class="reg-required">*</span>
                </label>
                <input type="text" id="nome-giocatore" class="form-input" placeholder="Il tuo nome">
              </div>

              <div class="reg-form__group">
                <label class="form-label" for="titolo-mazzo" style="color:#fff;">
                  Nome Mazzo <span class="reg-required">*</span>
                </label>
                <input type="text" id="titolo-mazzo" class="form-input" placeholder="Es. Il mio Mono Red">
              </div>
            </div>

            <div class="reg-form__group">
              <label class="form-label" for="archetipo" style="color:#fff;">
                Archetipo <span class="reg-required">*</span>
              </label>
              <select id="archetipo" class="form-select" disabled>
                <option value="">Caricamento archetipi...</option>
              </select>
            </div>

            <div class="decklist-columns">
              <div>
                <div class="decklist-col__header">
                  <span class="decklist-col__title">Mainboard</span>
                  <span class="decklist-col__counter" id="counter-mainboard">0</span>
                </div>
                <div class="decklist-rows" id="rows-mainboard"></div>
              </div>

              <div>
                <div class="decklist-col__header">
                  <span class="decklist-col__title">Sideboard</span>
                  <span class="decklist-col__counter" id="counter-sideboard">0</span>
                </div>
                <div class="decklist-rows" id="rows-sideboard"></div>
              </div>
            </div>

            <button type="button" id="btn-invia-decklist" class="btn btn-gold btn-lg reg-submit">
              Invia Decklist
            </button>

            <p class="alert alert-error" id="errore-form" style="margin-top:1rem;" hidden></p>
            <p class="alert alert-success" id="successo-form" style="margin-top:1rem;" hidden></p>
          </div>

        </section>

      @endif

    </div>
  </article>
@endsection
