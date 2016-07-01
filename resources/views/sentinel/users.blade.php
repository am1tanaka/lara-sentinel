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

                        @include('parts.role-select', ['userid' => 'new'])

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

                    <div class="panel-body">

                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<input type="hidden" name="_method" value="">
@endsection
