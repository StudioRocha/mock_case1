<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Order;
use App\Models\Message;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile'); // プロフィール情報を事前読み込み
        $activeTab = $request->query('page', 'sell');

        $listedItems = Item::query()
            ->where('user_id', $user->id)
            ->with(['user', 'categories'])
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $purchasedItems = Order::query()
            ->where('user_id', $user->id)
            ->with(['item.user', 'item.categories'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function($order){
                return $order->item;
            })
            ->filter();

        // 取引中の商品を取得（出品者または購入者として）
        $tradingOrders = Order::query()
            ->where(function($query) use ($user) {
                // 購入者として
                $query->where('user_id', $user->id)
                      // または出品者として
                      ->orWhereHas('item', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->where('trade_status', Order::TRADE_STATUS_TRADING)
            ->with(['item.user', 'item.categories'])
            ->get()
            ->sortByDesc(function($order) {
                // 最新メッセージの作成日時で並び替え
                $latestMessage = Message::where('item_id', $order->item_id)
                    ->orderByDesc('created_at')
                    ->first();
                return $latestMessage ? $latestMessage->created_at : $order->created_at;
            })
            ->values();

        // 各商品の未読メッセージ数を計算
        $itemUnreadCounts = [];
        $unreadMessageCount = 0;
        
        foreach ($tradingOrders as $order) {
            $itemId = $order->item_id;
            $latestMessage = Message::where('item_id', $itemId)
                ->orderByDesc('created_at')
                ->first();
            
            $itemUnreadCount = 0;
            // 最新メッセージが存在し、自分以外のユーザーから送信された場合
            if ($latestMessage && $latestMessage->user_id !== $user->id) {
                // 自分が最後に送信したメッセージ以降のメッセージ数をカウント
                $myLastMessage = Message::where('item_id', $itemId)
                    ->where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->first();
                
                if ($myLastMessage) {
                    $itemUnreadCount = Message::where('item_id', $itemId)
                        ->where('created_at', '>', $myLastMessage->created_at)
                        ->where('user_id', '!=', $user->id)
                        ->count();
                } else {
                    // 自分がまだメッセージを送信していない場合、すべてのメッセージが未読
                    $itemUnreadCount = Message::where('item_id', $itemId)
                        ->where('user_id', '!=', $user->id)
                        ->count();
                }
            }
            
            $itemUnreadCounts[$itemId] = $itemUnreadCount;
            $unreadMessageCount += $itemUnreadCount;
        }

        // 評価平均を計算
        $averageRating = $user->receivedRatings()->avg('rating');
        $roundedRating = $averageRating ? round($averageRating) : null;

        return view('mypage.index', [
            'user' => $user,
            'activeTab' => $activeTab,
            'listedItems' => $listedItems,
            'purchasedItems' => $purchasedItems,
            'tradingOrders' => $tradingOrders,
            'itemUnreadCounts' => $itemUnreadCounts,
            'unreadMessageCount' => $unreadMessageCount,
            'averageRating' => $roundedRating,
        ]);
    }
}


