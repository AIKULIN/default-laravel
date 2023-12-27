<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class ErrorResponse extends Exception
{
    public function render()
    {
        $file = $this->getFile();
        $line = $this->getLine();
        $msg = $this->getMessage();
        $code = $this->getCode()?: 422;

        if (!is_null($this->getPrevious())) {
            $file = $this->getPrevious()->getFile()?? $this->getFile();
            $line = $this->getPrevious()->getLine()?? $this->getLine();
            $msg = $this->getPrevious()->getMessage()?? $this->getMessage();
            $code = $this->getPrevious()->getCode()?? $this->getCode();
        }

        // 錯誤訊息寫進log
        $log['msg'] = $msg;
        $log['file'] = $file.':'.$line;
        $log['code'] = $code;
        Log::channel('handlerException')->error('ErrorResponse:'.json_encode($log));

        throw new HttpResponseException(response()->error('0x00000001', $msg, $file, $line, $code));
    }
}
