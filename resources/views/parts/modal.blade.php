{{-- 以下のように呼び出しボタンを用意する
<button type="button" class="btn" data-toggle="modal" data-target="#ターゲット">
    ボタン名
</button>

id 呼び出すID
title モーダルに表示するタイトル
body モーダルの本文
action 実行時のURL
method 実行時のメソッド
--}}
<div class="modal fade" id="{{$id}}" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">{{$title}}</h4>
      </div>
      <div class="modal-body">
          {{$body}}
      </div>
      <div class="modal-footer">
          <form action="{{$action}}" method="POST">
              {{ csrf_field() }}
              {{ method_field($method) }}

              <button type="submit" class="btn btn-primary">
                  <i class="fa fa-btn fa-check"></i>  はい
              </button>
              <button type="button" class="btn btn-default" data-dismiss="modal">
                  <i class="fa fa-btn fa-close"></i>いいえ</button>
          </form>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
