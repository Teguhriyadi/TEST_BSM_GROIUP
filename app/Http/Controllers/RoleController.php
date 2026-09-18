<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\RoleCreateRequest;
use App\Http\Requests\Roles\RoleUpdateRequest;
use App\Models\AktivitasLog;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $roles = Role::withCount('users')->with('permissions')->orderBy('nama_role', 'asc')->get();
            $hideFilterBar = true;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('roles.index');
            return view('modules.roles.index', compact(
                'roles',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data role: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $permissions = Permission::orderBy('kode_permission')->get();
        return view('modules.roles.create', compact('permissions'));
    }

    public function store(RoleCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::create($request->validated());
            $permissionIds = collect($request->input('permissions', []))->filter()->values()->all();
            $role->permissions()->sync($permissionIds);

            AktivitasLog::record(
                'CREATE_ROLE',
                'Menambah role baru: ' . $role->nama_role . ' (' . $role->kode_role . ') dengan ' . count($permissionIds) . ' permission',
                $role
            );

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Role gagal ditambahkan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('kode_permission')->get();
        return view('modules.roles.edit', compact('role', 'permissions'));
    }

    public function update(RoleUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);
            $role->update($request->safe()->except('permissions'));

            $permissionIds = collect($request->input('permissions', []))->filter()->values()->all();
            $role->permissions()->sync($permissionIds);

            AktivitasLog::record(
                'UPDATE_ROLE',
                'Memperbarui role: ' . $role->nama_role . ' (' . $role->kode_role . ') — ' . count($permissionIds) . ' permission',
                $role
            );

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Role gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);
            if ($role->users()->count() > 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh beberapa User.');
            }
            $snapshot = $role->nama_role . ' (' . $role->kode_role . ')';
            $role->permissions()->sync([]);
            $role->delete();

            AktivitasLog::record(
                'DELETE_ROLE',
                'Menghapus role: ' . $snapshot
            );

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Role gagal dihapus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $role = Role::with(['permissions', 'users:id,nama,email,role_id,is_active'])->withCount('users')->findOrFail($id);
            return view("modules.roles.show", compact('role'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail role: ' . $e->getMessage());
        }
    }
}
