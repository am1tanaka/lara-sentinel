@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">

            <form class="col" role="form" method="POST" action="{{url('users')}}">
                {{ csrf_field() }}

                @if ($role=="admin")
                <div class="panel panel-default">
                    <div class="panel-heading">ユーザー新規登録</div>
                    <div class="panel-body">

                        @include('parts.user-entry')

                        <div class="form-group">
                            <label for="cb_user_new" class="col-md-4 control-label">ロール</label>
                            <div class="col-md-6" id="cb_user_new">
                                @include('parts.role-select', ['user' => false, 'roles' => $roles])
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-user"></i> ユーザー登録
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                @endif

                <div class="panel panel-default">
                    <div class="panel-heading">ユーザー一覧</div>

                    <table class="table table-striped">

                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        {{$user->first_name}}
                                    </td>
                                    <td>
                                        @include('parts.role-select', ['user' => $user, 'roles' => $roles])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                </table>

                    <div class="panel-body">

                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<input type="hidden" name="_method" value="">
@endsection
