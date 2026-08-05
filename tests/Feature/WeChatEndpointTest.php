<?php

namespace Tests\Feature;

use Tests\TestCase;

class WeChatEndpointTest extends TestCase
{
    public function test_empty_probe_is_rejected_without_booting_easywechat(): void
    {
        $this->get('/wechat')->assertBadRequest();
        $this->post('/wechat')->assertBadRequest();
    }
}
