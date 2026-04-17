<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use CheckRole;

    /**
     * GET semua user + search (ADMIN)
     */
    public function index(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $search = $request->query('q');

        $users = User::with('penempatan')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%$search%")
                      ->orWhere('nip', 'like', "%$search%")
                      ->orWhere('role', 'like', "%$search%");
                });
            })
            // jika diminta, tampilkan hanya data yang sudah dihapus
            ->when($request->query('show_deleted') == '1', function($q){
                $q->whereNotNull('is_deleted');
            })
            ->orderBy('username')
            ->get();

        $authUser = auth()->user();
        return view('user.index', compact('users', 'search', 'authUser'));
    }

    /**
     * GET form edit user (ADMIN)
     */
    public function edit(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $user = User::findOrFail($id);

        // Only show mobils that share the same penempatan as this user
        $penempatanId = $user->penempatan_id;
        if ($penempatanId) {
            $mobilsQuery = \App\Models\Mobil::where('penempatan_id', $penempatanId)
                ->orderBy('no_polisi');

            // exclude mobils already assigned as mobil_id to other users
            $assigned = \App\Models\User::whereNotNull('mobil_id')
                ->where('mobil_id', '<>', $user->mobil_id)
                ->pluck('mobil_id')
                ->toArray();

            if (!empty($assigned)) {
                $mobilsQuery->whereNotIn('id', $assigned);
            }

            $mobils = $mobilsQuery->get();
        } else {
            $mobils = collect();
        }

        $authUser = auth()->user();
        return view('user.edit', compact('user', 'authUser', 'mobils'));
    }

    /**
     * UPDATE user (ADMIN)
     */
    public function update(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nip'           => 'required|string|max:30|unique:users,nip,' . $id,
            'username'      => 'required|string|max:50|unique:users,username,' . $id,
            'password'      => 'nullable|string|min:6',
            'role'          => 'required|in:admin,pegawai',
            'penempatan_id' => 'nullable|integer',
            'mobil_id' => [
                'nullable',
                Rule::exists('mobil', 'id')->where(function ($query) use ($user) {
                    $query->where('penempatan_id', $user->penempatan_id);
                }),
                Rule::unique('users', 'mobil_id')->ignore($id),
            ],
        ]);

        // password otomatis di-hash via mutator
        $user->update($validated);

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui');
    }

    /**
     * DELETE user (soft delete dengan datetime) (ADMIN)
     */
    public function destroy(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $user = User::findOrFail($id);
        $user->update(['is_deleted' => Carbon::now()]);

        return redirect()->back()->with('success', 'User berhasil dihapus');
    }

    /**
     * BULK SOFT DELETE USERS
     */
    public function bulkDestroy(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu user untuk dihapus');
        }

        $deleted = 0;
        foreach ($ids as $id) {
            try {
                $u = User::find($id);
                if (!$u) continue;
                $u->update(['is_deleted' => Carbon::now()]);
                $deleted++;
            } catch (\Exception $e) {
                continue;
            }
        }

        return redirect()->back()->with('success', "$deleted user berhasil dihapus");
    }

    /**
     * RESTORE user (ADMIN)
     */
    public function restore(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $user = User::findOrFail($id);
        $user->update(['is_deleted' => null]);

        return redirect()->back()->with('success', 'User berhasil direstore');
    }
}
