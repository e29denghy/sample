<?php

return [
    'disk' => 'public',
    'prefix' => trim((string) env('MEDIA_PREFIX', 'denghy/media'), '/'),
    'max_kilobytes' => (int) env('MEDIA_MAX_KILOBYTES', 8192),
    'max_width' => (int) env('MEDIA_MAX_WIDTH', 12000),
    'max_height' => (int) env('MEDIA_MAX_HEIGHT', 12000),
];
