<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AuditLog Model
 *
 * Tracks changes and events in the system
 */
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'session_id',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create audit log entry
     */
    public static function logEvent(
        string $event,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'session_id' => request()->hasSession() ? request()->session()->getId() : null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log model creation
     */
    public static function logCreated(Model $model, ?array $metadata = null): self
    {
        return self::logEvent('created', $model, null, $model->getAttributes(), $metadata);
    }

    /**
     * Log model update
     */
    public static function logUpdated(Model $model, array $oldValues, ?array $metadata = null): self
    {
        return self::logEvent('updated', $model, $oldValues, $model->getAttributes(), $metadata);
    }

    /**
     * Log model deletion
     */
    public static function logDeleted(Model $model, ?array $metadata = null): self
    {
        return self::logEvent('deleted', $model, $model->getAttributes(), null, $metadata);
    }

    /**
     * Log custom event
     */
    public static function logCustom(string $event, ?array $metadata = null): self
    {
        return self::logEvent($event, null, null, null, $metadata);
    }
}
