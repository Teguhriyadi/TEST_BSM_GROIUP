<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pinjaman\DokumenPinjamanUploadRequest;
use App\Http\Requests\Pinjaman\DokumenPinjamanVerifikasiRequest;
use App\Http\Requests\Pinjaman\PinjamanCreateRequest;
use App\Http\Requests\Pinjaman\PinjamanUpdateRequest;
use App\Http\Requests\Pinjaman\PinjamanVerifikasiPengajuanRequest;
use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\JenisPinjaman;
use App\Models\MasterDokumen;
use App\Models\Pinjaman;
use App\Models\PinjamanDokumen;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PinjamanController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            if (Auth::check() && Auth::user()->hasRole('Anggota')) {
                $anggotaAkun = Anggota::where('users_id', Auth::id())->first();
                if (! $anggotaAkun) {
                    return redirect(route('dashboard'))->with('warning', 'Akun anggota Anda belum terhubung dengan data nasabah. Hubungi teller koperasi.');
                }
                $pinjaman = Pinjaman::with(['anggota', 'cabang', 'jenisPinjaman', 'dokumen.masterDokumen'])
                    ->where('anggota_id', $anggotaAkun->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $cabangFilterList = collect();
                $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
                $isAnggotaFilter = true;
                $moduleFilterSummary = null;
                $filterFormAction = route('pinjaman.index');
                $hideFilterBar = true;
                return view("modules.pinjaman.index", compact(
                    'pinjaman',
                    'cabangFilterList',
                    'currentModuleFilter',
                    'isAnggotaFilter',
                    'moduleFilterSummary',
                    'filterFormAction',
                    'hideFilterBar',
                ));
            }

            $resolved = $this->resolveFilter($request, 'pinjaman', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tgl_pengajuan',
                'force_scope_cabang_user' => true,
                'custom_filters' => [
                    'status_pinjaman' => [
                        'label' => 'Status Pinjaman',
                        'placeholder' => 'Semua Status',
                        'options' => [
                            ['value' => 'menunggu_verifikasi', 'label' => 'Menunggu Verifikasi'],
                            ['value' => 'disetujui', 'label' => 'Disetujui (Belum Dicairkan)'],
                            ['value' => 'ditolak', 'label' => 'Ditolak Pengajuan'],
                            ['value' => 'dicairkan', 'label' => 'Dicairkan (Belum Berjalan)'],
                            ['value' => 'berjalan', 'label' => 'Berjalan (Sedang Diangsur)'],
                            ['value' => 'lunas', 'label' => 'Lunas'],
                            ['value' => 'ditolak_pencairan', 'label' => 'Ditolak Pencairan'],
                        ],
                    ],
                ],
            ]);
            $query = Pinjaman::with(['anggota', 'cabang', 'jenisPinjaman', 'dokumen.masterDokumen']);
            $this->applyFilter($query, $resolved);
            $statusPinjaman = $resolved['filter']['status_pinjaman'] ?? null;
            if ($statusPinjaman !== null && $statusPinjaman !== '') {
                $query->where('status', $statusPinjaman);
            }
            $pinjaman = $query->orderBy('tgl_pengajuan', 'desc')->get();
            $filterFormAction = route('pinjaman.index');
            $filterView = $this->buildViewFilterVars($resolved);
            return view("modules.pinjaman.index", array_merge(compact(
                'pinjaman',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data pinjaman: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (Auth::check() && Auth::user()->hasRole('Anggota')) {
            $anggotaAkun = Anggota::where('users_id', Auth::id())->first();
            if (! $anggotaAkun || $anggotaAkun->status !== 'aktif') {
                return redirect(route('dashboard'))->with('warning', 'Anda tidak dapat mengajukan pinjaman saat ini. Hubungi teller koperasi.');
            }
            $anggota = Anggota::where('id', $anggotaAkun->id)->where('status', 'aktif')->get(['id', 'nama', 'no_anggota']);
            $cabang = Cabang::where('id', $anggotaAkun->cabang_id)->get(['id', 'nama_cabang']);
        } else {
            $anggota = Anggota::where('status', 'aktif')->get(['id', 'nama', 'no_anggota']);
            $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);
        }
        $jenisPinjaman = JenisPinjaman::with('dokumenPersyaratanWajib:id')->get(['id', 'nama_jenis']);
        return view("modules.pinjaman.create", compact('anggota', 'cabang', 'jenisPinjaman'));
    }

    private function seedDokumenWajibForPinjaman(Pinjaman $pinjaman): void
    {
        $daftarWajib = $pinjaman->jenisPinjaman?->daftar_master_dokumen_id_wajib ?? [];
        $now = now();
        $rows = [];
        foreach (array_values(array_unique(array_map('strval', $daftarWajib))) as $masterDokumenId) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'pinjaman_id' => $pinjaman->id,
                'master_dokumen_id' => $masterDokumenId,
                'status' => 'belum_diunggah',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows !== []) {
            DB::table('pinjaman_dokumen')->upsert(
                $rows,
                ['pinjaman_id', 'master_dokumen_id'],
                ['updated_at' => $now],
            );
        }
    }

    public function store(PinjamanCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $pinjaman = Pinjaman::create($request->validated());
            $pinjaman->loadMissing('jenisPinjaman.dokumenPersyaratanWajib');
            $this->seedDokumenWajibForPinjaman($pinjaman);
            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Pengajuan pinjaman berhasil disimpan. Silakan unggah dokumen persyaratan berikutnya.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);
        $anggota = Anggota::where('status', 'aktif')->get(['id', 'nama', 'no_anggota']);
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);
        $jenisPinjaman = JenisPinjaman::with('dokumenPersyaratanWajib:id')->get(['id', 'nama_jenis']);
        return view("modules.pinjaman.edit", compact('pinjaman', 'anggota', 'cabang', 'jenisPinjaman'));
    }

    public function update(PinjamanUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pinjaman = Pinjaman::findOrFail($id);
            $jenisIdLama = (string) $pinjaman->jenis_pinjaman_id;
            $pinjaman->update($request->validated());
            $pinjaman->loadMissing('jenisPinjaman.dokumenPersyaratanWajib');
            if ($jenisIdLama !== (string) $pinjaman->jenis_pinjaman_id) {
                $this->seedDokumenWajibForPinjaman($pinjaman);
            }
            DB::commit();
            return redirect()->route('pinjaman.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pinjaman = Pinjaman::with([
            'anggota:id,nama,no_anggota',
            'cabang:id,nama_cabang',
            'jenisPinjaman.dokumenPersyaratanWajib',
            'dokumen.masterDokumen',
            'dokumen.uploader:id,name',
            'dokumen.verifikator:id,name',
        ])->findOrFail($id);

        if (Auth::check() && Auth::user()->hasRole('Anggota')) {
            $anggotaAkun = Anggota::where('users_id', Auth::id())->first();
            if (! $anggotaAkun || (string) $pinjaman->anggota_id !== (string) $anggotaAkun->id) {
                return redirect(route('pinjaman.index'))->with('error', 'Anda tidak memiliki izin mengakses pinjaman ini.');
            }
        }

        $daftarWajibId = $pinjaman->jenisPinjaman?->daftar_master_dokumen_id_wajib ?? [];
        $masterDokumenWajib = MasterDokumen::whereIn('id', $daftarWajibId)
            ->where('is_active', true)
            ->orderBy('nama_dokumen', 'asc')
            ->get(['id', 'kode_dokumen', 'nama_dokumen', 'deskripsi', 'format_diperbolehkan']);

        $existingByMaster = $pinjaman->dokumen->keyBy(fn ($d) => (string) $d->master_dokumen_id);
        $dokumenWajibList = collect();
        foreach ($masterDokumenWajib as $md) {
            $existing = $existingByMaster->get((string) $md->id);
            if (! $existing) {
                $existing = new PinjamanDokumen([
                    'pinjaman_id' => $pinjaman->id,
                    'master_dokumen_id' => $md->id,
                    'status' => 'belum_diunggah',
                ]);
                $existing->setRelation('masterDokumen', $md);
            }
            $dokumenWajibList->push($existing);
        }

        return view("modules.pinjaman.show", compact('pinjaman', 'dokumenWajibList'));
    }

    public function uploadDokumen(DokumenPinjamanUploadRequest $request, $pinjamanId, $masterDokumenId)
    {
        $pinjaman = Pinjaman::findOrFail($pinjamanId);

        if (Auth::check() && Auth::user()->hasRole('Anggota')) {
            $anggotaAkun = Anggota::where('users_id', Auth::id())->first();
            if (! $anggotaAkun || (string) $pinjaman->anggota_id !== (string) $anggotaAkun->id) {
                return redirect(route('pinjaman.index'))->with('error', 'Anda tidak memiliki izin mengakses pinjaman ini.');
            }
        }

        $masterDokumen = MasterDokumen::findOrFail($masterDokumenId);
        $daftarWajibId = $pinjaman->jenisPinjaman?->daftar_master_dokumen_id_wajib ?? [];
        if (! in_array((string) $masterDokumenId, $daftarWajibId, true)) {
            return redirect()->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Dokumen tersebut bukan merupakan persyaratan wajib untuk pinjaman ini.');
        }

        DB::beginTransaction();
        try {
            $pinjamanDokumen = PinjamanDokumen::firstOrCreate(
                [
                    'pinjaman_id' => $pinjaman->id,
                    'master_dokumen_id' => $masterDokumen->id,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'status' => 'belum_diunggah',
                ],
            );

            $oldPath = $pinjamanDokumen->file_path;
            $file = $request->file('dokumen');
            $originalName = $file->getClientOriginalName();
            $size = (int) $file->getSize();
            $mime = (string) $file->getMimeType();
            $directory = 'dokumen-pinjaman/' . $pinjaman->id;

            $ext = strtolower((string) $file->getClientOriginalExtension());
            $isPdf = $ext === 'pdf' || $mime === 'application/pdf';

            if ($isPdf) {
                $filename = Str::random(40) . '.pdf';
                $relativePath = (string) $file->storeAs(rtrim($directory, '/'), $filename, 'public');
            } else {
                $relativePath = compressImage($file, $directory, 80, 1600);
                if ($relativePath === null || trim($relativePath) === '') {
                    throw new \RuntimeException('Gagal menyimpan file dokumen. Silakan coba kembali.');
                }
            }

            $pinjamanDokumen->update([
                'uploader_users_id' => Auth::id(),
                'file_path' => $relativePath,
                'nama_file_asli' => $originalName,
                'ukuran_file' => $size,
                'tipe_mime' => $mime,
                'status' => 'menunggu_verifikasi',
                'catatan' => null,
                'verifikator_users_id' => null,
                'tgl_verifikasi' => null,
            ]);

            if (
                $oldPath !== null
                && trim((string) $oldPath) !== ''
                && $oldPath !== $relativePath
                && Storage::disk('public')->exists($oldPath)
            ) {
                try {
                    Storage::disk('public')->delete($oldPath);
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            if ($pinjaman->status !== 'diajukan') {
                $pinjaman->update(['status' => 'diajukan']);
            }

            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Dokumen ' . e($masterDokumen->nama_dokumen) . ' berhasil diunggah dan menunggu verifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Dokumen gagal diunggah: ' . $e->getMessage());
        }
    }

    public function verifikasiDokumen(DokumenPinjamanVerifikasiRequest $request, $pinjamanId, $pinjamanDokumenId)
    {
        $pinjaman = Pinjaman::findOrFail($pinjamanId);
        $pinjamanDokumen = PinjamanDokumen::where('pinjaman_id', $pinjaman->id)
            ->where('id', $pinjamanDokumenId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['verifikator_users_id'] = Auth::id();
            $data['tgl_verifikasi'] = now();
            if (! in_array($data['status'], ['ditolak', 'perlu_diperbaiki'], true)) {
                $data['catatan'] = $data['catatan'] ?? null;
            }
            $pinjamanDokumen->update($data);
            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Status verifikasi dokumen berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Verifikasi dokumen gagal: ' . $e->getMessage());
        }
    }

    public function verifikasiPengajuan(PinjamanVerifikasiPengajuanRequest $request, $pinjamanId)
    {
        $pinjaman = Pinjaman::with([
            'jenisPinjaman.dokumenPersyaratanWajib',
            'dokumen.masterDokumen',
        ])->findOrFail($pinjamanId);

        DB::beginTransaction();
        try {
            if (! $pinjaman->semuaDokumenWajibDisetujui()) {
                $hambatan = $pinjaman->hambatan_verifikasi;
                $msg = 'Pengajuan belum bisa diverifikasi.';
                if ($hambatan !== []) {
                    $msg .= ' Hambatan: ' . implode(' ', $hambatan);
                }
                return redirect()
                    ->route('pinjaman.show', $pinjaman->id)
                    ->with('error', $msg);
            }
            if ($pinjaman->status !== 'diajukan') {
                return redirect()
                    ->route('pinjaman.show', $pinjaman->id)
                    ->with('error', 'Pengajuan hanya bisa diverifikasi ketika status "Diajukan".');
            }
            $pinjaman->update(['status' => 'diverifikasi']);
            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Pengajuan pinjaman telah diverifikasi dan siap masuk ke proses persetujuan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Gagal melakukan verifikasi pengajuan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pinjaman = Pinjaman::with('dokumen')->findOrFail($id);
            foreach ($pinjaman->dokumen as $dok) {
                if (! empty($dok->file_path) && Storage::disk('public')->exists($dok->file_path)) {
                    try {
                        Storage::disk('public')->delete($dok->file_path);
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
            $pinjaman->delete();
            DB::commit();
            return redirect()->route('pinjaman.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }
}
