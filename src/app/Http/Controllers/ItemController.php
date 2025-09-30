<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;


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

    public function create()
    {
        $categories = Category::orderBy('category_names')->get();
        $conditions = [1 => '良好', 2 => '目立った傷や汚れ無し', 3 => 'やや傷や汚れあり', 4 => '状態が悪い'];
        return view('items.sell', compact('categories','conditions'));
    }

    public function store(ExhibitionRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        // 元のままストレージに保存
        $path = $request->file('item_image')->store('items', 'public');

        $item = Item::create([
            'user_id' => $user->id,
            'item_image_paths' => $path,
            'item_names' => $data['item_name'],
            'brand_names' => $data['brand_name'] ?? '',
            'item_prices' => $data['item_price'],
            'item_descriptions' => $data['item_description'],
            'conditions' => (int)$data['condition'],
        ]);

        // 多対多でカテゴリを紐付け
        if (method_exists($item, 'categories')) {
            $item->categories()->sync($data['category_ids']);
        }

        return redirect('/')->with('success', '出品が完了しました。');
    }
}
