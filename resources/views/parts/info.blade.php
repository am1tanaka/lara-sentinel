@if (session('info') || isset($info))
<div class="alert alert-info">
    @if (session('info'))
        {{ session('info') }}
    @endif
    @if (isset($info))
        {{ $info }}
    @endif
</div>
@endif
