<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where('item_names', 'like', "%{$keyword}%");
        }

        if (Auth::check()) {
            $query->where('user_id', '!=', Auth::id());
        }

        $items = $query->latest()->paginate(24)->appends($request->query());

        $activeTab = $request->query('tab', 'recommend');

        return view('items.index', [
            'items' => $items,
            'activeTab' => $activeTab,
            'keyword' => $request->input('q', ''),
        ]);
    }
}
