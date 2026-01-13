<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'item_id')) {
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method', 50);
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'comment')) {
                $table->text('comment')->nullable();
            }
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
            $table->dropForeign(['item_id']);
            $table->dropColumn(['item_id', 'payment_method', 'shipping_address', 'comment']);
        });
    }
}
