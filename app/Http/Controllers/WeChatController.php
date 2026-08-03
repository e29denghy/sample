<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class WeChatController extends Controller
{
    public function serve()
    {
        Log::info('WeChat request arrived.');

        $server = app('easywechat.official_account')->getServer();
        $server->with(function (): string {
            return '欢迎关注程序猿个人修养！';
        });

        return $server->serve();
    }
}
