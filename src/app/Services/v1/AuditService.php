<?php

namespace App\Services\v1;

use App\Models\v1\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Registra una acción crítica en el Audit Trail inmutable con encadenamiento criptográfico.
     *
     * @param  array{
     *     old_values?: array<string, mixed>,
     *     new_values?: array<string, mixed>,
     *     reason?: string,
     *     metadata?: array<string, mixed>
     * }  $details
     */
    public function log(string $action, string $entityType, int $entityId, array $details = []): AuditLog
    {
        $userId = auth()->id();
        $ip = Request::ip();
        $userAgent = Request::userAgent();

        // 1. Obtener el hash del último registro para mantener la cadena inmutable
        $lastLog = AuditLog::orderByDesc('id')->first();
        $previousHash = $lastLog?->details['hash'] ?? str_repeat('0', 64);

        // 2. Construir payload para firma criptográfica
        $timestamp = now()->toIso8601String();
        $detailsPayload = array_merge($details, [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'timestamp' => $timestamp,
            'previous_hash' => $previousHash,
        ]);

        $signatureSource = implode('|', [
            $previousHash,
            (string) $userId,
            $action,
            $entityType,
            (string) $entityId,
            json_encode($detailsPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $currentHash = hash('sha256', $signatureSource);
        $detailsPayload['hash'] = $currentHash;

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $detailsPayload,
        ]);
    }

    /**
     * Valida la integridad criptográfica de toda la cadena de auditoría.
     * Si algún usuario o administrador editó una fila en MySQL directamente (ej. levantó sanción o alteró marcador),
     * la función detecta el registro exacto donde se rompió la cadena.
     *
     * @return array{
     *     is_intact: bool,
     *     tampered_log_id: int|null,
     *     message: string
     * }
     */
    public function verifyChainIntegrity(): array
    {
        $logs = AuditLog::orderBy('id')->get();
        $expectedPrevHash = str_repeat('0', 64);

        foreach ($logs as $log) {
            $details = $log->details ?? [];
            $storedHash = $details['hash'] ?? null;
            $storedPrevHash = $details['previous_hash'] ?? null;

            if ($storedPrevHash !== $expectedPrevHash) {
                return [
                    'is_intact' => false,
                    'tampered_log_id' => $log->id,
                    'message' => "Ruptura de cadena en el registro #{$log->id}. El hash previo no coincide.",
                ];
            }

            // Recalcular hash
            $detailsForCheck = $details;
            unset($detailsForCheck['hash']);

            $signatureSource = implode('|', [
                $storedPrevHash,
                (string) $log->user_id,
                $log->action,
                $log->entity_type,
                (string) $log->entity_id,
                json_encode($detailsForCheck, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);

            $calculatedHash = hash('sha256', $signatureSource);

            if ($calculatedHash !== $storedHash) {
                return [
                    'is_intact' => false,
                    'tampered_log_id' => $log->id,
                    'message' => "Alteración de contenido detectada en el registro #{$log->id}. El hash criptográfico ha sido vulnerado.",
                ];
            }

            $expectedPrevHash = $storedHash;
        }

        return [
            'is_intact' => true,
            'tampered_log_id' => null,
            'message' => 'Cadena de auditoría 100% íntegra y verificada criptográficamente.',
        ];
    }
}
