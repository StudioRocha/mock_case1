<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>メール認証を完了してください</title>
    </head>
    <body>
        <h2>メール認証を完了してください</h2>

        <p>{{ $user->name }} 様</p>

        <p>ご登録いただき、ありがとうございます。</p>

        <p>以下の認証コードを入力してメール認証を完了してください：</p>

        <div style="text-align: center; margin: 30px 0">
            <div
                style="
                    background-color: #f5f5f5;
                    padding: 20px;
                    font-size: 32px;
                    font-weight: bold;
                    letter-spacing: 8px;
                    border: 2px solid #007bff;
                    border-radius: 8px;
                    display: inline-block;
                    font-family: monospace;
                "
            >
                {{ $verificationCode }}
            </div>
        </div>

        <p>認証コードの有効期限は24時間です。</p>

        <p>認証コード入力画面は以下のURLからアクセスできます：</p>

        <div
            style="
                background-color: #f5f5f5;
                padding: 15px;
                word-break: break-all;
                font-family: monospace;
                margin: 20px 0;
            "
        >
            <a href="{{ route('email.verify.code') }}">{{
                route("email.verify.code")
            }}</a>
        </div>

        <p>
            もしこのメールに心当たりがない場合は、このメールを無視してください。
        </p>

        <hr />
        <p style="font-size: 12px; color: #666">
            このメールは自動送信されています。返信はできません。
        </p>
    </body>
</html>
