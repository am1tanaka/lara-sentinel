<div class="checkbox">
    @foreach($roles as $rl)
    <label class="checkbox-inline" for="">
        <input type="checkbox"
            name="user_{{$userid=$user ? $user->id : 'new'}}_role_{{$rl->id}}"
            id="user_{{$userid}}_role_{{$rl->id}}"
            {{$user ? ($user->inRole($rl->slug) ? 'checked="true"' : '') : ''}}">{{$rl->name}}
    </label>
    @endforeach
</div>
