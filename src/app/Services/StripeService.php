<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Str;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret_key'));
    }

    /**
     * 決済セッションを作成
     */
    public function createCheckoutSession($item, $user, $paymentMethod, $shippingAddress)
    {
        try {
            $sessionData = [
                'payment_method_types' => [$paymentMethod === 'convenience_store' ? 'konbini' : 'card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->item_names,
                            'images' => [asset(Str::startsWith($item->item_image_paths,'http') ? $item->item_image_paths : 'storage/'.$item->item_image_paths)],
                        ],
                        'unit_amount' => $item->item_prices,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('stripe.success', ['item' => $item->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('items.show', $item),
                'metadata' => [
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'shipping_address' => $shippingAddress,
                ],
            ];

            // コンビニ決済の場合は顧客情報と支払期限を追加
            if ($paymentMethod === 'convenience_store') {
                $sessionData['customer_email'] = $user->email;
                // 支払期限を設定（デフォルト24時間、環境変数で変更可能）
                $expiryHours = config('stripe.konbini_expiry_hours', 24);
                $sessionData['expires_at'] = time() + ($expiryHours * 60 * 60);
            }

            $session = Session::create($sessionData);

            return $session;
        } catch (ApiErrorException $e) {
            throw new \Exception('Stripe決済セッションの作成に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * セッションIDからセッション情報を取得
     */
    public function retrieveSession($sessionId)
    {
        try {
            return Session::retrieve($sessionId);
        } catch (ApiErrorException $e) {
            throw new \Exception('Stripeセッションの取得に失敗しました: ' . $e->getMessage());
        }
    }
}
