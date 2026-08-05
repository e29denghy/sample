<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeChatController extends Controller
{
    public function serve(Request $request)
    {
        Log::info('WeChat request arrived.');

        $hasVerificationQuery = collect(['signature', 'timestamp', 'nonce', 'echostr'])
            ->every(fn (string $key): bool => $request->filled($key));

        if ($request->isMethod('GET') && ! $hasVerificationQuery) {
            return response('Bad Request', 400);
        }

        if ($request->isMethod('POST') && trim($request->getContent()) === '') {
            return response('Bad Request', 400);
        }

        $server = app('easywechat.official_account')->getServer();
        $server->with(function (): string {
            return '欢迎关注程序猿个人修养！';
        });

        return $server->serve();
    }
}
