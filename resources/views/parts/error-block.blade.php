{{--
    コントロールを以下のような
<div class="form-group{{ $errors->has('対応するID') ? ' has-error' : '' }}">
    <input class="form-control" ・・・
    @include('本ファイル', ['id' => '対応するID'])
</div>
--}}
@if ($errors->has($id))
    <span class="help-block">
        <strong>{{ $errors->first($id) }}</strong>
    </span>
@endif
