{{ $user->first_name }}様<br>
<br>
[{{ config('app.appname') }}]のパスワードを再設定するには、以下のリンクをクリックしてください。<br>
<br>
<a href="{{ $link = url('password/reset', [base64_encode($user->email), $code, base64_encode($password)])}}">{{ $link }}</a><br>
<br>
--------<br>
[{{config('app.appname')}}]システムメール<br>
＊本メールは登録専用のものです。返信には使えません。<br>
<br>
