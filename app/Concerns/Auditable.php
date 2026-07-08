<?php

namespace App\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Writes a row to audit_logs on create/update/delete via Eloquent model events.
 *
 * Relies on Eloquent's event ordering: the "updated" event fires after the
 * update query runs but BEFORE syncOriginal() is called (that happens later,
 * back in Model::save()). So inside the "updated" listener, getOriginal()
 * still holds the pre-save values and getChanges() holds what was just
 * written — no need to manually snapshot state in an "updating" listener.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model): void {
            static::recordAudit($model, 'created', [], $model->auditableAttributes($model->getAttributes()));
        });

        static::updated(function (self $model): void {
            $changed = $model->auditableAttributes($model->getChanges());

            if (empty($changed)) {
                return;
            }

            $original = $model->auditableAttributes(
                collect($model->getOriginal())->only(array_keys($changed))->all()
            );

            static::recordAudit($model, 'updated', $original, $changed);
        });

        static::deleted(function (self $model): void {
            static::recordAudit($model, 'deleted', $model->auditableAttributes($model->getOriginal()), []);
        });
    }

    /**
     * Columns never written to old_values/new_values — secrets and noise
     * that isn't meaningful in a change log. Models may override
     * $auditExcludes to add their own (e.g. Worker's lat/lng churn).
     */
    protected function auditableAttributes(array $attributes): array
    {
        $excluded = array_merge(['password', 'remember_token', 'updated_at'], $this->auditExcludes ?? []);

        return collect($attributes)->except($excluded)->all();
    }

    protected static function recordAudit(self $model, string $action, array $old, array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            // request() is unavailable outside an HTTP context (console, queue jobs).
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : Str::limit((string) request()->userAgent(), 255, ''),
        ]);
    }
}
