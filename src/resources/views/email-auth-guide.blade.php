@extends('layouts.app') @push('styles')
<link rel="stylesheet" href="/css/email-auth.css" />
<style>
    /* Hide header elements except logo for email auth guide page */
    body.email-auth-page .p-header__center,
    body.email-auth-page .p-header__right {
        display: none;
    }
</style>
@endpush @section('title', 'メール認証') @section('content')
<div class="p-email-auth">
    <div class="p-email-auth__container">
        <div class="p-email-auth__message">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
        </div>

        <div class="p-email-auth__actions">
            @if(config('app.auto_verify_enabled', false))
            <a
                href="{{ route('email.auto-verify') }}"
                class="p-email-auth__auto-btn"
            >
                認証はこちらから
            </a>
            @endif

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
