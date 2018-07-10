<?php
return [
    'upload' => [
        'disks' => env('UPLOAD_DISKS', 'admin'),
        'url' => env("UPLOAD_URL", 'upload')
    ]
];