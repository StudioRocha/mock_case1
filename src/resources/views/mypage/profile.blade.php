@extends('layouts.app') @section('title', 'プロフィール設定')
@section('content')
<div class="p-auth">
    <h1 class="p-auth__title">プロフィール設定</h1>
    <form
        action="{{ url('/mypage/profile') }}"
        method="post"
        enctype="multipart/form-data"
        class="p-form"
        novalidate
    >
        @csrf @method('put')
        <div class="p-form__group">
            <label class="p-form__label">プロフィール画像</label>
            <input
                class="p-form__control"
                type="file"
                name="avatar"
                accept="image/jpeg,image/png"
            />
            <div class="p-form__hint">拡張子: jpg/png</div>
            @if(optional($profile)->avatar_paths)
            <div>
                <img
                    src="{{ asset('storage/'. $profile->avatar_paths) }}"
                    alt="avatar"
                    style="max-width: 120px"
                />
            </div>
            @endif @error('avatar')
            <div class="p-form__error">{{ $message }}</div>
            @enderror
        </div>
        <div class="p-form__group">
            <label class="p-form__label" for="username">ユーザー名</label>
            <input
                class="p-form__control"
                type="text"
                id="username"
                name="username"
                value="{{ old('username', optional($profile)->usernames) }}"
                placeholder="例）山田太郎（20文字以内）"
            />
            @error('username')
            <div class="p-form__error">{{ $message }}</div>
            @enderror
        </div>
        <div class="p-form__group">
            <label class="p-form__label" for="postal_code">郵便番号</label>
            <input
                class="p-form__control"
                type="text"
                id="postal_code"
                name="postal_code"
                value="{{ old('postal_code', optional($profile)->postal_codes) }}"
                placeholder="123-4567"
            />
            @error('postal_code')
            <div class="p-form__error">{{ $message }}</div>
            @enderror
        </div>
        <div class="p-form__group">
            <label class="p-form__label" for="address">住所</label>
            <input
                class="p-form__control"
                type="text"
                id="address"
                name="address"
                value="{{ old('address', optional($profile)->addresses) }}"
                placeholder="例）東京都千代田区丸の内1-1-1"
            />
            @error('address')
            <div class="p-form__error">{{ $message }}</div>
            @enderror
        </div>
        <div class="p-form__group">
            <label class="p-form__label" for="building_name">建物名</label>
            <input
                class="p-form__control"
                type="text"
                id="building_name"
                name="building_name"
                value="{{ old('building_name', optional($profile)->building_names) }}"
            />
            @error('building_name')
            <div class="p-form__error">{{ $message }}</div>
            @enderror
        </div>
        <button class="c-button c-button--primary p-form__submit" type="submit">
            更新する
        </button>
    </form>
    @endsection
</div>
