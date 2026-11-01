<?php

return [
    'registration_mode' => env('REGISTRATION_MODE', 'invite'),
    'attachment_max_size' => (int) env('ATTACHMENT_MAX_SIZE', 52428800),
    'attachment_max_total' => (int) env('ATTACHMENT_MAX_TOTAL', 524288000),
    'backup_retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
];
