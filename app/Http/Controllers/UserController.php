<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UserCreateRequest;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Models\AktivitasLog;
use App\Models\Cabang;
use App\Models\Role;
use App\Models\User;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $resolved = $this->resolveFilter($request, 'users', [
                'show_cabang' => true,
                'show_tanggal' => false,
                'force_scope_cabang_user' => true,
            ]);
            $query = User::with(['cabang', 'role']);
            $this->applyFilter($query, $resolved);
            $users = $query->orderBy('nama', 'asc')->get();
            $filterFormAction = route('users.index');
            $filterView = $this->buildViewFilterVars($resolved);
            return view('modules.users.index', array_merge(compact(
                'users',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data user: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $cabang = Cabang::orderBy('nama_cabang')->get();
        $role = Role::where('is_active', '1')->orderBy('nama_role')->get();
        return view('modules.users.create', compact('cabang', 'role'));
    }

    public function store(UserCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $data['force_change_password'] = true;
            $user = User::create($data);

            AktivitasLog::record(
                'CREATE_USER',
                'Menambah user baru: ' . $user->nama . ' (' . $user->email . ')',
                $user
            );

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan. User wajib mengganti password pada login pertama.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'User gagal ditambahkan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $cabang = Cabang::orderBy('nama_cabang')->get();
        $role = Role::orderBy('nama_role')->get();
        return view('modules.users.edit', compact('user', 'cabang', 'role'));
    }

    public function update(UserUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $data = $request->validated();

            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
                $data['force_change_password'] = true;
            }

            $user->update($data);

            AktivitasLog::record(
                'UPDATE_USER',
                'Memperbarui user: ' . $user->nama . ' (' . $user->email . ')',
                $user
            );

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'User gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            if (auth()->id() === $user->id) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Tidak dapat menghapus user yang sedang login.');
            }
            $snapshot = $user->nama . ' (' . $user->email . ')';
            $user->delete();

            AktivitasLog::record(
                'DELETE_USER',
                'Menghapus user: ' . $snapshot
            );

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'User gagal dihapus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $user = User::with(['cabang', 'role', 'role.permissions'])->findOrFail($id);
            $aktivitasTerakhir = AktivitasLog::query()
                ->where('users_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->get();
            return view("modules.users.show", compact('user', 'aktivitasTerakhir'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail user: ' . $e->getMessage());
        }
    }
}
