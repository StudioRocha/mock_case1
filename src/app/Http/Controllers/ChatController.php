<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Item;
use App\Models\Order;
use App\Models\Message;
use App\Http\Requests\ChatMessageRequest;

class ChatController extends Controller
{
    /**
     * チャット画面を表示
     */
    public function index(Item $item)
    {
        $user = Auth::user();

        // 取引中のOrderを取得（購入者または出品者として）
        $order = Order::where('item_id', $item->id)
            ->where('trade_status', Order::TRADE_STATUS_TRADING)
            ->where(function($query) use ($user) {
                // 購入者として
                $query->where('user_id', $user->id)
                      // または出品者として
                      ->orWhereHas('item', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with(['item.user', 'user'])
            ->first();

        if (!$order) {
            return redirect()->route('mypage.index', ['page' => 'trading'])
                ->with('error', '取引中の商品が見つかりません。');
        }

        // 取引相手を取得
        $otherUser = $order->user_id === $user->id 
            ? $order->item->user  // 購入者の場合、出品者
            : $order->user;        // 出品者の場合、購入者

        // メッセージを取得
        $messages = Message::where('item_id', $item->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // その他の取引中の商品を取得（サイドバー用）
        $otherTradingOrders = Order::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('item', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->where('trade_status', Order::TRADE_STATUS_TRADING)
            ->where('item_id', '!=', $item->id)
            ->with(['item'])
            ->orderByDesc('updated_at')
            ->get();

        // 購入者かどうかを判定
        $isBuyer = $order->user_id === $user->id;

        // 既読情報を更新（チャット画面を開いた時点で既読とする）
        if ($isBuyer) {
            $order->update(['buyer_last_viewed_at' => now()]);
        } else {
            $order->update(['seller_last_viewed_at' => now()]);
        }

        // セッションから入力情報を取得（入力情報保持機能）
        $savedMessage = session("chat_message_{$item->id}", '');

        return view('chat.index', [
            'item' => $item,
            'order' => $order,
            'otherUser' => $otherUser,
            'messages' => $messages,
            'otherTradingOrders' => $otherTradingOrders,
            'isBuyer' => $isBuyer,
            'savedMessage' => $savedMessage,
        ]);
    }

    /**
     * メッセージを投稿
     */
    public function store(ChatMessageRequest $request, Item $item)
    {
        $user = Auth::user();

        // 取引中のOrderを確認
        $order = Order::where('item_id', $item->id)
            ->where('trade_status', Order::TRADE_STATUS_TRADING)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('item', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->first();

        if (!$order) {
            return redirect()->route('mypage.index', ['page' => 'trading'])
                ->with('error', '取引中の商品が見つかりません。');
        }

        // メッセージを作成
        Message::create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        // Orderのupdated_atを更新（並び替え用）
        $order->touch();

        // セッションから入力情報を削除
        session()->forget("chat_message_{$item->id}");

        return redirect()->route('chat.index', $item);
    }

    /**
     * メッセージを編集
     */
    public function update(ChatMessageRequest $request, Item $item, Message $message)
    {
        $user = Auth::user();

        // 自分のメッセージのみ編集可能
        if ($message->user_id !== $user->id) {
            return redirect()->route('chat.index', $item);
        }

        // メッセージを更新
        $message->update([
            'message' => $request->message,
        ]);

        return redirect()->route('chat.index', $item);
    }

    /**
     * メッセージを削除
     */
    public function destroy(Item $item, Message $message)
    {
        $user = Auth::user();

        // 自分のメッセージのみ削除可能
        if ($message->user_id !== $user->id) {
            return redirect()->route('chat.index', $item);
        }

        // メッセージを削除（ソフトデリート）
        $message->delete();

        return redirect()->route('chat.index', $item);
    }

    /**
     * 取引を完了（購入者のみ）
     */
    public function complete(Item $item)
    {
        $user = Auth::user();

        // 取引中のOrderを確認（購入者のみ）
        $order = Order::where('item_id', $item->id)
            ->where('user_id', $user->id)
            ->where('trade_status', Order::TRADE_STATUS_TRADING)
            ->first();

        if (!$order) {
            return redirect()->route('chat.index', $item)
                ->with('error', '取引中の商品が見つかりません。');
        }

        // 取引状態を完了に更新
        $order->update([
            'trade_status' => Order::TRADE_STATUS_COMPLETED,
        ]);

        // TODO: フェーズ6で実装予定 - 取引完了モーダルから評価機能を実装

        return redirect()->route('items.index')
            ->with('success', '取引を完了しました。');
    }

    /**
     * 入力情報をセッションに保存（入力情報保持機能）
     */
    public function saveDraft(Request $request, Item $item)
    {
        $message = $request->input('message', '');
        Session::put("chat_message_{$item->id}", $message);
        return response()->json(['success' => true]);
    }

    /**
     * 既読情報を更新（非同期処理用）
     */
    public function markAsRead(Request $request, Item $item)
    {
        $user = Auth::user();

        // 取引中のOrderを取得
        $order = Order::where('item_id', $item->id)
            ->where('trade_status', Order::TRADE_STATUS_TRADING)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('item', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => '取引中の商品が見つかりません。'], 404);
        }

        // 既読情報を更新
        $isBuyer = $order->user_id === $user->id;
        if ($isBuyer) {
            $order->update(['buyer_last_viewed_at' => now()]);
        } else {
            $order->update(['seller_last_viewed_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
