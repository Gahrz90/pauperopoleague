@extends('layouts.app')

@section('content')
  <article @php(post_class('tappa'))>

    <header class="tappa__header">
      <h1 class="tappa__titolo">{{ $titolo }}</h1>

      @if($data_inizio_tappa && !$tappa_conclusa)
        <p class="tappa__data">
          Chiusura iscrizioni: <strong>{{ $data_inizio_tappa }}</strong>
        </p>
      @endif
    </header>

    <div class="tappa__content">
      @php(the_content())
    </div>

    @if($tappa_conclusa)

      {{-- ── Risultati ─────────────────────────────────────── --}}
      <div class="tappa__risultati">

        @if($mazzi_top8)
          <section class="risultati__top8">
            <h2>Top 8</h2>

            @foreach($mazzi_top8 as $deck)
              <details class="decklist-card">
                <summary class="decklist-card__summary">
                  <span class="decklist-card__giocatore">{{ $deck['nome_giocatore'] }}</span>
                  <span class="decklist-card__archetipo">{{ $deck['archetipo'] }}</span>
                  <span class="decklist-card__titolo">{{ $deck['titolo'] }}</span>
                </summary>

                <div class="decklist-card__lista">
                  @foreach(\App\View\Composers\SingleTappa::parseMazzoLines($deck['mazzo']) as $line)
                    @if($line['type'] === 'card')
                      <div class="card-line">
                        <span class="card-qty">{{ $line['qty'] }}</span>
                        <span class="card-name" data-card="{{ $line['name'] }}">{{ $line['name'] }}</span>
                      </div>
                    @elseif($line['type'] === 'section')
                      <div class="card-section">{{ $line['text'] }}</div>
                    @else
                      <div class="card-blank"></div>
                    @endif
                  @endforeach
                </div>
              </details>
            @endforeach
          </section>
        @endif

        <section
          class="risultati__metagame"
          id="metagame-stats"
          data-archetypes="{{ json_encode($archetype_stats) }}"
          data-cards="{{ json_encode($card_stats) }}"
        >
          <h2>Metagame</h2>
          <div class="metagame__charts">
            <div class="metagame__chart">
              <h3>Archetipi</h3>
              <div id="chart-archetypes"></div>
            </div>
            <div class="metagame__chart">
              <h3>Carte più giocate</h3>
              <div id="chart-cards"></div>
            </div>
          </div>
        </section>

      </div>

    @else

      {{-- ── Form iscrizione ───────────────────────────────── --}}
      <section
        class="tappa__decklist"
        id="decklist-app"
        data-tappa-id="{{ $tappa_id }}"
        data-rest-url="{{ rest_url('paupero/v1/decklist') }}"
        data-rest-nonce="{{ wp_create_nonce('wp_rest') }}"
      >
        {{-- Step 1: verifica codice --}}
        <div class="decklist__step" id="step-codice">
          <h2>Inserisci il codice</h2>
          <p>Inserisci il codice tappa per accedere al form di invio decklist.</p>

          <div class="decklist__form-group">
            <label for="codice-tappa">Codice Tappa</label>
            <input type="text" id="codice-tappa" placeholder="Es. TAPPA2024A" autocomplete="off" />
          </div>

          <button type="button" id="btn-verifica-codice" class="btn btn--primary">
            Verifica codice
          </button>

          <p class="decklist__errore" id="errore-codice" hidden></p>
        </div>

        {{-- Step 2: form decklist --}}
        <div class="decklist__step" id="step-form" hidden>
          <h2>Inserisci la tua Decklist</h2>

          <div class="decklist__form-group">
            <label for="nome-giocatore">Nome Giocatore *</label>
            <input type="text" id="nome-giocatore" placeholder="Il tuo nome" />
          </div>

          <div class="decklist__form-group">
            <label for="archetipo">Archetipo *</label>
            <select id="archetipo" disabled>
              <option value="">Caricamento arcotipi...</option>
            </select>
          </div>

          <div class="decklist__form-group">
            <label for="titolo-mazzo">Nome Mazzo *</label>
            <input type="text" id="titolo-mazzo" placeholder="Es. Il mio Mono Red" />
          </div>

          <div class="decklist__columns">
            <div class="decklist__column">
              <div class="decklist__column-header">
                <h3>Mainboard</h3>
                <span class="decklist__counter" id="counter-mainboard">60</span>
              </div>
              <div class="decklist__rows" id="rows-mainboard"></div>
            </div>

            <div class="decklist__column">
              <div class="decklist__column-header">
                <h3>Sideboard</h3>
                <span class="decklist__counter" id="counter-sideboard">15</span>
              </div>
              <div class="decklist__rows" id="rows-sideboard"></div>
            </div>
          </div>

          <button type="button" id="btn-invia-decklist" class="btn btn--primary">
            Invia Decklist
          </button>

          <p class="decklist__errore" id="errore-form" hidden></p>
          <p class="decklist__successo" id="successo-form" hidden></p>
        </div>
      </section>

    @endif

  </article>
@endsection
