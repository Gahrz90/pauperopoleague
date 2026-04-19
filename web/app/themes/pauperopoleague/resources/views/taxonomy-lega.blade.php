@extends('layouts.app')

@section('content')
  <div class="lega-archive">

    <header class="lega-archive__header">
      <h1 class="lega-archive__titolo">{{ single_term_title('', false) }}</h1>

      @php($descrizione = term_description())
      @if($descrizione)
        <div class="lega-archive__descrizione">{!! $descrizione !!}</div>
      @endif
    </header>

    <ul class="lega-archive__tappe">
      @while(have_posts()) @php(the_post())
        <li class="lega-archive__tappa">
          <a href="{{ get_permalink() }}">{{ get_the_title() }}</a>
        </li>
      @endwhile
    </ul>

  </div>
@endsection
