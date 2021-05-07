<?php

namespace App\Http\Controllers;

// 下二行 不要？
use Illuminate\Http\Request;
use App\User;

class FavoritesController extends Controller
{

    public function store($id)
    {   // 投稿をお気に入り登録する
        \Auth::user()->favorite($id);
        // 前のURLにリダイレクトさせる
        return back();
    }
    
    public function destroy($id)
    {
        // お気に入り解除
        \Auth::user()->unfavorite($id);
        // リダイレクト
        return back();
    }
    
}
