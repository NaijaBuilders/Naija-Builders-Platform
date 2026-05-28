<?php

return [
    'lock_enabled' => filter_var(env('PRELAUNCH_LOCK_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'bypass_token' => env('PRELAUNCH_BYPASS_TOKEN'),

    'allowed_ips' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('PRELAUNCH_ALLOWED_IPS', ''))
    ))),
];
