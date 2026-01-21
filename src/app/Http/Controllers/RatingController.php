<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Rating;
use App\Http\Requests\RatingRequest;

class RatingController extends Controller
{
    /**
     * 評価を保存
     */
    public function store(RatingRequest $request, Order $order)
    {
        $user = Auth::user();

        // 購入者のみ評価可能
        if ($order->user_id !== $user->id) {
            return redirect()->back()
                ->with('error', '評価は購入者のみ可能です。');
        }

        // 取引が完了しているか確認
        if ($order->trade_status !== Order::TRADE_STATUS_COMPLETED) {
            return redirect()->back()
                ->with('error', '取引が完了していません。');
        }

        // 既に評価済みか確認
        if ($order->rating) {
            return redirect()->back()
                ->with('error', '既に評価済みです。');
        }

        // 評価される人（出品者）を取得
        $ratedUser = $order->item->user;

        // 評価を保存
        Rating::create([
            'order_id' => $order->id,
            'rater_id' => $user->id,
            'rated_id' => $ratedUser->id,
            'rating' => $request->rating,
        ]);

        return redirect()->route('items.index')
            ->with('success', '評価を投稿しました。');
    }
}
