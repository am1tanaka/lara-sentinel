<?php
namespace App\Http\Controllers\Sentinel;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Redirect;
use Sentinel;

class RoleController extends Controller
{
     /**
      * コンストラクター
      * 処理に権限チェックのミドルウェアを設定
      */
     public function __construct() {
         $this->middleware('permission:role.view', [
             'only' => [
                 'index'
             ]
         ]);
         $this->middleware('permission:role.update', [
             'only' => [
                 'update'
             ]
         ]);
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sentinel.roles', ['permissions' => self::getPermissionList()]);
    }

    /**
     * 全ロールに設定されているロールのリストを作成
     */
    public static function getPermissionList() {
        $permissions = [];
        foreach(Sentinel::getRoleRepository()->all() as $role) {
            foreach($role->permissions as $k => $v) {
                if (!in_array($k, $permissions)) {
                    $permissions[] = $k;
                }
            }
        }
        return $permissions;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'new_role' => 'required|max:255',
            'new_slug' => 'required|max:255',
        ]);

        // パーミッションリストの作成
        $permissions = [];
        $pers = self::getPermissionList();
        foreach($pers as $per) {
            $permissions[$per] = $request['new_per_'.str_replace(".", "-", $per)] == "on";
        }

        $role = Sentinel::getRoleRepository()->createModel()->create([
            'name' => $request->new_role,
            'slug' => $request->new_slug,
            'permissions' => $permissions
        ]);

        return Redirect::back()->with(['info' => trans('sentinel.role_create_done')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $idx = 'role_'.$id.'_';

        // バリデーションを実施
        $this->validate($request, [
            $idx.'name' => 'required|max:255',
            $idx.'slug' => 'required|max:255',
        ]);

        // 修正をチェック
        $updates = [];
        $updates[] = trans('sentinel.role_update_done');

        // 現ロールを取得
        $role = Sentinel::findRoleById($id);
        if ($role == null) {
            return Redirect::back()->withInput()->withErrors(['invalid_role' => trans('sentinel.invalid_role')]);
        }

        // 名前チェック
        if ($role->name !== $request[$idx."name"]) {
            $updates[] = "ロール名： ".$role->name." > ".$request[$idx."name"];
            $role->name = $request[$idx."name"];
        }
        // slugチェック
        if ($role->slug !== $request[$idx."slug"]) {
            $updates[] = "Slug： ".$role->slug." > ".$request[$idx."slug"];
            $role->slug = $request[$idx."slug"];
        }

        // パーミッションの設定
        $permissions = self::getPermissionList();
        foreach($permissions as $per) {
            if ($role->hasAccess($per) && (!$request[$idx."per_".str_replace(".", "-", $per)])) {
                $updates[] = $per." > off";
                $role->updatePermission($per, false);
            }
            else if (!$role->hasAccess($per) && ($request[$idx."per_".str_replace(".","-",$per)])) {
                $updates[] = $per." > on";
                $role->updatePermission($per, true);
            }
        }

        // 更新
        if (count($updates) > 1) {
            $role->save();
            return Redirect::back()->with(['info' => $updates]);
        }

        // 更新なし
        return Redirect::back()->with(['info' => trans('sentinel.no_changed')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 削除実行
        $role = Sentinel::findRoleById($id);
        if ($role === null) {
            return Redirect::back()->withInput()->withErrors(['role-'.$id.'_delete', trans('sentinel.invalid_role')]);
        }
        $name = $role->name;

        // 削除実行
        $role->delete();

        return Redirect::back()->with(['info' => trans('sentinel.role_delete_done')." : ".$name]);
    }
}
