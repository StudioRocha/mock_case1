<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrdersForSingleItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('item_id')->after('user_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('price')->after('item_id');
            $table->unsignedInteger('qty')->default(1)->after('price');
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
            $table->dropConstrainedForeignId('item_id');
            $table->dropColumn('price');
            $table->dropColumn('qty');
        });
    }
}
