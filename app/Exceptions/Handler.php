<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->limitRoute();
    }

    /**
     * 預防不允許的ROUTE 漏洞
     * 限制預設ROUTE
     *
     * @return void
     */
    private function limitRoute()
    {
        $uri = request()->getRequestUri();
        switch ($uri) {
            case '/_ignition/health-check':
            case '/_ignition/execute-solution':
            case '/_ignition/update-config':
            case '/sanctum/csrf-cookie':
                $ip = request()->ip();
                Log::channel('limitRoute')->info($uri.':'.$ip);
                exit();
            default:

        }
    }
}
