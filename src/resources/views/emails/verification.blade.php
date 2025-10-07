<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>メール認証コード</title>
    </head>
    <body>
        <h2>メール認証コード</h2>

        <p>{{ $user->name }} 様</p>

        <p>ご登録いただき、ありがとうございます。</p>

        <p>以下の認証コードを入力してメール認証を完了してください。</p>

        <div
            style="
                background-color: #f5f5f5;
                padding: 20px;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                letter-spacing: 5px;
                margin: 20px 0;
            "
        >
            {{ $verificationCode }}
        </div>

        <p>この認証コードは6桁の数字です。</p>

        <p>認証コードの有効期限は24時間です。</p>

        <p>
            もしこのメールに心当たりがない場合は、このメールを無視してください。
        </p>

        <hr />
        <p style="font-size: 12px; color: #666">
            このメールは自動送信されています。返信はできません。
        </p>
    </body>
</html>
