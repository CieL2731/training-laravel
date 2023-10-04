<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_items', function (Blueprint $table) {
            // 符号なしBIGINT型でカラムを作成
            $table->unsignedBigInteger('player_id')->comment("プレイヤーID");
            $table->unsignedBigInteger('item_id')->comment("アイテムID");

            // 複合主キーの設定
            $table->primary(['player_id', 'item_id']);

            $table->integer('itemCount')->comment("アイテムの所持数");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_items');
    }
}
