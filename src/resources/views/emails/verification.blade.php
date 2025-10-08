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

        <p>以下のボタンをクリックしてメール認証を完了してください。</p>

        <div style="text-align: center; margin: 30px 0">
            <a
                href="{{ $verificationUrl }}"
                target="_self"
                style="
                    background-color: #007bff;
                    color: white;
                    padding: 15px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 16px;
                    font-weight: bold;
                    display: inline-block;
                "
            >
                🚀 メール認証を完了する
            </a>
        </div>

        <p>
            ボタンがクリックできない場合は、以下のリンクをコピーしてブラウザに貼り付けてください：
        </p>

        <div
            style="
                background-color: #f5f5f5;
                padding: 15px;
                word-break: break-all;
                font-family: monospace;
                margin: 20px 0;
            "
        >
            <a href="{{ $verificationUrl }}" target="_self">{{
                $verificationUrl
            }}</a>
        </div>

        <p>認証リンクの有効期限は24時間です。</p>

        <p>
            もしこのメールに心当たりがない場合は、このメールを無視してください。
        </p>

        <hr />
        <p style="font-size: 12px; color: #666">
            このメールは自動送信されています。返信はできません。
        </p>
    </body>
</html>
