<?php

namespace App\AuditDrivers;

use OwenIt\Auditing\Contracts\Audit;
use OwenIt\Auditing\Contracts\AuditDriver;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit as MakeAudit;

class CustomAuditLog implements AuditDriver
{
    /**
     * {@inheritdoc}
     */
    public function audit(Auditable $model): ?Audit
    {
        $audit = $model->toAudit();
        if (empty($audit['old_values']) and empty($audit['new_values'])) {
            return null;
        }
        return MakeAudit::create($audit);
    }

    /**
     * {@inheritdoc}
     */
    public function prune(Auditable $model): bool
    {
        if (($threshold = $model->getAuditThreshold()) > 0) {
            $forRemoval = $model->audits()
                ->latest()
                ->get()
                ->slice($threshold)
                ->pluck('id');

            if (!$forRemoval->isEmpty()) {
                return $model->audits()
                    ->whereIn('id', $forRemoval)
                    ->delete() > 0;
            }
        }

        return false;
    }
}
