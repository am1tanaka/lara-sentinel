<?php

namespace App\Http\Middleware;

use Closure;
use Sentinel;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Sentinel::guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                // ajaxでのアクセスや、JSONの場合はテキストのみ
                return response('Unauthorized.', 401);
            } else {
                // ログインしていないのでログイン画面へ
                return redirect()->guest('login');
            }
        }

        // 認証しているので指定のルートへ
        return $next($request);
    }
}
