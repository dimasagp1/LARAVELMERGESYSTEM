<?php

namespace App\Modules\GeneralAffair\Services;

use App\Models\User;
use App\Modules\GeneralAffair\Models\CleaningCheckpoint;
use App\Modules\GeneralAffair\Models\CleaningCheckpointLog;
use App\Modules\GeneralAffair\Models\SecurityCheckpoint;
use App\Modules\GeneralAffair\Models\SecurityCheckpointLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PatrolService
{
    public function __construct(
        protected GaNumberingService $numberingService
    ) {}

    /**
     * Get active security checkpoints by plant.
     *
     * @return Collection<int, SecurityCheckpoint>
     */
    public function getSecurityCheckpoints(?int $plantId = null): Collection
    {
        $query = SecurityCheckpoint::with(['plant', 'logs' => fn ($q) => $q->latest('scanned_at')->limit(1)])
            ->where('is_active', true)
            ->orderBy('code');

        if ($plantId) {
            $query->where('plant_id', $plantId);
        }

        return $query->get();
    }

    /**
     * Get paginated security logs.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<SecurityCheckpointLog>
     */
    public function getSecurityLogs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SecurityCheckpointLog::with(['checkpoint.plant', 'user'])
            ->orderByDesc('scanned_at');

        if (! empty($filters['plant_id'])) {
            $query->whereHas('checkpoint', fn ($q) => $q->where('plant_id', (int) $filters['plant_id']));
        }

        if (isset($filters['is_anomaly']) && $filters['is_anomaly'] !== '') {
            $query->where('is_anomaly', (bool) $filters['is_anomaly']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('scanned_at', $filters['date']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Record a security checkpoint scan.
     *
     * @param  array<string, mixed>  $data
     */
    public function logSecurityScan(array $data, User $officer): SecurityCheckpointLog
    {
        return DB::transaction(function () use ($data, $officer) {
            $checkpoint = null;
            if (! empty($data['qr_token'])) {
                $checkpoint = SecurityCheckpoint::where('qr_token', $data['qr_token'])->first();
            }

            if (! $checkpoint && ! empty($data['checkpoint_id'])) {
                $checkpoint = SecurityCheckpoint::find($data['checkpoint_id']);
            }

            if (! $checkpoint) {
                throw new \InvalidArgumentException('Checkpoint Keamanan tidak ditemukan atau QR token tidak valid.');
            }

            $isAnomaly = ! empty($data['is_anomaly']);
            $durationSeconds = (int) ($data['duration_seconds'] ?? 0);

            $status = $data['status'] ?? ($isAnomaly ? 'Anomali / Insiden' : 'Aman');
            if ($isAnomaly && $status === 'Aman') {
                $status = 'Anomali / Insiden';
            }

            return SecurityCheckpointLog::create([
                'checkpoint_id' => $checkpoint->id,
                'user_id' => $officer->id,
                'scanned_at' => $data['scanned_at'] ?? Carbon::now(),
                'duration_seconds' => $durationSeconds,
                'status' => $status,
                'is_anomaly' => $isAnomaly,
                'anomaly_notes' => $data['anomaly_notes'] ?? null,
                'evidence_photo_path' => $data['evidence_photo_path'] ?? null,
            ]);
        });
    }

    /**
     * Get active cleaning checkpoints by plant.
     *
     * @return Collection<int, CleaningCheckpoint>
     */
    public function getCleaningCheckpoints(?int $plantId = null): Collection
    {
        $query = CleaningCheckpoint::with(['plant', 'logs' => fn ($q) => $q->latest('scanned_at')->limit(1)])
            ->where('is_active', true)
            ->orderBy('code');

        if ($plantId) {
            $query->where('plant_id', $plantId);
        }

        return $query->get();
    }

    /**
     * Get paginated cleaning logs.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<CleaningCheckpointLog>
     */
    public function getCleaningLogs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CleaningCheckpointLog::with(['checkpoint.plant', 'user'])
            ->orderByDesc('scanned_at');

        if (! empty($filters['plant_id'])) {
            $query->whereHas('checkpoint', fn ($q) => $q->where('plant_id', (int) $filters['plant_id']));
        }

        if (isset($filters['is_sanitized']) && $filters['is_sanitized'] !== '') {
            $query->where('is_sanitized', (bool) $filters['is_sanitized']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('scanned_at', $filters['date']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Record a cleaning / sanitasi checkpoint scan.
     *
     * @param  array<string, mixed>  $data
     */
    public function logCleaningScan(array $data, User $cleaner): CleaningCheckpointLog
    {
        return DB::transaction(function () use ($data, $cleaner) {
            $checkpoint = null;
            if (! empty($data['qr_token'])) {
                $checkpoint = CleaningCheckpoint::where('qr_token', $data['qr_token'])->first();
            }

            if (! $checkpoint && ! empty($data['checkpoint_id'])) {
                $checkpoint = CleaningCheckpoint::find($data['checkpoint_id']);
            }

            if (! $checkpoint) {
                throw new \InvalidArgumentException('Checkpoint Kebersihan tidak ditemukan atau QR token tidak valid.');
            }

            return CleaningCheckpointLog::create([
                'checkpoint_id' => $checkpoint->id,
                'user_id' => $cleaner->id,
                'scanned_at' => $data['scanned_at'] ?? Carbon::now(),
                'is_sanitized' => (bool) ($data['is_sanitized'] ?? true),
                'checklist_json' => $data['checklist_json'] ?? [
                    'lantai' => true,
                    'tempat_sampah' => true,
                    'aroma' => true,
                    'refill_sabun' => true,
                ],
                'photo_path' => $data['photo_path'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Get patrol overview summary.
     *
     * @return array<string, mixed>
     */
    public function getPatrolSummary(?int $plantId = null): array
    {
        $today = Carbon::today();

        $secQuery = SecurityCheckpointLog::whereDate('scanned_at', $today);
        if ($plantId) {
            $secQuery->whereHas('checkpoint', fn ($q) => $q->where('plant_id', $plantId));
        }

        $cleanQuery = CleaningCheckpointLog::whereDate('scanned_at', $today);
        if ($plantId) {
            $cleanQuery->whereHas('checkpoint', fn ($q) => $q->where('plant_id', $plantId));
        }

        $totalSecurityPoints = SecurityCheckpoint::when($plantId, fn ($q) => $q->where('plant_id', $plantId))->count();
        $scannedSecurityPoints = (clone $secQuery)->distinct('checkpoint_id')->count('checkpoint_id');
        $anomaliesCount = (clone $secQuery)->where('is_anomaly', true)->count();

        $totalCleaningPoints = CleaningCheckpoint::when($plantId, fn ($q) => $q->where('plant_id', $plantId))->count();
        $scannedCleaningPoints = (clone $cleanQuery)->distinct('checkpoint_id')->count('checkpoint_id');
        $sanitizedCount = (clone $cleanQuery)->where('is_sanitized', true)->count();

        return [
            'security' => [
                'total_checkpoints' => $totalSecurityPoints,
                'scanned_today' => $scannedSecurityPoints,
                'compliance_percent' => $totalSecurityPoints > 0 ? round(($scannedSecurityPoints / $totalSecurityPoints) * 100, 1) : 100.0,
                'anomalies_count' => $anomaliesCount,
            ],
            'cleaning' => [
                'total_checkpoints' => $totalCleaningPoints,
                'scanned_today' => $scannedCleaningPoints,
                'compliance_percent' => $totalCleaningPoints > 0 ? round(($scannedCleaningPoints / $totalCleaningPoints) * 100, 1) : 100.0,
                'sanitized_rate' => $scannedCleaningPoints > 0 ? round(($sanitizedCount / $scannedCleaningPoints) * 100, 1) : 100.0,
            ],
        ];
    }
}
