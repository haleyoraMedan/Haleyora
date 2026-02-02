<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanRusak;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $laporans = LaporanRusak::with(['mobil', 'fotos'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('laporan.index', compact('laporans'));
    }
}
