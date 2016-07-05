<div class="checkbox">
    @foreach($roles as $rl)
    <label class="checkbox-inline" for="">
        <input type="checkbox"
            name="user_{{$userid=$user ? $user->id : 'new'}}_role_{{$rl->id}}"
            id="user_{{$userid}}_role_{{$rl->id}}"
            @if ($user)
                @if ($user->inRole($rl->slug))
                    checked="true"
                @endif
            @elseif (old('user_'.$userid.'_role_'.$rl->id) == "on")
                checked="true"
            @endif
            >
        {{$rl->name}}
    </label>
    @endforeach
</div>
