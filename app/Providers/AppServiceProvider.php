<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Memo;
use App\Models\Tag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    // 全てのメソッドが呼ばれる前に先に呼ばれるメソッド
    public function boot()
    {
        view()->composer('*', function ($view) {
            $query_tag = \Request::query('tag');
            // もしクエリパラメータがあれば
            if ( !empty( $query_tag ) ) {
                // タグで絞込
                $memos = Memo::select('memos.*')
                    ->leftJoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
                    ->where('memo_tags.tag_id','=', $query_tag)
                    ->where('user_id','=',\Auth::id())
                    ->whereNull('deleted_at')
                    ->orderBy('updated_at','DESC')
                    ->get();
            } else {
                // タグがなければすべて取得
                $memos = Memo::select('memos.*')
                    ->where('user_id','=',\Auth::id())
                    ->whereNull('deleted_at')
                    ->orderBy('updated_at','DESC')
                    ->get();
            }

            $tags = Tag::where('user_id','=',\Auth::id())
                ->whereNull('deleted_at')
                ->orderBy('id','DESC')
                ->get();

            $view->with('memos', $memos)->with('tags', $tags);
        });
    }
}
