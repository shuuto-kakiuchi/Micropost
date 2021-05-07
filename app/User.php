<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    /**
     * このユーザに関係するモデルの件数をロードする。
     */
    public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers','favorites']);
    }
    
    /**
     * このユーザがフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    /**
     * このユーザをフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    /**
     * $userIdで指定されたユーザをフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function follow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 対象が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist || $its_me) {
            // すでにフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }

    /**
     * $userIdで指定されたユーザをアンフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function unfollow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 対象が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            // すでにフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }

    /**
     * 指定された $userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中ならtrueを返す。
     *
     * @param  int  $userId
     * @return bool
     */
    public function is_following($userId)
    {
        // フォロー中ユーザの中に $userIdのものが存在するか
        return $this->followings()->where('follow_id', $userId)->exists();
    }


    /**
     * このユーザとフォロー中ユーザの投稿に絞り込む。
     */
    public function feed_microposts()
    {
        // このユーザがフォロー中のユーザのidを取得して配列にする
        $userIds = $this->followings()->pluck('users.id')->toArray();
        // このユーザのidもその配列に追加
        $userIds[] = $this->id;
        // それらのユーザが所有する投稿に絞り込む
        return Micropost::whereIn('user_id', $userIds);
    }
    
    
    /* お気に入り機能 */
    
    // 「このユーザーがお気に入りしてる投稿」を表すメソッドが必要？
    /*
    publice function favoposts()
    {
        return $this->hasMany(～～～)
    }
    のような
    */
    
    // belongsToMany(関連づけるモデル名, 使用する中間テーブル名, 中間テーブルに保存されている自分のidのカラム名, 中間テーブルに保存されている関係先のidのカラム名);
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }


    public function favorite($micropost_id) // 絶対間違ってる
    {
        if (!$this->is_favorite($micropost_id)) {
            $this->favorites()->attach($micropost_id);
            return true;
        }

        return false;
        
        // すでにお気に入りしているかの確認
        $exist = $this->is_favorite($micropost_id);
        // 対象がその投稿かどうか確認 不要？
        // $its_post = $this->id == $micropost_id;

        if ($exist || $its_post) {
            // すでにお気に入りしていれば何もしない
            return false;
        } else {
            // まだであればお気に入りする
            $this->favorites()->attach($micropost_id);
            return true;
        }
    }
    
    public function unfavorite($micropost_id) 
    {
        
        if ($this->is_favorite($micropost_id)) {
            $this->favorites()->detach($micropost_id);
            return true;
        }

        return false;
        
        // すでにお気に入りしているかの確認
        $exist = $this->is_favorite($micropost_id);

        // 対象がその投稿かどうかの確認 不要？
        // $its_post = $this->id == $micropost_id;

        if ($exist && !$its_post) {
            // すでにお気に入りしていればお気に入りを外す
            $this->favorites()->detach($micropost_id);
            return true;
        } else {
            // まだであれば何もしない
            return false;
        }
    }
    
    public function is_favorite($micropost_id)
    {
        // お気に入りの中に $userIdのものが存在するか
        return $this->favorites()->where('micropost_id', $micropost_id)->exists();
    }
}