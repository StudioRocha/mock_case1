<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemLikesTable extends Migration
{
    public function up()
    {
        Schema::create('item_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['item_id','user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_likes');
    }
}


