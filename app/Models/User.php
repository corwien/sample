<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

// use App\Models\Status;
use Auth;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    /**
     * 默认初始化激活Token
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function($user)
        {
            $user->activation_token = str_random(30);
        });


    }


    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
         return "http://www.gravatar.com/avatar/$hash?s=$size";

    }

    /**
    *  加密密码，默认调用
    */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    /**
     * 用户一对多，微博
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(Status::class);

    }

    public function feed()
    {
        /*return $this->statuses()
                    ->orderBy('created_at', 'desc');*/

        // 获取关注人的用户ID
        $user_ids = Auth::user()->followings->pluck('id')->toArray();

        // 将用户自己的放入
        array_push($user_ids, Auth::user()->id);

        return Status::whereIn('user_id', $user_ids)
                              ->with('user')
                              ->orderBy('created_at', 'desc');

    }

    /**
     * 关联操作 获取粉丝
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    /**
     * 关联操作 获取关注者
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * 关注
     * @param $user_ids
     */
    public function follow($user_ids)
    {
        if(!is_array($user_ids))
        {
            $user_ids = compact('user_ids');
        }

        $this->followings()->sync($user_ids, false);
    }

    /**
     * 取消关注
     * @param $user_ids
     */
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    /**
     * 判断是否有关注
     * @param $user_id
     * @return mixed
     */
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);

    }




}
