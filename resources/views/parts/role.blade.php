<form class="col" role="form" method="POST" action="{{url($link)}}">
    {{ csrf_field() }}

    <div class="form-group{{ $errors->has($targetname) ? ' has-error' : '' }}">
        <table class="table table-striped table-hover">
            <tr>

            </tr>
        </table>

        <label for="{{$targetname}}" class="col-md-4 control-label">ロール名</label>

        <div class="col-md-6">
            <input id="new_permission" type="text" class="form-control" name="new_permission" value="{{ old('new_permission') }}">

            @if ($errors->has('new_permission'))
                <span class="help-block">
                    <strong>{{ $errors->first('new_permission') }}</strong>
                </span>
            @endif
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-btn fa-plus"></i> 新規登録
            </button>
        </div>

    </div>

</form>
