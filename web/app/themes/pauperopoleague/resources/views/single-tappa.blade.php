@extends('layouts.app')

@section('content')
  <article @php(post_class('tappa'))>

    <header class="tappa__header">
      <h1 class="tappa__titolo">{{ $titolo }}</h1>

      @if($data_inizio_tappa)
        <p class="tappa__data">
          Chiusura iscrizioni: <strong>{{ $data_inizio_tappa }}</strong>
        </p>
      @endif
    </header>

    {{-- Contenuto principale del post --}}
    <div class="tappa__content">
      @php(the_content())
    </div>

    {{-- Sezione Decklist --}}
    <section
      class="tappa__decklist"
      id="decklist-app"
      data-tappa-id="{{ $tappa_id }}"
      data-tappa-aperta="{{ $tappa_aperta ? 'true' : 'false' }}"
      data-data-inizio="{{ $data_inizio_iso }}"
      data-rest-url="{{ rest_url('paupero/v1/decklist') }}"
      data-rest-nonce="{{ wp_create_nonce('wp_rest') }}"
    >

      @if(!$tappa_aperta)
        {{-- Tappa chiusa: data_inizio_tappa superata --}}
        <div class="decklist__chiusa">
          <p>Non puoi inserire la decklist! Iscrizioni chiuse!</p>
        </div>
      @else
        {{-- Step 1: verifica codice --}}
        <div class="decklist__step" id="step-codice">
          <h2>Inserisci il codice</h2>
          <p>Inserisci il codice tappa per accedere al form di invio decklist.</p>

          <div class="decklist__form-group">
            <label for="codice-tappa">Codice Tappa</label>
            <input
              type="text"
              id="codice-tappa"
              placeholder="Es. TAPPA2024A"
              autocomplete="off"
            />
          </div>

          <button type="button" id="btn-verifica-codice" class="btn btn--primary">
            Verifica codice
          </button>

          <p class="decklist__errore" id="errore-codice" hidden></p>
        </div>

        {{-- Step 2: form decklist (nascosto finché il codice non è verificato) --}}
        <div class="decklist__step" id="step-form" hidden>
          <h2>Inserisci la tua Decklist</h2>

          <div class="decklist__form-group">
            <label for="nome-giocatore">Nome Giocatore *</label>
            <input type="text" id="nome-giocatore" placeholder="Il tuo nome" />
          </div>

          <div class="decklist__form-group">
            <label for="archetipo">Archetipo *</label>
            <input type="text" id="archetipo" placeholder="Es. Mono Red Burn" />
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
      @endif

    </section>

  </article>
@endsection