<?php

namespace App\Http\Controllers\Sentinel;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sentinel;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Mail;
use Activation;
use Reminder;

class SentinelController extends Controller
{
    /**
     * ログイン後に表示するパス
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * ログアウト処理
     */
    protected function logout(Request $request) {
        Sentinel::logout();

        return redirect($this->redirectTo);
    }

    /**
     * パスワードのリセットを実行する
     */
    protected function resetPassword(Request $request) {
        // データ長を調整しておく
        $email = substr(base64_decode($request->email), 0, 255);
        $code = substr($request->code, 0, 64);
        $passwd = substr(base64_decode($request->password), 0,255);

        $user = Sentinel::findByCredentials(['email' => $email]);
        if (is_null($user)) {
            // 不正なアクセス
            // TODO: throttleを呼び出す
            // 正常なメッセージを返す
            return redirect('login')->with('info', trans('sentinel.password_reset_done'));
        }

        // リマインダーを完了させる
        if (Reminder::complete($user, $code, $passwd)) {
            // 成功
            return redirect('login')->with('info', trans('sentinel.password_reset_done'));
        }

        // 失敗
        return redirect('login')->with('info', trans('sentinel.password_reset_failed'));
    }

    /**
     * パスワードを再設定するための処理
     * ユーザーが有効で、パスワードが条件に合致していたら、SentinelのReminderを使って処理する
     */
    protected function sendResetPassword(Request $request) {
        // 古いリマインダーコードを削除
        Reminder::removeExpired();

        // チェック
        $this->validate($request, [
           // emailは必須で、emailの形式で、255文字まで
           // メールアドレスの有無は、不正を避けるためにチェックしない
           'email' => 'required|email|max:255',
           // passwordは必須で、6文字以上255文字以下で、確認欄と一致する必要がある
           'password' => 'required|between:6,255|confirmed',
       ]);

       // ユーザーを検索
       $user = Sentinel::findByCredentials(['email'=>$request->email]);
       if (is_null($user)) {
           // TODO:Throttleを呼び出す
           // ユーザーがいなければ成功したような感じにしてログイン画面へ
           return redirect('login')->with(['info'=>trans('sentinel.password_reset_sent')]);
       }

       // リマインダーが作成済みなら、それを再送信する
       $code = "";
       $exists = Reminder::exists($user);
       if ($exists) {
           // すでに設定されているので、リマインダーコードを設定
           $code = $exists->code;
       }
       else {
           // 新規にリマインダーを作成して、コードを返す
           $reminder = Reminder::create($user);
           $code = $reminder->code;
       }

       // メールを送信
       Mail::send('sentinel.emails.reminder', [
           'user' => $user,
           'code' => $code,
           'password' => $request->password,
       ], function($m) use ($user) {
           $m->from(config('app.activation_from'), config('app.appname'));
           $m->to($user->email, $user->name)->subject(trans('sentinel.reminder_title'));
       });

       // 成功したら、login画面へ移動
       return redirect('login')->with(['info'=>trans('sentinel.password_reset_sent')]);
    }

    /**
     * 指定のメールアドレスのアクティベーションコードを再送する
     */
    protected function resendActivationCode(Request $request) {
        // 古いアクティベーションコードを削除
        Activation::removeExpired();

        // ユーザーを確認
        $user = Sentinel::findByCredentials(['email' => base64_decode($request->email)]);
        if (is_null($user)) {
            return redirect('login')->with(['myerror' => trans('sentinel.invalid_activation_params')]);
        }

        // すでにアクティベート済みの時は、何もせずにログインへ
        if (Activation::completed($user)) {
            return redirect('login')->with(['info' => trans('sentinel.activation_done')]);
        }

        // アクティベーションの状況を確認
        $exists = Activation::exists($user);
        if (!$exists) {
            // 存在しない場合は、再生成して、そのコードを送信する
            $activation = Activation::create($user);
        }
        else {
            // 現在のコードを
            $activation = $exists;
        }

        // メールで送信する
        $this->sendActivationCode($user, $activation->code);
        // メールを確認して、承認してからログインすることを表示するページへ
        return redirect('login')->with('info', trans('sentinel.after_register'));
    }

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
        // バリデーション
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
        } catch (NotActivatedException $notactivated) {
            return view('auth.login', [
                'myerror' => trans('sentinel.not_activation'),
                'resend_code' => $request['email'],
            ]);
        } catch (ThrottlingException $throttling) {
            return view('auth.login', ['myerror' => trans('sentinel.login_throttling')."[あと".$throttling->getDelay()."秒]"]);
        }

        if (!$this->userInterface) {
            // エラー
            return view('auth.login', ['myerror' => trans('sentinel.login_failed')]);
        }

        // ロールのチェック
        $this->checkAdminMailRoles($request);

        return redirect($this->redirectTo);
    }

    /**
     * 管理者のメールアドレスチェックをして、管理者の時で、ロールがない時は、
     * ロールを整える
     * @return bool true=作成した / false=何もしない
     */
    private function checkAdminMailRoles(Request $request) {
        if (strcmp($request['email'], config('app.admin_email')) !== 0) {
            return false;
        }

        // ロールがあるかを確認する
        if (!Sentinel::findRoleBySlug('admin')) {
            // ロールがないので、作成する
            $defs = config('roles.default_roles');
            foreach($defs as $k => $v)
            {
                // ロールがなければ作成
                $role = Sentinel::findRoleByName($defs[$k]['name']);
                if (!$role) {
                    Sentinel::getRoleRepository()->createModel()->create($defs[$k]);
                }
                else {
                    // ロールがある場合は、更新
                    $role->permissions = $defs[$k]['permissions'];
                    $role->save();
                }
            }
        }

        // 管理者のメール。管理者ロールが割り当てられている場合は何もしない
        if (Sentinel::inRole('admin')) {
            return false;
        }

        // ユーザーにadminロールを設定する
        $role = Sentinel::findRoleBySlug('admin');
        $role->users()->attach($this->userInterface);
        return true;
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
        $this->sendActivationCode($user, $activation->code);

        // メールを確認して、承認してからログインすることを表示するページへ
        return redirect('login')->with('info', trans('sentinel.after_register'));
    }

    /**
     * 指定のユーザーに、指定のコードをメールで送信する
     * @param Cartalyst\Sentinel\Users\UserInterface $user ユーザー
     * @param string アクティベーションコード
     */
    private function sendActivationCode($user, $code) {
        Mail::send('sentinel.emails.activation', [
            'user' => $user,
            'code' => $code,
        ], function($m) use ($user) {
            $m->from(config('app.activation_from'), config('app.appname'));
            $m->to($user->email, $user->name)->subject(trans('sentinel.activate_title'));
        });
    }
}
