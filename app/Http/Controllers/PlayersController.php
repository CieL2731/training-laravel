<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new Response(
            Player::query()
            ->select(['id', 'name','hp','mp','money'])
            ->get()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new Response(
            Player::query()
                ->find($id) // 指定したIDに一致するプレイヤーを取得
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); // トランザクション開始

        try {// エラーが発生すればcatchへ
            $newId = Player::insertGetId([// 送られてきたデータに沿ってカラムを新規作成
                'name' => $request->name,
                'hp' => $request->hp,
                'mp' => $request->mp,
                'money' => $request->money,
            ]);

            DB::commit(); // トランザクションコミット

            // JSONレスポンスとしてidを返す
            return response()->json(['id' => $newId]);

        } catch (\Exception $e) {// エラーが発生した場合の処理
            DB::rollBack(); // トランザクションロールバック

            // エラーメッセージを返す
            return response()->json(['error' => 'store error!'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // トランザクション開始

        try {// エラーが発生すればcatchへ
            Player::Where('id',$id) // 指定したIDに一致するプレイヤーを検索
            ->update($request->all());   // 送られてきたデータに沿って指定されたIDのカラムを更新

            DB::commit(); // トランザクションコミット

            // UP DATEが完了したらJSONレスポンスを返す
            return response()->json(['update complete.']);

        } catch (\Exception $e) {// エラーが発生した場合の処理
            DB::rollBack(); // トランザクションロールバック

            // エラーメッセージを返す
            return response()->json(['error' => 'update error!'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {




        try {// エラーが発生すればcatchへ
            Player::where('id',$id) // 指定したIDに一致するプレイヤーを検索
            ->delete(); // 削除

            DB::commit(); // トランザクションコミット

            // DELETEが完了したらJSONレスポンスを返す
            return response()->json(['delete complete.']);

        } catch (\Exception $e) {// エラーが発生した場合の処理
            DB::rollBack(); // トランザクションロールバック

            // エラーメッセージを返す
            return response()->json(['error' => 'delete error!'], 500);
        }
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
}