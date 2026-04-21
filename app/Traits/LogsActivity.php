<?php

namespace App\Traits;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Record user activity.
     *
     * @param string $action Action name (e.g., 'booking_created')
     * @param Model|null $subject Related model (e.g., $booking)
     * @param array $properties Additional data to record
     * @param Model|null $user The user performing the action (optional)
     * @return void
     */
    public function logActivity(string $action, ?Model $subject = null, array $properties = [], ?Model $user = null)
    {
        $user = $user ?: auth()->user();

        Log::create([
            'company_id' => $user && isset($user->company_id) ? $user->company_id : null,
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }
}
