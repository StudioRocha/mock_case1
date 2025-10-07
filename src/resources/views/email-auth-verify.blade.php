@extends('layouts.app') @push('styles')
<link rel="stylesheet" href="/css/email-auth.css" />
@endpush @section('title', 'メール認証') @section('content')
<div class="p-email-auth">
    <div class="p-email-auth__container">
        <h1 class="p-email-auth__title">メール認証</h1>

        <div class="p-email-auth__message">
            <p>メールに記載されている認証コードを入力してください。</p>
        </div>

        <form
            action="{{ route('email.verify') }}"
            method="post"
            class="p-email-auth__form"
        >
            @csrf

            <div class="p-email-auth__field">
                <label for="verification_code" class="p-email-auth__label"
                    >認証コード</label
                >
                <input
                    type="text"
                    id="verification_code"
                    name="verification_code"
                    class="p-email-auth__input @error('verification_code') p-email-auth__input--error @enderror"
                    value="{{ old('verification_code') }}"
                    placeholder="6桁の認証コードを入力"
                    maxlength="6"
                    required
                />
                @error('verification_code')
                <div class="p-email-auth__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="p-email-auth__actions">
                <button type="submit" class="p-email-auth__submit-btn">
                    認証する
                </button>

                <a
                    href="{{ route('email.guide') }}"
                    class="p-email-auth__back-link"
                >
                    戻る
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
