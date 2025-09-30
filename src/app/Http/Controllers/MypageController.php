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
        $user = Auth::user();
        $activeTab = $request->query('page', 'sell');

        $listedItems = Item::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $purchasedItems = Order::query()
            ->where('user_id', $user->id)
            ->with('item')
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


