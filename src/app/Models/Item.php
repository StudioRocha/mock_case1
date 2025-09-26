<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_image_paths',
        'item_names',
        'brand_names',
        'item_prices',
        'like_counts',
        'comment_counts',
        'item_descriptions',
        'category_ids',
        'conditions',
        'is_sold',
    ];
}
