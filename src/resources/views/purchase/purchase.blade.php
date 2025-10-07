@extends('layouts.app') @push('styles')
<link rel="stylesheet" href="/css/purchase.css" />
@endpush @section('title', '商品購入') @section('content')
<div class="p-purchase">
    <div class="p-purchase__main">
        <div class="p-purchase__left">
            <!-- 商品情報 -->
            <div class="p-purchase__product">
                <div class="p-purchase__product-image">
                    <img
                        class="p-purchase__img"
                        src="{{ asset(Str::startsWith($item->item_image_paths,'http') ? $item->item_image_paths : 'storage/'.$item->item_image_paths) }}"
                        alt="{{ $item->item_names }}"
                    />
                </div>
                <div class="p-purchase__product-info">
                    <h1 class="p-purchase__product-name">
                        {{ $item->item_names }}
                    </h1>
                    <div class="p-purchase__product-price">
                        ¥ {{ number_format($item->item_prices) }}
                    </div>
                </div>
            </div>

            <hr class="p-purchase__divider" />

            <!-- 支払い方法 -->
            <div class="p-purchase__section">
                <h2 class="p-purchase__section-title">支払い方法</h2>
                <select
                    name="payment_method"
                    class="p-purchase__select"
                    form="purchase-form"
                >
                    <option value="">選択してください</option>
                    <option value="convenience_store">コンビニ支払い</option>
                    <option value="credit_card">カード支払い</option>
                </select>
            </div>

            <hr class="p-purchase__divider" />

            <!-- 配送先 -->
            <div class="p-purchase__section">
                <div class="p-purchase__section-header">
                    <h2 class="p-purchase__section-title">配送先</h2>
                    <a
                        href="{{ route('address.change', $item) }}"
                        class="p-purchase__change-link"
                        >変更する</a
                    >
                </div>
                <div class="p-purchase__address">
                    {!! nl2br(e($currentAddress)) !!}
                </div>
            </div>

            <hr class="p-purchase__divider" />
        </div>

        <div class="p-purchase__right">
            <!-- 注文サマリー -->
            <div class="p-purchase__summary">
                <div class="p-purchase__summary-row">
                    <span class="p-purchase__summary-label">商品代金</span>
                    <span class="p-purchase__summary-value"
                        >¥ {{ number_format($item->item_prices) }}</span
                    >
                </div>
                <div class="p-purchase__summary-row">
                    <span class="p-purchase__summary-label">支払い方法</span>
                    <span
                        class="p-purchase__summary-value"
                        id="payment-method-display"
                        >-</span
                    >
                </div>
            </div>

            <!-- 購入ボタン -->
            <form
                id="purchase-form"
                action="{{ route('stripe.checkout', $item) }}"
                method="post"
            >
                @csrf
                <input
                    type="hidden"
                    name="shipping_address"
                    value="{{ $currentAddress }}"
                />
                <button type="submit" class="p-purchase__buy-btn">
                    購入する
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const paymentSelect = document.querySelector(
            'select[name="payment_method"]'
        );
        const paymentDisplay = document.getElementById(
            "payment-method-display"
        );

        paymentSelect.addEventListener("change", function () {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                paymentDisplay.textContent = selectedOption.textContent;
            } else {
                paymentDisplay.textContent = "-";
            }
        });
    });
</script>
@endsection
