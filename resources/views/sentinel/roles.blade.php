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
                        <th>操作</th>
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
                                    @include('parts.error-block', ['name' => 'new_permission'])
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
                        <button type="button" class="btn btn-default" aria-label="Close" data-toggle="modal"
                            data-target="#delete-permission-{{array_search($permission, $permissions)}}">
                            <i class="fa fa-btn fa-remove"></i> {{$permission}}
                        </button>
                        @include('parts.modal', [
                            'id' => 'delete-permission-'.array_search($permission, $permissions),
                            'title' => trans('sentinel.delete_permission_title'),
                            'body' => trans('sentinel.confirm_delete_permission').' : '.$permission,
                            'action' => url('permissions', base64_encode($permission)),
                            'method' => 'DELETE'
                        ])
                    @endforeach
                </div>
            </div>

            <hr>

            <h4>ロール追加</h4>

            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>ロール名(日本語可)</th>
                        <th>Slug</th>
                        <th>パーミッション</th>
                        <th>操作</th>
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
                                    @include('parts.error-block', ['name' => 'new_role'])
                                </div>
                            </td>
                            <td>
                                <div class="form-group{{ $errors->has('new_slug') ? ' has-error' : '' }}">
                                    <input id="new_slug" type="text" class="form-control" name="new_slug" value="{{ old('new_slug') }}">
                                    @include('parts.error-block', ['name' => 'new_slug'])
                                </div>
                            </td>
                            <td>
                                @foreach ($permissions as $per)
                                    <div>
                                        <input type="checkbox" name="new_per_{{str_replace(".", "-", $per)}}"
                                            {{old("new_per_".str_replace(".", "-", $per))=="on" ? 'checked="true"' : ''}}
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
                        <th>ロール名(日本語可)</th>
                        <th>Slug</th>
                        <th>パーミッション</th>
                        <th colspan="2">操作</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(Sentinel::getRoleRepository()->all() as $role)
                        <tr>
                            <form class="col" role="form" method="POST" action="{{url('roles', $role->id)}}">
                                {{ csrf_field() }}
                                {{ method_field('PUT') }}
                                <td>
                                    <div class="form-group{{ $errors->has('role_'.$role->id.'_name') ? ' has-error' : '' }}">
                                        <input type="text"
                                            class="form-control"
                                            name="role_{{$role->id}}_name"
                                            id="role_{{$role->id}}_name"
                                            value="{{empty(old('role_'.$role->id.'_name')) ? $role->name : old('role_'.$role->id.'_name')}}">
                                        @include('parts.error-block', ['name' => 'role_'.$role->id.'_name'])
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group{{ $errors->has('role_'.$role->id.'_slug') ? ' has-error' : '' }}">
                                        <input type="text"
                                            class="form_control"
                                            name="role_{{$role->id}}_slug"
                                            id="role_{{$role->id}}_slug"
                                            value="{{empty(old('role_'.$role->id.'_slug')) ? $role->slug : old('role_'.$role->id.'_slug')}}">
                                        @include('parts.error-block', ['name' => 'role_'.$role->id.'_slug'])
                                    </div>
                                </td>
                                <td>
                                    @foreach ($permissions as $per)
                                        <div>
                                            <input type="checkbox"
                                                name="role_{{$role->id}}_per_{{str_replace(".", "-", $per)}}"
                                                @if (old("role_".$role->id."_per_".str_replace(".", "-", $per))=="on")
                                                    checked="true"
                                                @elseif ($role->hasAccess($per))
                                                    checked="true"
                                                @endif
                                            > {{$per}}
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#role_{{$role->id}}_update">
                                        <i class="fa fa-btn fa-refresh"></i> 変更
                                    </button>

                                    @include('parts.modal-no-form', [
                                        'id' => 'role_'.$role->id.'_update',
                                        'title' => 'ロールの更新',
                                        'body' => 'ロール['.$role->name.']の情報を更新しますか？',
                                    ])
                                </td>
                            </form>
                            <td>
                                <button type="submit" class="btn btn-danger" data-toggle="modal" data-target="#role-{{$role->id}}-delete">
                                    <i class="fa fa-btn fa-remove"></i> 削除
                                </button>

                                @include('parts.modal', [
                                    'id' => 'role-'.$role->id.'-delete',
                                    'title' => 'ロールの削除',
                                    'body' => 'ロール['.$role->name.']を削除しますか？',
                                    'action' => url('roles', $role->id),
                                    'method' => 'DELETE',
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>
</div>
@endsection
