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

    /**
     * Update resources using items.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function use_item(Request $request, $id)
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

        // データがないか、アイテムを所持していない場合、エラーレスポンスを返す
        if (!$playerItem || $playerItem->item_count <= 0) {
            return response()->json(['error' => 'アイテムを持っていません。'], 400);
        }

        if($itemId == 1){
            // hpが上限の200の場合何もしない
            if($player->hp == 200){
                return response()->json(['HPが満タンです！'], 200);
            }

            // 上限以上回復しようとした場合使用個数を補正
            $nowhp = $player->hp;   // 現在のHPを取得
            $curehp = 200 - $nowhp; // 回復できる量を取得
            for($usecount = 1; $usecount<$count; $usecount++){
                // 回復できる量に対して使える最大数を取得 
                $curehp = $curehp - $item->value;
                if($curehp <= 0){
                    // 使える最大数になったらループを抜ける
                    break;
                }
            }

            // アイテムIDが1の時はhpに加算
            $player->hp += $item->value * $usecount;    // プレイヤーのhpにitemのvalue*count分加算
            $player->save(); // プレイヤーの変更を保存

            // 200を越えた場合上限値200に補正
            if($player->hp >= 200)
            {
                $player->hp = 200;
                $player->save(); // プレイヤーの変更を保存
            }

        }
        else if($itemId == 2){
            // mpが上限の200の場合何もしない
            if($player->mp == 200){
                return response()->json(['MPが満タンです！'], 200);
            }

            // 上限以上回復しようとした場合使用個数を補正
            $nowmp = $player->mp;   // 現在のHPを取得
            $curemp = 200 - $nowmp; // 回復できる量を取得
            for($usecount = 1; $usecount<$count; $usecount++){
                // 回復できる量に対して使える最大数を取得 
                $curemp = $curemp - $item->value;
                if($curemp <= 0){
                    // 使える最大数になったらループを抜ける
                    break;
                    }
            }

            // アイテムIDが2の時はmpに加算
            $player->mp += $item->value * $usecount;    // プレイヤーのmpにitemのvalue*count分加算
            $player->save(); // プレイヤーの変更を保存

            // 200を越えた場合上限値200に補正
            if($player->mp >= 200)
            {   
                $player->mp = 200;
                $player->save(); // プレイヤーの変更を保存
            }

        }

        // 使用したアイテムの個数分item_countを減算
        PlayerItems::where('player_id', $player->id)
        ->where('item_id', $item->id)
        ->update(['item_count'=>$playerItem->item_count - $usecount]);

        // 各カラムのデータレスポンスを返す
        return response()
        ->json(
            [
            'itemId' => $itemId,
            'count'  => $playerItem->item_count - $usecount,

            'player'=>
            [
            'id' => $id,
            'hp' => $player->hp,
            'mp' => $player->mp,
            ]
            ]
        );
    }

    /**
     * Obtain items using gacha.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function use_gacha(Request $request, $id)
    {
        // ガチャ一回分の使用金額を設定
        $gachaprice = 10;

        // リクエストから回数を取得
        $count = $request->input('count');

        // 使用金額を取得
        $usemoney = $count * $gachaprice;

        // プレイヤーIDからプレイヤーを取得
        $player = Player::find($id);

        // アイテムのデータを取得
        // idが1のデータを取得してitemAに格納
        $itemA = Item::find(1);

        // idが2のデータを取得してitemBに格納
        $itemB = Item::find(2);

        // ガチャの結果を初期化
        $results = [];

        // 所持金が不足していればエラー
        if($player->money < $usemoney){
            return response()->json(['error' => '所持金が足りません！'], 400);
        }

        // ガチャを指定回数引く
        for ($i = 0; $i < $count; $i++) {
            // 0から100の乱数を生成
            $rand = mt_rand(1, 100); 

            // ランダムにアイテムを選択
            $item = null;
            if($itemA->percent >= $rand){
                // 生成された乱数がアイテムAより小さかった場合
                $item = $itemA;
            }
            else{
                // アイテムAより大きかった場合パーセントの値分引いて次へ
                $rand -= $itemA->percent;
            }

            if($itemB->percent >= $rand && $item == null){
                // 生成された乱数がアイテムBより小さかった場合
                $item = $itemB;
            }
            // ここまでで該当しなければハズレ
            
            if($item){
            // アイテムが選択されていればプレイヤーの所持アイテムに追加
            $playerItem = PlayerItems::where('player_id', $player->id)
            ->where('item_id', $item->id)
            ->first();

            if ($playerItem) {
                // すでに同じアイテムを持っていれば値を加算
                PlayerItems::where('player_id', $player->id)
                ->where('item_id', $item->id)
                ->update(['item_count'=>$playerItem->item_count + 1]);
            }
            else{
                // 選択されたアイテムを未所持の場合はカラムを作成
                 PlayerItems::insert([
                     'player_id'=>$player->id,
                     'item_id'=>$item->id,
                     'item_count'=>1
                    ]);
                }
             
            // 結果にアイテムを追加
            $results[] = 
                [
                'itemId' => $item->id,
                'count' => 1,
                ];
             }
             else{
                // アイテムが未選択の場合
                $results[] = 
                [
                'ハズレ'
                ];
             }
        }

       // プレイヤーの所持金を更新
       $player->money -= $usemoney;
       $player->save();

       // レスポンスデータを作成
       $response = [
           'results' => $results,
           'player' => [
               'money' => $player->money,
               'items' => PlayerItems::where('player_id', $player->id)
                          ->select('item_id', 'item_count')->get(),
           ],
       ];

       // 作成したレスポンスデータをレスポンス
        return response()->json($response);
    }
}