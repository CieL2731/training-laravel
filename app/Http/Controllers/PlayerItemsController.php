<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\PlayerItemsResource;
use App\Models\Player;
use App\Models\Item;
use App\Models\PlayerItems;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PlayerItemsController extends Controller
{
    /**
     * Store or add items to a resource
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function add_item(Request $request, $id)
    {
        // リクエストからデータを取得
        $itemId = $request->input('itemId');
        $count = $request->input('count');

        // プレイヤーIDからプレイヤーを取得
        $player = Player::find($id);

        // アイテムIDからアイテムを取得
        $item = Item::find($itemId);

        // PlayerItemsテーブルから対応するデータを取得
        $playerItem = PlayerItems::where('player_id', $player->id)
            ->where('item_id', $item->id)
            ->first();

        // データが存在するかどうかを確認し、カラムを追加または更新
        if ($playerItem) {
            // プレイヤーIDとアイテムIDを参照し、item_countを加算
            PlayerItems::where('player_id', $player->id)
            ->where('item_id', $item->id)
            ->update(['item_count'=>$playerItem->item_count + $count]);

            // アイテムIDと所持数が加算後のレスポンスを返す
            return response()->json(['itemId' => $itemId, 'count' => $playerItem->item_count + $count]);
        } 
        else {
            // データが存在しない場合、新しいカラムを作成
            PlayerItems::insert([
                'player_id'=>$id,
                'item_id'=>$itemId,
                'item_count'=>$count
            ]);

            // 追加されたアイテムID、所持数のレスポンスを返す
            return response()->json(['itemId' => $itemId, 'count' => $count]);
        }
    }
}