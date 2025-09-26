<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('title', 'COACHTECH Flea')</title>
        <link rel="stylesheet" href="/css/sanitize.css" />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="/css/common.css" />
    </head>
    <body class="l-body">
        <header class="l-header">
            <div class="l-header__inner">
                <div class="p-header">
                    <div class="p-header__left">
                        <a href="/" class="p-header__logo">
                            <img
                                src="{{ asset('images/logo.svg') }}"
                                alt="COACHTECH"
                                class="p-header__logo-img"
                            />
                        </a>
                    </div>
                    <div class="p-header__center">
                        <form
                            action="{{ route('items.index') }}"
                            method="get"
                            class="p-header__search c-search"
                        >
                            <input
                                type="hidden"
                                name="tab"
                                value="{{ request('tab', 'recommend') }}"
                            />
                            <div class="input-group">
                                <input
                                    class="form-control c-search__input"
                                    type="text"
                                    name="q"
                                    value="{{ old('q', $keyword ?? '') }}"
                                    placeholder="なにをお探しですか？"
                                />
                                <button
                                    class="btn btn-dark c-search__button"
                                    type="submit"
                                >
                                    検索
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="p-header__right">
                        <nav class="p-header__nav c-nav">
                            @guest
                            <a class="c-nav__link" href="/login">ログイン</a>
                            <a class="c-nav__link" href="/mypage">マイページ</a>
                            @endguest @auth
                            <a class="c-nav__link" href="/mypage">マイページ</a>
                            <form
                                method="POST"
                                action="/logout"
                                class="d-inline"
                            >
                                @csrf
                                <button
                                    type="submit"
                                    class="btn btn-link c-nav__link p-0 align-baseline"
                                >
                                    ログアウト
                                </button>
                            </form>
                            @endauth
                            <a
                                class="c-button c-button--primary btn btn-dark"
                                href="/sell"
                                >出品</a
                            >
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <main class="l-main">
            <div class="l-container">@yield('content')</div>
        </main>
    </body>
</html>
