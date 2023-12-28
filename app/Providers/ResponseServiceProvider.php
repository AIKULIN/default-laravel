<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Response::macro('data', function ($data, $httpCode=200) {
            return Response::json([
                'status' => true,
                'data' => $data,
            ], $httpCode);
        });

        Response::macro('jwtToken', function ($token, $verified=false, $httpCode=200) {
            return Response::json([
                'status' => true,
                'data' => [
                    'verified' => $verified,
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->guard('api')->factory()->getTTL() * 60,
                ],
            ], $httpCode);
        });

        Response::macro('error', function ($code, $message, $file='', $line=0, $httpCode=400) {
            $data = [
                'status' => false,
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
            ];

            // 環境有開除錯才會出現
            if (env('APP_DEBUG', false)) {
                $data['error']['file'] = $file;
                $data['error']['line'] = $line;
            }

            return Response::json($data, $httpCode);
        });
    }
}
