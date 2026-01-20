<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use App\Models\Rating;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ユーザーを取得
        $tanaka = User::where('email', 'tanaka@example.com')->first(); // 田中太郎
        $sato = User::where('email', 'sato@example.com')->first(); // 佐藤花子
        
        if (!$tanaka || !$sato) {
            $this->command->error('ユーザーが見つかりません。先にUserSeederを実行してください。');
            return;
        }

        // 田中太郎が出品した商品を取得（最初の1つ）
        $tanakaItem = Item::where('user_id', $tanaka->id)
            ->first();

        if (!$tanakaItem) {
            $this->command->error('田中太郎が出品した商品が見つかりません。先にItemSeederを実行してください。');
            return;
        }

        // 佐藤花子が田中太郎の商品を購入したOrderを作成（取引中）
        $order = Order::firstOrCreate(
            [
                'user_id' => $sato->id,
                'item_id' => $tanakaItem->id,
            ],
            [
                'user_id' => $sato->id,
                'item_id' => $tanakaItem->id,
                'total_amount' => $tanakaItem->item_prices,
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'trade_status' => Order::TRADE_STATUS_TRADING,
                'payment_method' => 'credit_card',
                'shipping_address' => '東京都渋谷区神宮前1-1 テストアパート 201',
            ]
        );

        // 商品を売却済みにマーク
        $tanakaItem->update(['is_sold' => true]);

        // 佐藤花子が田中太郎を評価（3点）
        Rating::firstOrCreate(
            [
                'order_id' => $order->id,
            ],
            [
                'order_id' => $order->id,
                'rater_id' => $sato->id, // 評価する人（佐藤花子）
                'rated_id' => $tanaka->id, // 評価される人（田中太郎）
                'rating' => 3, // 3点評価
            ]
        );

        $this->command->info("田中太郎への評価データを作成しました（評価者: 佐藤花子、評価: 3点）。");
    }
}
