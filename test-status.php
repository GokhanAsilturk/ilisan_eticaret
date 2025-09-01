<?php

return [
    'status' => 'API working without database',
    'message' => 'Test successful',
    'timestamp' => now()->toISOString(),
    'routes' => [
        'GET /api/api-test/ping',
        'GET /api/api-test/auth-headers',
        'POST /api/api-test/post-data',
        'POST /api/api-test/validation',
        'GET /api/api-test/error'
    ]
];
