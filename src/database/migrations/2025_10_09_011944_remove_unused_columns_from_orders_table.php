<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedColumnsFromOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // 使用されていないpriceとqtyカラムを削除
            $table->dropColumn(['price', 'qty']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // ロールバック時にpriceとqtyカラムを復元
            $table->unsignedInteger('price')->after('item_id');
            $table->unsignedInteger('qty')->default(1)->after('price');
        });
    }
}
