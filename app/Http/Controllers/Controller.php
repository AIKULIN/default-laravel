<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi (
 *     @OA\Info(
 *         title="檔案社群 API",
 *         description="檔案社群API文件
 *         1.1 ~ 1.8 粉絲相關API
 *         2.1 ~ 2.6 創作者相關API",
 *         version="1.0.19"
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
 *         name="後台",
 *         description="員工管理、報表管理、當日即時報表"
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


    /**
     *
     * @OA\Post(
     *      path="/api/v1/onsite/get_number",
     *      tags={"前台"},
     *      summary="場場取號",
     *      security={{"JwtToken":{}}},
     *      description="現場員工登入後才可取號",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/json",
     *             @OA\Schema(
     *                 required={"username", "gender", "phone", "head_count_big", "head_count_small"},
     *                 @OA\Property(property="username", description="客戶名稱", type="string", default="李"),
     *                 @OA\Property(property="gender", description="客戶名稱", enum={"male", "female"}, default="male"),
     *                 @OA\Property(property="phone", description="客戶名稱", type="string", default="0912345678"),
     *                 @OA\Property(property="head_count_big", description="客戶名稱", type="string", default="1"),
     *                 @OA\Property(property="head_count_small", description="客戶名稱", type="string", default="0"),
     *             )
     *         ),
     *      ),
     *      @OA\Response(response=200, description="成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", description="狀態"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="number", description="取號號碼", type="integer", default="A0001"),
     *                      @OA\Property(property="seat", description="桌位類型", type="string", default="1 人桌"),
     *                      @OA\Property(property="waiting_count", description="候位人數", type="string", default="9"),
     *                  ),
     *             ),
     *         )
     *      ),
     *      @OA\Response(response=401, description="認證錯誤"),
     *      @OA\Response(response=422, description="錯誤")
     * )
     *

     * @OA\Post(
     *      path="/api/v1/manage/users/login",
     *      tags={"後台"},
     *      summary="使用者登入",
     *      description="",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/json",
     *             @OA\Schema( /api/v1/login POST
     *                 required={"account", "password"},
     *                 @OA\Property(property="account", description="員工號碼", type="string", default="00001"),
     *                 @OA\Property(property="password", description="密碼", type="string", default="password")
     *             )
     *         ),
     *     ),
     *     @OA\Response(response=200, description="成功",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", description="狀態"),
     *             @OA\Property(property="data", type="object", description="資料",
     *                 @OA\Property(property="verified", type="boolean", description="驗證(true:已驗證,false:未驗證)"),
     *                 @OA\Property(property="token", type="string", description="簽章"),
     *                 @OA\Property(property="token_type", type="string", description="簽章類型"),
     *                 @OA\Property(property="expires_in", type="integer", description="時效")
     *             )
     *          ),
     *     ),
     *     @OA\Response(response=401, description="認證錯誤"),
     *     @OA\Response(response=422, description="錯誤")
     * )
     *
     */
    function index()
    {
    }

    /**
     * 三竹簡訊傳送
     *
     * @param $data
     * @return mixed
     * @throws GuzzleException
     */
    public function sendMiTake($data): mixed
    {
        $appName = env('APP_NAME');
        $username = $data['username'];
        $url = $data['url'];
        $phone = $data['phone'];
        $smsBody = "{$username} 您已成功在 {$appName}抽號候位, 查詢網址 {$url}" ;
        $client = new Client();
        $response = $client->request('POST', env('MITAKE_SMS_URL'), [
            'form_params' => [
                'username' => env('MITAKE_USERNAME'),
                'password' => env('MITAKE_PASSWORD'),
                'dstaddr'  => $phone,
                'smbody'   => $smsBody
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function queueNumber($request)
    {
        // 檢查是否為失約黑名單
        $this->blackListUsers($request['phone']);

        // 如果資料存在更新，不存在新建客戶資料
        $client = $this->clientUpdateOrCreate($request);
        // 客戶 id
        $request['queue_client_id'] = $client->id?? null; //若無法抓取客戶資料，寫入null 失敗觸發DB::rollback()
        // 若是員工有登入將取的user_id
        $request['user_id'] = auth()->guard('jwt_users')->id()?? null;
        // 取號並儲存候位資料
        $number = $this->queueNumber($request);
        $data['url'] = env('APP_URL') . "?id={$number}";

        // 進入Queue 排程，待發送簡訊
        event(new eventSendSMS($data));
    }

    /**
     * 更新或新建客戶資料
     *
     * @param $data
     * @return mixed
     */
    public function clientUpdateOrCreate($data)
    {
        return $this->repositoryQueueUsers->updateOrCreate([
            'phone' => $data['phone']
        ], [
            'username' => $request['username'],
            'gender' => $request['gender'],
            'phone' => $data['phone'],
        ]);
    }

    public function blackListUsers($phone)
    {
        if($this->repositoryBlacklistUsers->checkUsers($phone)) {
            // 自訂義Exceptions, Response將呈現422 status, 並依照設定語言輸出錯誤訊息
            throw new ErrorResponse(__('exception.blacklist_users.limit'));
        }
    }

    /**
     * 取號並儲存候位資料
     *
     * @param $data
     * @return mixed
     */
    public function queueNumber($data): mixed
    {
        // 以時間為key 用redis 遞增數字
        $number = $this->redisService->increment(date('Y-m-d'));
        // 將字串數字以openssl_encrypt和base64編碼加密
        $number = $this->encryptDecryptService->eId($number, 'wait_count');
        $data['number'] = $number;
        $data['get_date'] = date('Y-m-d H:i:s');
        // 儲存候位資料
        $this->queueDataSave($data);

        return $number;
    }

    /**
     * 儲存候位資料
     *
     * @param $data
     * @return void
     */
    public function queueDataSave($data)
    {
        $this->repositoryQueueData->create($data);
    }

}
