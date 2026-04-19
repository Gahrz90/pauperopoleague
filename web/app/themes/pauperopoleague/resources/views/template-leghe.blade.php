{{--
  Template Name: Leghe
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @include('partials.page-header')
    @include('partials.content-page')
  @endwhile

  <div class="leghe">

    @forelse($leghe as $lega)
      @if(empty($lega['tappe'])) @continue @endif
      <section class="lega">
        <h2 class="lega__titolo">
          <a href="{{ get_term_link($lega['term']) }}">{{ $lega['term']->name }}</a>
        </h2>

        @if($lega['term']->description)
          <p class="lega__descrizione">{{ $lega['term']->description }}</p>
        @endif
      </section>
    @empty
      <p>Nessuna lega disponibile.</p>
    @endforelse

  </div>
@endsection
