@foreach (['danger', 'warning', 'success', 'info'] as $msg)
  @if(session()->has($msg))
    <div class="flash-message alert alert-{{ $msg }}">{{ session()->get($msg) }}</div>
  @endif
@endforeach
