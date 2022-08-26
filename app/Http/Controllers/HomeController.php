<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // ここでメモを取得
        $memos = Memo::select('memos.*')
            ->where('user_id','=',\Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at','DESC')
            ->get();

        $tags = Tag::where('user_id', '=', \Auth::id())->whereNull('deleted_at')->orderBy('id','DESC')->get();

        return view('create', compact('memos', 'tags'));
    }

    public function store(Request $request)
    {
        $posts = $request->all();
     
        // dd=dump dieの略 → メソッドの引数の採った値を展開して止める → データ確認

        // ======= ここからトランザクション開始 =======
        DB::transaction(function() use($posts) {
        // メモIDをインサートして取得
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id()]);
            $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])->exists();
        // 新規タグが入力されているかチェック
        // 新規タグが既にtagsテーブルに存在するのかチェック
            if( !empty($posts['new_tag']) && !$tag_exists ) {
        // 新規タグが存在していなければ、tagsテーブルにインサート→IDを取得
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
        // memo_tagsにインサートして、メモとタグを紐付ける
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }
        // 既存タグが紐付けられた場合→memo_tagsにインサート
            foreach($posts['tags'] as $tag) {
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag]);
            }
        });
        // ======= ここからトランザクションの範囲 =======
    
        return redirect( route('home') );
    }

    public function edit($id)
    {
        // ここでメモを取得
        $memos = Memo::select('memos.*')
            ->where('user_id','=',\Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at','DESC')
            ->get();

        $edit_memo = Memo::find($id);

        return view('edit', compact('memos', 'edit_memo'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        Memo::where('id', $posts['memo_id'])->update(['content' => $posts['content']]);
        return redirect( route('home') );
    }

    public function destroy(Request $request)
    {
        $posts = $request->all();

        // Memo::where('id', $posts['memo_id'])->delete();←NGこれやると物理削除
        Memo::where('id', $posts['memo_id'])->update(['deleted_at' => date("Y-m-d H:i:s", time())]);

        return redirect( route('home') );
    }


}
