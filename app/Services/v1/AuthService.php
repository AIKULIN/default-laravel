<?php

namespace App\Services\v1;

use App\Exceptions\Auth\AuthException;
use App\Exceptions\ErrorResponse;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Users\UsersRepository;
use Illuminate\Support\Facades\Password;

class AuthService
{

    protected UsersRepository $usersRepository;

    public function __construct() {
        $this->usersRepository = app('App\Repositories\Users\UsersRepository');
    }

    /**
     * 註冊
     *
     * @param $request
     * @return array
     * @throws ErrorResponse
     */
    public function register($request)
    {
        $user = $this->usersRepository->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        event(new Registered($user));

        return $this->login();
    }

    /**
     * 登入
     *
     * @return array
     * @throws ErrorResponse
     */
    public function login(): array
    {
        $credentials = request(['email', 'password']);
        if (! $token = auth()->guard('api')->attempt($credentials)) {
            throw new ErrorResponse('invalid credentials', 401);
        }
        $verified = (bool) auth()->guard('api')->user()->email_verified_at?? false;

        return [
            'token' => $token,
            'verified' => $verified
        ];
    }

    public function logout()
    {
        auth()->guard('api')->logout();
        return false;
    }


    /**
     * 會員Email 驗證
     *
     * EMAIL 欄位為區分使用者以前自行注冊
     * @param $request
     * @return object
     */
    public function emailVerify($request): object
    {
        $user = $this->usersRepository->where('email', $request->email?? null)->first();

        $data['hasVerifiedEmail'] = $user->hasVerifiedEmail(); // 檢查是否已驗證
        $data['expires'] = Carbon::now()->timestamp > $request->expires?? 0; // 檢查連結是否已過期
        $data['hash'] = !hash_equals($request->hash?? null, sha1($user->email)); // 驗證雜湊值
        $data['signature'] = !$this->hasCorrectSignature($request->all());
        $data['user'] = $user;

        return (object) $data;
    }

    /**
     * Determine if the signature from the given request matches the URL.
     *
     * @param $params
     * @return bool
     */
    public function hasCorrectSignature($params)
    {
        $signature = $params['signature'];
        unset($params['signature']);

        $hash = hash_hmac('sha256', http_build_query($params), env('APP_KEY'));
        return hash_equals($hash, $signature);
    }

    /**
     * 寄送忘記密碼Token 連結
     *
     * @param $request
     * @return bool
     */
    public function resetPasswordEmail($request): bool
    {
        return Password::broker('jwt_users')->sendResetLink($request->only('email')) == Password::RESET_LINK_SENT;
    }
}
