<?php

namespace App\Support;

use App\Mail\AdminActionNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AdminActionNotifier
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function notify(string $title, array $details = [], ?User $actor = null): void
    {
        $recipient = config('admin.email');

        if (! is_string($recipient) || trim($recipient) === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new AdminActionNotification(
                title: $title,
                details: $details,
                actor: $this->actorPayload($actor),
            ));
        } catch (Throwable $exception) {
            Log::warning('Admin action notification could not be sent.', [
                'title' => $title,
                'recipient' => $recipient,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function actorPayload(?User $actor): ?array
    {
        if (! $actor) {
            return null;
        }

        return [
            'id' => $actor->id,
            'name' => $actor->name,
            'email' => $actor->email,
            'role' => $actor->role,
        ];
    }
}
