<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi (
 *     @OA\Info(
 *         title="登入系統",
 *         description="登入系統文件",
 *         version="1.0.0"
 *     ),
 *     @OA\ExternalDocumentation(
 *         description="Jwt IO",
 *         url="https://jwt.io/"
 *     ),
 *     @OA\Server(
 *         url = "http://localhost:8000",
 *         description="本機"
 *     ),
 *     @OA\Server(
 *         url = "https://waitSys.dev",
 *         description="開發"
 *     ),
 *     @OA\Tag(
 *         name="登入",
 *         description=""
 *     )
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="JwtToken",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
