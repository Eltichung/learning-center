@if ($paginator->hasPages())
<div style="display:flex;gap:8px;justify-content:center;align-items:center;padding:14px;flex-wrap:wrap">
  @if ($paginator->onFirstPage())
    <span class="btn ghost sm" style="opacity:.45;pointer-events:none">‹ Trước</span>
  @else
    <a class="btn ghost sm" href="{{ $paginator->previousPageUrl() }}">‹ Trước</a>
  @endif
  <span style="font-size:13px;color:var(--muted)">Trang {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }} · {{ $paginator->total() }} mục</span>
  @if ($paginator->hasMorePages())
    <a class="btn ghost sm" href="{{ $paginator->nextPageUrl() }}">Sau ›</a>
  @else
    <span class="btn ghost sm" style="opacity:.45;pointer-events:none">Sau ›</span>
  @endif
</div>
@endif
