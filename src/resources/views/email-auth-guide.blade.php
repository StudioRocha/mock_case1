@extends('layouts.app') @push('styles')
<link rel="stylesheet" href="/css/email-auth.css" />
@endpush @section('title', 'メール認証') @section('content')
<div class="p-email-auth">
    <div class="p-email-auth__container">
        <div class="p-email-auth__message">
            <p>
                登録していただいたメールアドレスに認証メールを送付しました。メール認証を完了してください。
            </p>
        </div>

        <div class="p-email-auth__actions">
            <a
                href="{{ route('email.verify') }}"
                class="p-email-auth__verify-btn"
            >
                認証はこちらから
            </a>

            <a
                href="{{ route('email.resend') }}"
                class="p-email-auth__resend-link"
            >
                認証メールを再送する
            </a>
        </div>
    </div>
</div>
@endsection
