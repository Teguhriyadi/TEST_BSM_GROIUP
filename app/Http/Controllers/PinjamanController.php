<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $jenisPinjaman = JenisPinjaman::with(['dokumenPersyaratan' => function ($q) {
            $q->select(['master_dokumen.id', 'kode_dokumen', 'nama_dokumen', 'deskripsi', 'format_diperbolehkan']);
        }])->get([
            'id', 'nama_jenis', 'bunga_tahunan', 'tenor_minimal', 'tenor_maksimal', 'maksimal_plafon',
        ]);
        return view("modules.pinjaman.create", compact('anggota', 'cabang', 'jenisPinjaman'));
    }

    private function seedDokumenWajibForPinjaman(Pinjaman $pinjaman): void
    {
        $daftarSemua = $pinjaman->jenisPinjaman?->daftar_semua_master_dokumen_id ?? [];
        $now = now();
        $rows = [];
        foreach (array_values(array_unique(array_map('strval', $daftarSemua))) as $masterDokumenId) {
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

    private function hitungAngsuranPerBulan(float $jumlah, float $bungaTahunan, int $tenor): float
    {
        if ($tenor <= 0) {
            return round($jumlah, 2);
        }
        if ($bungaTahunan <= 0) {
            return round($jumlah / $tenor, 2);
        }
        $r = $bungaTahunan / 100 / 12;
        if ($r <= 0) {
            return round($jumlah / $tenor, 2);
        }
        $pow = pow(1 + $r, $tenor);
        $denominator = $pow - 1;
        if ($denominator <= 0) {
            return round($jumlah / $tenor, 2);
        }
        return round($jumlah * ($r * $pow) / $denominator, 2);
    }

    public function store(PinjamanCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $valid = $request->validated();

            $jenisPinjaman = isset($valid['jenis_pinjaman_id'])
                ? JenisPinjaman::find($valid['jenis_pinjaman_id'])
                : null;

            if (empty($valid['nomor_pinjaman'] ?? null)) {
                $tglPengajuan = null;
                if (! empty($valid['tgl_pengajuan'])) {
                    try {
                        $tglPengajuan = Carbon::parse($valid['tgl_pengajuan']);
                    } catch (\Throwable $e) {
                        $tglPengajuan = null;
                    }
                }
                $cabangId = (string) ($valid['cabang_id'] ?? (Auth::check() ? (string) (Auth::user()->cabang_id ?? '') : ''));
                $valid['nomor_pinjaman'] = Pinjaman::generateNomorPinjaman($cabangId, $tglPengajuan);
            }

            if (isset($jenisPinjaman) && $jenisPinjaman->exists) {
                if (empty($valid['bunga'] ?? null)) {
                    $valid['bunga'] = $jenisPinjaman->bunga_tahunan;
                }
                if (! empty($valid['jumlah_pinjaman']) && ! empty($valid['tenor'])) {
                    $jumlah = (float) $valid['jumlah_pinjaman'];
                    $tenor = (int) $valid['tenor'];
                    $bunga = (float) ($valid['bunga'] ?? $jenisPinjaman->bunga_tahunan);
                    $angsuranDefault = $this->hitungAngsuranPerBulan($jumlah, $bunga, $tenor);
                    if (empty($valid['angsuran_per_bulan']) || (float) $valid['angsuran_per_bulan'] <= 0) {
                        $valid['angsuran_per_bulan'] = $angsuranDefault;
                    }
                }
            }

            $pinjaman = Pinjaman::create($valid);
            $pinjaman->loadMissing('jenisPinjaman.dokumenPersyaratanWajib');
            $this->seedDokumenWajibForPinjaman($pinjaman);
            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Pengajuan pinjaman berhasil disimpan (Nomor: ' . $pinjaman->nomor_pinjaman . '). Silakan unggah dokumen persyaratan berikutnya.');
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
        $jenisPinjaman = JenisPinjaman::with(['dokumenPersyaratan' => function ($q) {
            $q->select(['master_dokumen.id', 'kode_dokumen', 'nama_dokumen', 'deskripsi', 'format_diperbolehkan']);
        }])->get([
            'id', 'nama_jenis', 'bunga_tahunan', 'tenor_minimal', 'tenor_maksimal', 'maksimal_plafon',
        ]);
        return view("modules.pinjaman.edit", compact('pinjaman', 'anggota', 'cabang', 'jenisPinjaman'));
    }

    public function update(PinjamanUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pinjaman = Pinjaman::findOrFail($id);
            $jenisIdLama = (string) $pinjaman->jenis_pinjaman_id;

            $valid = $request->validated();

            if (empty($valid['nomor_pinjaman'] ?? null) && empty($pinjaman->nomor_pinjaman)) {
                $tglPengajuan = null;
                $tgl = $valid['tgl_pengajuan'] ?? $pinjaman->tgl_pengajuan ?? null;
                if (! empty($tgl)) {
                    try { $tglPengajuan = Carbon::parse($tgl); } catch (\Throwable $e) { $tglPengajuan = null; }
                }
                $cabangId = (string) ($valid['cabang_id'] ?? $pinjaman->cabang_id ?? (Auth::check() ? (string) (Auth::user()->cabang_id ?? '') : ''));
                $valid['nomor_pinjaman'] = Pinjaman::generateNomorPinjaman($cabangId, $tglPengajuan);
            }

            $jenisIdBaru = (string) ($valid['jenis_pinjaman_id'] ?? $jenisIdLama);
            $jenisBaru = $jenisIdBaru !== '' ? JenisPinjaman::find($jenisIdBaru) : null;

            if ($jenisBaru && $jenisBaru->exists) {
                if (empty($valid['bunga'] ?? null) || (float) ($valid['bunga'] ?? 0) <= 0) {
                    $valid['bunga'] = $jenisBaru->bunga_tahunan;
                }
            }

            $pinjaman->update($valid);

            if (isset($jenisBaru) && $jenisBaru->exists && ! empty($pinjaman->jumlah_pinjaman) && ! empty($pinjaman->tenor)) {
                $jumlah = (float) $pinjaman->jumlah_pinjaman;
                $tenor = (int) $pinjaman->tenor;
                $bunga = (float) ($pinjaman->bunga ?? $jenisBaru->bunga_tahunan);
                $angsuranDefault = $this->hitungAngsuranPerBulan($jumlah, $bunga, $tenor);
                if (empty($pinjaman->angsuran_per_bulan) || (float) $pinjaman->angsuran_per_bulan <= 0) {
                    $pinjaman->update(['angsuran_per_bulan' => $angsuranDefault]);
                }
            }

            $pinjaman->loadMissing('jenisPinjaman.dokumenPersyaratanWajib');
            if ($jenisIdLama !== (string) $pinjaman->jenis_pinjaman_id) {
                $this->seedDokumenWajibForPinjaman($pinjaman);
            }
            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Data berhasil diubah (Nomor Pinjaman: ' . ($pinjaman->nomor_pinjaman ?? '-') . ').');
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
            'jenisPinjaman.dokumenPersyaratan',
            'dokumen.masterDokumen',
            'dokumen.uploader:id,nama',
            'dokumen.verifikator:id,nama',
            'angsuran',
        ])->findOrFail($id);

        if (Auth::check() && Auth::user()->hasRole('Anggota')) {
            $anggotaAkun = Anggota::where('users_id', Auth::id())->first();
            if (! $anggotaAkun || (string) $pinjaman->anggota_id !== (string) $anggotaAkun->id) {
                return redirect(route('pinjaman.index'))->with('error', 'Anda tidak memiliki izin mengakses pinjaman ini.');
            }
        }

        $daftarMasterJenis = $pinjaman->jenisPinjaman?->dokumenPersyaratan ?? collect();
        $masterDokumenList = $daftarMasterJenis
            ->filter(fn ($md) => $md->is_active ?? true)
            ->sortBy(fn ($md) => (int) ($md->pivot?->urutan ?? 9999))
            ->values();

        $existingByMaster = $pinjaman->dokumen->keyBy(fn ($d) => (string) $d->master_dokumen_id);
        $dokumenWajibList = collect();
        foreach ($masterDokumenList as $md) {
            $existing = $existingByMaster->get((string) $md->id);
            if (! $existing) {
                $existing = new PinjamanDokumen([
                    'pinjaman_id' => $pinjaman->id,
                    'master_dokumen_id' => $md->id,
                    'status' => 'belum_diunggah',
                ]);
                $existing->setRelation('masterDokumen', $md);
            }
            $existing->is_wajib = (bool) ($md->pivot?->is_wajib ?? false);
            if (! $existing->getRelation('masterDokumen')) {
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
        $daftarSemuaId = $pinjaman->jenisPinjaman?->daftar_semua_master_dokumen_id ?? [];
        if (! in_array((string) $masterDokumenId, $daftarSemuaId, true)) {
            return redirect()->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Dokumen tersebut bukan merupakan persyaratan (wajib maupun opsional) untuk pinjaman ini.');
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
                $relativePath = ImageHelper::storeFile($file, $directory, 'public');
                if ($relativePath === null || trim((string) $relativePath) === '') {
                    throw new \RuntimeException('Gagal menyimpan file dokumen. Silakan coba kembali.');
                }
            } else {
                $relativePath = ImageHelper::compressAndStoreFile($file, $directory, 80, 1600, 'public');
                if ($relativePath === null || trim((string) $relativePath) === '') {
                    throw new \RuntimeException('Gagal menyimpan file dokumen. Silakan coba kembali.');
                }
            }

            if (! empty($oldPath) && $oldPath !== $relativePath && ImageHelper::exists((string) $oldPath)) {
                ImageHelper::delete((string) $oldPath);
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
            if (! in_array($pinjaman->status, ['diajukan', 'perlu_diperbaiki'], true)) {
                return redirect()
                    ->route('pinjaman.show', $pinjaman->id)
                    ->with('error', 'Pengajuan hanya bisa diverifikasi ketika status "Diajukan".');
            }
            $pinjaman->update(['status' => 'diverifikasi']);
            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Pengajuan pinjaman telah diverifikasi dan siap masuk ke proses persetujuan Kepala Cabang.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Gagal melakukan verifikasi pengajuan: ' . $e->getMessage());
        }
    }

    public function approvePengajuan(Request $request, $pinjamanId)
    {
        if (! Auth::check()) {
            return redirect(route('login'));
        }
        $user = Auth::user();
        if (! $user->hasRole('Administrator') && ! $user->can('PINJAMAN_APPROVE')) {
            if ($user->can('PINJAMAN_APPROVE') === false && ! $user->hasRole('Kepala Cabang')) {
                abort(403);
            }
        }

        $pinjaman = Pinjaman::findOrFail($pinjamanId);
        DB::beginTransaction();
        try {
            if ($pinjaman->status !== 'diverifikasi') {
                return redirect()
                    ->route('pinjaman.show', $pinjaman->id)
                    ->with('error', 'Pengajuan hanya bisa disetujui ketika status "Diverifikasi".');
            }
            $pinjaman->update(['status' => 'disetujui']);
            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Pengajuan pinjaman telah DISSETUJUI. Silakan lanjutkan ke tahap pencairan oleh Teller.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Gagal menyetujui pengajuan: ' . $e->getMessage());
        }
    }

    public function tolakPengajuan(Request $request, $pinjamanId)
    {
        if (! Auth::check()) {
            return redirect(route('login'));
        }
        $user = Auth::user();
        if (! $user->hasRole('Administrator') && ! $user->can('PINJAMAN_APPROVE')) {
            if ($user->can('PINJAMAN_APPROVE') === false && ! $user->hasRole('Kepala Cabang')) {
                abort(403);
            }
        }

        $pinjaman = Pinjaman::findOrFail($pinjamanId);
        $validated = $request->validate([
            'catatan_penolakan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            if (! in_array($pinjaman->status, ['diverifikasi', 'disetujui'], true)) {
                return redirect()
                    ->route('pinjaman.show', $pinjaman->id)
                    ->with('error', 'Pengajuan hanya bisa ditolak pada status Diverifikasi atau Disetujui.');
            }
            $catatan = $validated['catatan_penolakan'] ?? null;
            $pinjaman->update([
                'status' => 'ditolak',
            ]);
            DB::commit();
            $msg = 'Pengajuan pinjaman telah ditolak.';
            if ($catatan !== null && trim((string) $catatan) !== '') {
                $msg .= ' Catatan: ' . $catatan;
            }
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }

    public function cairkanPinjaman(Request $request, $pinjamanId)
    {
        if (! Auth::check()) {
            return redirect(route('login'));
        }
        $user = Auth::user();
        if (! $user->hasRole('Administrator') && ! $user->can('PINJAMAN_CAIRKAN')) {
            if ($user->can('PINJAMAN_CAIRKAN') === false && ! $user->hasRole('Teller')) {
                abort(403);
            }
        }

        $pinjaman = Pinjaman::with('angsuran')->findOrFail($pinjamanId);

        DB::beginTransaction();
        try {
            if ($pinjaman->status !== 'disetujui') {
                return redirect()
                    ->route('pinjaman.show', $pinjaman->id)
                    ->with('error', 'Pencairan hanya bisa dilakukan ketika status "Disetujui".');
            }

            $tglCairRaw = $request->input('tgl_cair');
            try {
                $tglCair = $tglCairRaw ? Carbon::parse($tglCairRaw) : Carbon::now();
            } catch (\Throwable $e) {
                $tglCair = Carbon::now();
            }

            $pinjaman->update([
                'status' => 'dicairkan',
                'tgl_cair' => $tglCair->toDateString(),
            ]);

            $sudahAda = $pinjaman->angsuran->count();
            if ($sudahAda <= 0) {
                $this->generateJadwalAngsuran($pinjaman, $tglCair);
            }

            DB::commit();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('success', 'Pinjaman berhasil dicairkan. Jadwal angsuran sebanyak ' . (int) $pinjaman->tenor . ' bulan telah dibuat otomatis dan sudah muncul di modul Angsuran.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('pinjaman.show', $pinjaman->id)
                ->with('error', 'Gagal melakukan pencairan pinjaman: ' . $e->getMessage());
        }
    }

    private function generateJadwalAngsuran(Pinjaman $pinjaman, Carbon $tglCair): void
    {
        $tenor = (int) max(1, (int) $pinjaman->tenor);
        $angsuranPerBulan = (float) $pinjaman->angsuran_per_bulan;
        if ($angsuranPerBulan <= 0) {
            $jumlah = (float) $pinjaman->jumlah_pinjaman;
            $bungaTahunan = (float) $pinjaman->bunga;
            $bungaPerBulan = $bungaTahunan / 100 / 12;
            if ($bungaPerBulan > 0 && $tenor > 0) {
                $angsuranPerBulan = $jumlah * ($bungaPerBulan * pow(1 + $bungaPerBulan, $tenor)) / (pow(1 + $bungaPerBulan, $tenor) - 1);
            } else {
                $angsuranPerBulan = $tenor > 0 ? $jumlah / $tenor : $jumlah;
            }
        }
        $angsuranPerBulan = round($angsuranPerBulan, 2);

        $rows = [];
        for ($i = 1; $i <= $tenor; $i++) {
            $jatuhTempo = (clone $tglCair)->addMonthsNoOverflow($i);
            $rows[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'pinjaman_id' => $pinjaman->id,
                'angsuran_ke' => $i,
                'tanggal_jatuh_tempo' => $jatuhTempo->toDateString(),
                'nominal' => $angsuranPerBulan,
                'denda' => 0,
                'total_bayar' => $angsuranPerBulan,
                'status' => 'belum_lunas',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('angsuran')->upsert(
            $rows,
            ['pinjaman_id', 'angsuran_ke'],
            ['tanggal_jatuh_tempo', 'nominal', 'total_bayar', 'updated_at']
        );
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pinjaman = Pinjaman::with('dokumen')->findOrFail($id);
            foreach ($pinjaman->dokumen as $dok) {
                if (empty($dok->file_path) || trim((string) $dok->file_path) === '') {
                    continue;
                }
                if (ImageHelper::exists((string) $dok->file_path)) {
                    ImageHelper::delete((string) $dok->file_path);
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
