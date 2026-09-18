<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permissions\PermissionCreateRequest;
use App\Http\Requests\Permissions\PermissionUpdateRequest;
use App\Models\Permission;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $permissions = Permission::withCount('roles')->orderBy('kode_permission')->get();
            $hideFilterBar = true;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('permissions.index');
            return view('modules.permissions.index', compact(
                'permissions',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data permission: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('modules.permissions.create');
    }

    public function store(PermissionCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            Permission::create($request->validated());
            DB::commit();
            return redirect()->route('permissions.index')->with('success', 'Permission berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Permission gagal ditambahkan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('modules.permissions.edit', compact('permission'));
    }

    public function update(PermissionUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $permission = Permission::findOrFail($id);
            $permission->update($request->validated());
            DB::commit();
            return redirect()->route('permissions.index')->with('success', 'Permission berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Permission gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $permission = Permission::findOrFail($id);
            $permission->roles()->sync([]);
            $permission->delete();

            DB::commit();
            return redirect()->route('permissions.index')->with('success', 'Permission berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Permission gagal dihapus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $permission = Permission::with('roles')->withCount('roles')->findOrFail($id);
            return view("modules.permissions.show", compact('permission'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail permission: ' . $e->getMessage());
        }
    }
}
