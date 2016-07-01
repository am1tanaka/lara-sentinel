<div class="form-group">
    <label for="cb_user_{{$userid}}" class="col-md-4 control-label">ロール</label>
    <div class="col-md-6 col-md-offset-4">
        <div class="checkbox" id="cb_user_{{$userid}}">
            @foreach($roles as $rl)
            <label class="checkbox-inline">
                <input type="checkbox" name="user_{{$userid}}_role_{{$rl->id}} {{old('user_$userid_role_$rl->id')=='1' ? checked : ''}}">{{$rl->name}}
            </label>
            @endforeach
        </div>
    </div>
</div>
