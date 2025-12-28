<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PushSubscription;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        // try to use minishlink/web-push if available
        if (!class_exists('Minishlink\WebPush\WebPush')) {
            return; // library not installed
        }

        $subscriptions = PushSubscription::all();
        if ($subscriptions->isEmpty()) return;

        $auth = [
            'VAPID' => [
                'subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ],
        ];

        $webPush = new \Minishlink\WebPush\WebPush($auth);

        $payloadJson = json_encode($this->payload);

        $options = [
            // Time to live in seconds and high urgency to make delivery faster
            'TTL' => 60,
            'urgency' => 'high'
        ];

        foreach ($subscriptions as $s) {
            $subscription = [
                'endpoint' => $s->endpoint,
                'keys' => [
                    'p256dh' => $s->public_key,
                    'auth' => $s->auth_token,
                ]
            ];

            try {
                $webPush->queueNotification($subscription, $payloadJson, $options);
            } catch (\Exception $e) {
                // continue on error, optionally log
                if (function_exists('logger')) logger('Push queue error: ' . $e->getMessage());
            }
        }

        try {
            foreach ($webPush->flush() as $report) {
                // optionally handle reports (could log failures)
            }
        } catch (\Exception $e) {
            if (function_exists('logger')) logger('Push flush error: ' . $e->getMessage());
        }
    }
}
