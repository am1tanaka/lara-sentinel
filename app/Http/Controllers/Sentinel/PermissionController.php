<?php

namespace App\Http\Controllers\Sentinel;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sentinel\RoleController;

use Sentinel;
use Redirect;

class PermissionController extends Controller
{
    /**
     * コンストラクター
     * 処理に権限チェックのミドルウェアを設定。パーミッションの権限はロールに準じる
     */
    public function __construct() {
        $this->middleware('permission:role.create', [
            'only' => [
                'store'
            ]
        ]);
        $this->middleware('permission:role.delete', [
            'only' => [
                'destroy'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // バリデーション
        $this->validate($request, [
           // nameは必須で、255文字まで
           'new_permission' => 'required|max:255',
       ]);

       // 既存なら何もしない
       $nowper = RoleController::getPermissionList();

       if (in_array($request->new_permission, $nowper)) {
           // すでにあるので、エラーで返す
           return Redirect::back()->withInput()->withErrors(['new_permission' => trans('sentinel.same_permission')]);
       }

       // 作成実行
       foreach(Sentinel::getRoleRepository()->all() as $role) {
           $role->addPermission($request->new_permission, false)->save();
       }

       // 成功
       return Redirect::back()->with(['info' => trans('sentinel.permission_add_done').":".$request->new_permission]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($name)
    {
        $per = base64_decode($name);
        $permissions = RoleController::getPermissionList();
        if (!in_array($per, $permissions)) {
            return Redirect::back()->withErrors(['delete_permission' => trans('sentinel.invalid_permission')]);
        }

        // 削除
        foreach (Sentinel::getRoleRepository()->all() as $role) {
            $role->removePermission($per)->save();
        }

        return Redirect::back()->with(['info' => trans('sentinel.permission_delete_done').":".$per]);
    }
}
