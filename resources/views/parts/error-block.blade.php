{{-- 以下のような入力要素を作って利用する
<div class="form-group{{ $errors->has('入力要素のname') ? ' has-error' : '' }}">
    <input name="入力要素のname" class="form-control" ・・・
    @include('本ファイル', ['name' => '入力要素のname'])
</div>
--}}
@if ($errors->has($name))
    <span class="help-block">
        <strong>{{ $errors->first($name) }}</strong>
    </span>
@endif
