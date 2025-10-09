<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Order;

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

        return view('mypage.index', [
            'user' => $user,
            'activeTab' => $activeTab,
            'listedItems' => $listedItems,
            'purchasedItems' => $purchasedItems,
        ]);
    }
}


