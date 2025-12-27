<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PemakaianMobil;
use App\Jobs\SendPushNotification;

class TestPushController extends Controller
{
    // Test endpoint: trigger push notification manually
    public function testPush(Request $request)
    {
        try {
            $pending = PemakaianMobil::where('status', 'pending')->count();
            
            $payload = [
                'title' => 'Test Notifikasi Push',
                'body' => "Test push notification - {$pending} pemakaian pending.",
                'pending_count' => $pending,
                'url' => url('/admin/pemakaian')
            ];

            // dispatch job to send push
            SendPushNotification::dispatch($payload);

            return response()->json([
                'success' => true,
                'message' => 'Push notification job dispatched. Check queue worker logs.',
                'payload' => $payload
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show test page
    public function showTestPage()
    {
        $pending = PemakaianMobil::where('status', 'pending')->count();
        $subscriptions = \App\Models\PushSubscription::count();
        
        return view('test-push', compact('pending', 'subscriptions'));
    }
}
