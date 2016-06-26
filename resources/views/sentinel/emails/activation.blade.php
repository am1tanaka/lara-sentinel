{{ $user->first_name }}様<br>
<br>
[{{ config('app.appname') }}]へのユーザー登録を仮受付いたしました。<br>
以下のリンクをクリックして、登録を確定させてください。<br>
<br>
<a href="{{ $link = url('activate', [base64_encode($user->email), $code])}}">{{ $link }}</a><br>
<br>
--------<br>
[{{config('app.appname')}}]システムメール<br>
＊本メールは登録専用のものです。返信には使えません。<br>
<br>
