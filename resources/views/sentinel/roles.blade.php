@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            @include('parts.info')
            @if (count($errors) > 0)
                <!-- Form Error List -->
                <div class="alert alert-danger">
                    <strong>以下のエラーが発生しました。</strong>
                    <br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h4>パーミッション</h4>

            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>パーミッション名</th>
                        <th>
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 新規登録-->
                    <form class="col" role="form" method="POST" action="{{url('permissions')}}">
                        <tr>
                            {{ csrf_field() }}
                            <td>
                                <div class="form-group{{ $errors->has('new_permission') ? ' has-error' : '' }}">
                                    <input id="new_permission" type="text" class="form-control" name="new_permission" value="{{ old('new_permission') }}">
                                    @if ($errors->has('new_permission'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('new_permission') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-plus"></i> 新規登録
                                </button>
                            </td>
                        </tr>
                    </form>
                </tbody>
            </table>

            <h4>パーミッションの削除</h4>

            <div class="row">
                <div class="col-md-12">
                    @foreach($permissions as $permission)
                        <form style="display:inline" class="col" role="form" method="POST" action="{{url('permissions', base64_encode($permission))}}">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-default" aria-label="Close">
                                <i class="fa fa-btn fa-remove"></i> {{$permission}}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            <hr>

            <h4>ロール追加</h4>

            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>
                            ロール名(日本語可)
                        </th>
                        <th>
                            Slug
                        </th>
                        <th>
                            パーミッション
                        </th>
                        <th>
                            操作
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <!-- 新規登録-->
                    <form class="col" role="form" method="POST" action="{{url('roles')}}">
                        {{ csrf_field() }}

                        <tr>
                            <td>
                                <div class="form-group{{ $errors->has('new_role') ? ' has-error' : '' }}">
                                    <input id="new_role" type="text" class="form-control" name="new_role" value="{{ old('new_role') }}">
                                    @if ($errors->has('new_role'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('new_role') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="form-group{{ $errors->has('new_slug') ? ' has-error' : '' }}">
                                    <input id="new_slug" type="text" class="form-control" name="new_slug" value="{{ old('new_slug') }}">
                                    @if ($errors->has('new_slug'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('new_slug') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @foreach ($permissions as $per)
                                    <div>
                                        <input type="checkbox" name="new_per_{{$per}}" id="new_per_{{$per}}"
                                            {{old("new_per_".$per)=="on" ? 'checked="true"' : ''}}
                                        > {{$per}}
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-plus"></i> 新規登録
                                </button>
                            </td>
                        </tr>


                        </div>

                    </form>

                </tbody>
            </table>

            <h4>ロール一覧</h4>
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>
                            ロール名(日本語可)
                        </th>
                        <th>
                            Slug
                        </th>
                        <th>
                            パーミッション
                        </th>
                        <th colspan="2">
                            操作
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(Sentinel::getRoleRepository()->all() as $role)
                        <tr>
                            <td>
                                {{$role->name}}
                            </td>
                            <td>
                                {{$role->slug}}
                            </td>
                            <td>
                                @foreach ($permissions as $per)
                                    <div>
                                        <input type="checkbox" name="name" value=""
                                            @if (array_key_exists($per, $role->permissions))
                                                {{$role->permissions[$per] ? 'checked="true"' : ''}}
                                            @endif
                                        > {{$per}}
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-refresh"></i> 変更
                                </button>
                            </td>
                            <td>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-btn fa-remove"></i> 削除
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>
</div>
@endsection
