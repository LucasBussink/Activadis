@if ($paginator->hasPages())
  <nav class="pagination" role="navigation" aria-label="Paginering">
    @if ($paginator->onFirstPage())
      <span class="page disabled" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
    @else
      <a class="page" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Vorige pagina"><i class="bi bi-chevron-left"></i></a>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="page gap">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="page active" aria-current="page">{{ $page }}</span>
          @else
            <a class="page" href="{{ $url }}" aria-label="Ga naar pagina {{ $page }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a class="page" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Volgende pagina"><i class="bi bi-chevron-right"></i></a>
    @else
      <span class="page disabled" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
    @endif
  </nav>
@endif
