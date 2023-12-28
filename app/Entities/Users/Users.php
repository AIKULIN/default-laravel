<?php

namespace App\Entities\Users;

use App\Notifications\ApiEmailVerifyNotification;
use App\Notifications\ApiPasswordResetNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User.
 *
 * @package namespace App\Entities\Users;
 */
// class User extends Model implements Transformable
class Users extends Authenticatable implements Transformable, JWTSubject, MustVerifyEmail
{
    use TransformableTrait, SoftDeletes, Notifiable, HasRoles;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        'oauth_id',
        'oauth_type',
        'oauth_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The other attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * 獲取將存儲在JWT的主題聲明中的標識符。
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * 返回一個鍵值數組，其中包含要添加到JWT的所有自定義聲明。
     */
    public function getJWTCustomClaims()
    {
        // JWT自訂欄位
        return [
            'role'          => 'user',
            // 'name'          => $this->name,
            // 'brand'         => $this->brand,
        ];
    }

    public function setPasswordAttribute($password)
    {
        // 改寫密碼會重複執行，所以需要判斷這個
        if(Hash::needsRehash($password)) {
            $password = Hash::make($password);
        }

        $this->attributes['password'] = $password;
    }

    /**
     * 發送驗證Email 通知。
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new ApiEmailVerifyNotification());
    }

    /**
     * 發送密碼重設通知。
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ApiPasswordResetNotification($token));
    }
}
