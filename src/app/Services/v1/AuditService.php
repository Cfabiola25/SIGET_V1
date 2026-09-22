<?php

namespace App\Services\v1;

class AuditService
{
    public function log(string $action, string $entityType, int $entityId, array $details = []): void
    {
        // placeholder for audit logging
    }
}
