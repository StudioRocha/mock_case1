@extends('layouts.app') @section('title', 'マイページ') @push('styles')
<link rel="stylesheet" href="/css/mypage.css" />
@endpush @section('content')
<div class="p-mypage">
    <div class="p-mypage__header">
        <div class="p-avatar__thumb">
            <div
                class="p-avatar__bg"
                style="@if(optional($user->profile)->avatar_paths) background-image:url('{{ asset('storage/'. $user->profile->avatar_paths) }}') @endif"
            ></div>
        </div>
        <div class="p-mypage__user-info">
            <div class="p-mypage__name">
                {{ optional($user->profile)->usernames ?? $user->name }}
            </div>
            @if($averageRating)
            <div class="p-mypage__rating">
                @for($i = 1; $i <= 5; $i++)
                    <span class="p-mypage__star {{ $i <= $averageRating ? 'p-mypage__star--filled' : '' }}">★</span>
                @endfor
            </div>
            @endif
        </div>
        <a href="/mypage/profile" class="c-button c-button--outline-danger"
            >プロフィールを編集</a
        >
    </div>

    <div class="p-tabs p-mypage__tabs">
        <a
            href="{{ url('/mypage?page=sell') }}"
            class="p-tabs__tab {{
                $activeTab === 'sell' ? 'p-tabs__tab--active' : ''
            }}"
            >出品した商品</a
        >
        <a
            href="{{ url('/mypage?page=buy') }}"
            class="p-tabs__tab {{
                $activeTab === 'buy' ? 'p-tabs__tab--active' : ''
            }}"
            >購入した商品</a
        >
        <a
            href="{{ url('/mypage?page=trading') }}"
            class="p-tabs__tab {{
                $activeTab === 'trading' ? 'p-tabs__tab--active' : ''
            }}"
            >取引中の商品
            @if($unreadMessageCount > 0)
            <span class="p-tabs__badge">{{ $unreadMessageCount }}</span>
            @endif
            </a
        >
    </div>

    @if($activeTab==='sell')
    <div class="p-grid">
        @forelse($listedItems as $item)
        <a class="c-card" href="/item/{{ $item->id }}">
            <div class="c-card__thumb">
                @if($item->is_sold)
                <span class="c-card__badge">Sold</span>
                @endif @if($item->item_image_paths)
                <img
                    class="c-card__img"
                    src="{{ asset(Str::startsWith($item->item_image_paths, 'http') ? $item->item_image_paths : (Str::startsWith($item->item_image_paths, 'images/') ? $item->item_image_paths : 'storage/'.$item->item_image_paths)) }}"
                    alt="{{ $item->item_names }}"
                />
                @else
                <div class="c-card__img c-card__img--placeholder">商品画像</div>
                @endif
            </div>
            <div class="c-card__name">{{ $item->item_names }}</div>
        </a>
        @empty
        <div class="p-empty">出品した商品はまだありません。</div>
        @endforelse
    </div>
    @if($listedItems->count())
    <div class="p-pagination">{{ $listedItems->links() }}</div>
    @endif @elseif($activeTab === 'buy')
    <div class="p-grid">
        @forelse($purchasedItems as $item)
        <a class="c-card" href="/item/{{ $item->id }}">
            <div class="c-card__thumb">
                @if($item->is_sold)
                <span class="c-card__badge">Sold</span>
                @endif @if($item->item_image_paths)
                <img
                    class="c-card__img"
                    src="{{ asset(Str::startsWith($item->item_image_paths, 'http') ? $item->item_image_paths : (Str::startsWith($item->item_image_paths, 'images/') ? $item->item_image_paths : 'storage/'.$item->item_image_paths)) }}"
                    alt="{{ $item->item_names }}"
                />
                @else
                <div class="c-card__img c-card__img--placeholder">商品画像</div>
                @endif
            </div>
            <div class="c-card__name">{{ $item->item_names }}</div>
        </a>
        @empty
        <div class="p-empty">購入した商品はありません。</div>
        @endforelse
    </div>
    @elseif($activeTab === 'trading')
    <div class="p-grid">
        @forelse($tradingOrders as $order)
        <a class="c-card" href="/chat/{{ $order->item_id }}">
            <div class="c-card__thumb">
                @php
                    $item = $order->item;
                    $itemUnreadCount = $itemUnreadCounts[$item->id] ?? 0;
                @endphp
                @if($itemUnreadCount > 0)
                <span class="c-card__notification">{{ $itemUnreadCount }}</span>
                @endif
                @if($item->item_image_paths)
                <img
                    class="c-card__img"
                    src="{{ asset(Str::startsWith($item->item_image_paths, 'http') ? $item->item_image_paths : (Str::startsWith($item->item_image_paths, 'images/') ? $item->item_image_paths : 'storage/'.$item->item_image_paths)) }}"
                    alt="{{ $item->item_names }}"
                />
                @else
                <div class="c-card__img c-card__img--placeholder">商品画像</div>
                @endif
            </div>
            <div class="c-card__name">{{ $item->item_names }}</div>
        </a>
        @empty
        <div class="p-empty">取引中の商品はありません。</div>
        @endforelse
    </div>
    @endif
</div>
@endsection
