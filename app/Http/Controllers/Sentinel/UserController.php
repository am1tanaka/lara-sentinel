<?php

namespace App\Http\Controllers\Sentinel;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Mail;
use Sentinel;
use Redirect;

class UserController extends Controller
{
    /**
     * コンストラクター
     * 処理に権限チェックのミドルウェアを設定
     */
    public function __construct() {
        $this->middleware('permission:user.view', [
            'only' => [
                'index'
            ]
        ]);
        $this->middleware('permission:user.create', [
            'only' => [
                'store'
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     * user.view権限が必要
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 自分の最初のロールを取得
        $user = Sentinel::check();

        return view('sentinel.users',
        [
            'role' => $user->roles[0]->slug,
            'roles' => Sentinel::getRoleRepository()->all(),
            'users' => Sentinel::getUserRepository()->all()
        ]);
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
     * あらかじめ、user.create 権限があることをチェック
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // バリデーション
        $this->validate($request, [
           // nameは必須で、255文字まで
           'name' => 'required|max:255',
           // emailは必須で、emailの形式で、255文字までで、usersテーブル内でユニーク
           'email' => 'required|email|max:255|unique:users',
           // passwordは必須で、6文字以上255文字以下で、確認欄と一致する必要がある
           'password' => 'between:6,255|confirmed',
       ]);

       // パスワードが無指定の場合は、自動生成する
       $pass = $request->password;
       if (empty($pass)) {
           $pass = str_random(config('app.password_generate_length'));
       }

       // DBに登録
       $credentials = [
           'first_name' => $request['name'],
           'email' => $request['email'],
           'password' => $pass,
       ];
       if (!config('app.ignore_db_access')) {
           $user = Sentinel::registerAndActivate($credentials);
       }

       // ロールを設定する
       $roles = [];
       $rolenames = "";
       $allroles = Sentinel::getRoleRepository()->all();
       foreach($allroles as $role) {
            if ($request['user_new_role_'.$role->id] == "on") {
                if (!config('app.ignore_db_access')) {
                    $role->users()->attach($user);
                }
                if (mb_strlen($rolenames) > 0) {
                    $rolenames .= ", ";
                }
                $rolenames .= $role->name;
            }
       }

       // メールで送信する
       $this->sendMail([
           'toemail' => config('app.admin_email'),
           'toname' => config('app.admin_name'),
           'subject' => trans('sentinel.user_regist_subject'),
           'blade' => 'sentinel.emails.user_regist_done',
           'args' => [
               'name' => $request['name'],
               'email' => $request['email'],
               'password' => $pass,
               'roles' => $rolenames,
           ]
       ]);

       // メールを確認して、承認してからログインすることを表示するページへ
       return redirect('users')->with('info', trans('sentinel.user_regist_done'));
    }

    /**
     * 指定の内容でメールを送信する
     * @param array $params 送信データの連想配列
     * 'toemail' 宛先メールアドレス
     * 'toname' 宛先名
     * 'subject' メール件名
     * 'blade' 本文のテンプレート名
     * 'args' bladeに渡す連想配列
     */
    public function sendMail($params) {
        Mail::send($params['blade'], ['args' => $params['args']], function($m) use ($params) {
            $m->from(config('app.activation_from'), config('app.appname'));
            $m->to($params['toemail'], $params['toname'])->subject($params['subject']);
        });
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
        //
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
