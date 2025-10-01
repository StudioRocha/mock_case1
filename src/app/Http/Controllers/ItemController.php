<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\ItemLike;
use Illuminate\Support\Facades\DB;


class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where('item_names', 'like', "%{$keyword}%");
        }

        $activeTab = $request->query('tab', 'recommend');

        // タブ=マイリストの場合は、ログインユーザーが「いいね」した商品のみ表示
        if ($activeTab === 'mylist') {
            if (Auth::check()) {
                $userId = Auth::id();
                $query->whereExists(function ($sub) use ($userId) {
                    $sub->selectRaw('1')
                        ->from('item_likes')
                        ->whereColumn('item_likes.item_id', 'items.id')
                        ->where('item_likes.user_id', $userId);
                });
            } else {
                // 未ログイン時は空にする
                $query->whereRaw('1=0');
            }
        } else {
            // おすすめタブなど通常一覧では自分の出品を除外
            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }
        }

        $items = $query->latest()->paginate(24)->appends($request->query());

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

    public function show(Item $item)
    {
        $item->load(['categories','comments' => function($q){ $q->latest(); }, 'comments.user']);
        $liked = $item->isLikedBy(optional(auth()->user())->id);
        $likeCount = $item->like_counts;
        return view('items.show', compact('item','liked','likeCount'));
    }

    public function comment(Item $item, CommentRequest $request)
    {
        $data = $request->validated();
        $comment = new Comment();
        $comment->item_id = $item->id;
        $comment->user_id = auth()->id();
        $comment->comment_body = $data['comment_body'];
        $comment->save();
        return back()->with('success', 'コメントを投稿しました');
    }

    public function purchase(Item $item)
    {
        // TODO: 購入フロー実装（暫定）
        return redirect()->route('items.show', $item)->with('success', '購入手続きは準備中です。');
    }

    public function like(Item $item)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $userId = auth()->id();
        DB::transaction(function() use ($item, $userId) {
            $like = ItemLike::where('item_id', $item->id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();
            if ($like) {
                $like->delete();
                if ((int)$item->like_counts > 0) {
                    $item->decrement('like_counts');
                }
            } else {
                ItemLike::create(['item_id' => $item->id, 'user_id' => $userId]);
                $item->increment('like_counts');
            }
        });
        return back();
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
