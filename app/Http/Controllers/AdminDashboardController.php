<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mobil;
use App\Models\PemakaianMobil;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $counts = [
            'users' => User::count(),
            'mobils' => Mobil::whereNull('is_deleted')->count(),
            'pending_pemakaian' => PemakaianMobil::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('counts'));
    }
}
