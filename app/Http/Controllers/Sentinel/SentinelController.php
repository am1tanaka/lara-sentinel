<?php

namespace App\Http\Controllers\Sentinel;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sentinel;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Mail;
use Activation;

class SentinelController extends Controller
{
    /**
     * ログイン後に表示するパス
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * アクティベーション
     */
    protected function activate(Request $request) {
        // ユーザーを取得する
        $user = Sentinel::findByCredentials(['email' => base64_decode($request->email)]);
        if (is_null($user)) {
            return redirect('login')->with(['myerror' => trans('sentinel.invalid_activation_params')]);
        }

        // アクティベーション済みだった場合、そのまま戻る
        if (Activation::completed($user)) {
            return redirect('login');
        }

        // アクティベーションを実行する
        if (!Activation::complete($user, $request->code)) {
            return redirect('login')->with(['myerror' => trans('sentinel.invalid_activation_params')]);
        }

        return redirect('login')->with(['info' => trans('sentinel.activation_done')]);
    }

    /**
     * ログイン
     */
    protected function login(Request $request) {
        // チェック
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required|between:6,255',
            'remember' => 'boolean',
        ]);

        // 認証処理
        try {
            $this->userInterface = Sentinel::authenticate([
                'email' => $request['email'],
                'password' => $request['password']
            ], $request['remember']);
        } catch (NotActivatedException $e) {
            return view('auth.login', [
                'info' => '登録したメールアドレスに、登録確認用のメールを送信してあります。メールを開いて、リンクをクリックしてから、再度ログインしてください。'
            ]);
        }

        if (!$this->userInterface) {
            // エラー
            return view('auth.login', [
                'info' => 'ログインに失敗しました。正しいメールアドレスとパスワードを入力してください。']
            );
        }

        return redirect($this->redirectTo);
    }

    /**
     * ユーザー登録
     */
     protected function register(Request $request)
     {
         // チェック
         $this->validate($request, [
            // nameは必須で、255文字まで
            'name' => 'required|max:255',
            // emailは必須で、emailの形式で、255文字までで、usersテーブル内でユニーク
            'email' => 'required|email|max:255|unique:users',
            // passwordは必須で、6文字以上255文字以下で、確認欄と一致する必要がある
            'password' => 'required|between:6,255|confirmed',
        ]);

        // 上記の認証に問題がなければ、ユーザー登録
        $credentials = [
            'first_name' => $request['name'],
            'email' => $request['email'],
            'password' => $request['password'],
        ];
        $user = Sentinel::register($credentials);

        // アクティベーションを作成する
        $activation = Activation::create($user);
        // メールで送信する
        Mail::send('sentinel.emails.activation', [
            'user' => $user,
            'code' => $activation->code,
        ], function($m) use ($user) {
            $m->from(config('app.activation_from'), config('app.appname'));
            $m->to($user->email, $user->name)->subject(trans('sentinel.activate_title'));
        });

        // メールを確認して、承認してからログインすることを表示するページへ
        return redirect('login')->with('info', trans('sentinel.after_register'));
     }
}
