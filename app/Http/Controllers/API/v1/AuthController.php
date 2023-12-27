<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Exceptions\ErrorResponse;
use App\Http\Requests\V1\Users\RegisterRequest;
use App\Services\v1\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(
        AuthService $authService
    ) {
        $this->authService = $authService;
    }

    public function login()
    {
        try {
            $data = $this->authService->login();
            $token = $data['token'];
            $verified = $data['verified'];

            return response()->jwtToken($token, $verified);
        } catch (\Exception $e) {
            throw new ErrorResponse($e->getMessage(), 422, $e);
        }
    }

    public function logout()
    {
        auth()->guard('api')->logout();
        return response()->json(['status' => 0]);
    }

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->authService->register($request);
            $token = $data['token'];
            $verified = $data['verified'];

            DB::commit();
            return response()->jwtToken($token, $verified);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorResponse($e->getMessage(), 422, $e);
        }

    }

    /**
     * Email Verify. 後端背景運行
     *
     * @param Request $request
     * @return Response
     * @throws ErrorResponse
     */
    public function emailVerify(Request $request): Response
    {
        $verify = $this->authService->emailVerify($request);

        // 檢查是否已驗證
        if ($verify->hasVerifiedEmail) {
            return redirect()->away(env('APP_URL'));
        }

        // 檢查連結是否已過期
        if ($verify->expires) {
            throw new ErrorResponse('Verification link has expired', 422);
        }

        // 驗證雜湊值
        if ($verify->hash) {
            throw new ErrorResponse('Invalid verification hash', 422);
        }

        // 驗證簽名
        if ($verify->signature) {
            throw new ErrorResponse('Invalid signature', 422);
        }

        // 驗證成功，執行相應的操作（例如激活用戶）
        if (!$verify->user->hasVerifiedEmail()) {
            $verify->user->markEmailAsVerified();
        }

        return redirect()->away(env('APP_URL'));
    }

    /**
     * 忘記密碼
     *
     * @param Request $request
     * @return Response
     * @throws ErrorResponse
     */
    public function passwordEmail(Request $request): Response
    {
        DB::beginTransaction();
        try {
            if ($this->authService->resetPasswordEmail($request)) {
                DB::commit();
                return response()->data([]);
            } else {
                DB::rollback();
                throw new ErrorResponse('Unable to send password reset link', 422);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw new ErrorResponse($e->getMessage(), 422, $e);
        }

    }
}
