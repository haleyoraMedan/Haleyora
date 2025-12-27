<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        $authUser = auth()->user();
        return view('user.edit', compact('user', 'authUser'));
    }

    /**
     * UPDATE user (ADMIN)
     */
    public function update(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nip'           => 'nullable|string|max:30',
            'username'      => 'required|string|max:50|unique:users,username,' . $id,
            'password'      => 'nullable|string|min:6',
            'role'          => 'required|in:admin,pegawai',
            'penempatan_id' => 'nullable|integer',
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
