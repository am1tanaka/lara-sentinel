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

       if (array_search($request->new_permission, $nowper) !== false) {
           // すでにあるので、エラーで返す
           return Redirect::back()->withInput()->withErrors(['new_permission' => trans('sentinel.same_permission')]);
       }

       // 作成実行
       foreach(Sentinel::getRoleRepository()->all() as $role) {
           $role->addPermission($request->new_permission, false);
           $role->save();
       }

       // 成功
       return Redirect::back()->withInput()->with(['info' => trans('sentinel.permission_add_done')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
