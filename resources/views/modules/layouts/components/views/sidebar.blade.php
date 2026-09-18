<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <div class="sidebar-brand d-flex align-items-center justify-content-center my-3">
        <div class="sidebar-brand-icon">
            <i class="bi bi-bank text-orange" style="font-size: 2rem;"></i>
        </div>
        <div class="sidebar-brand-text mx-2 text-uppercase"
            style="font-weight: 800; font-size: 1rem; letter-spacing: 0.5px;">
            KSP BSM
        </div>
    </div>

    <hr class="sidebar-divider my-0" style="border-color: rgba(255,255,255,0.12);">

    @if (auth()->check())
        @php
            $userLogin = auth()->user();
            $namaCabangUser = $userLogin->cabang?->nama_cabang ?? 'Seluruh Cabang';
            $kodeCabangUser = $userLogin->cabang?->kode_cabang ?? null;
            $namaRoleUser = $userLogin->role?->nama_role ?? 'Pengguna';
            if ($userLogin->hasRole('Anggota')) {
                $headerLabelRole = 'ANGGOTA';
            } elseif ($userLogin->hasRole('Administrator')) {
                $headerLabelRole = 'ADMINISTRATOR';
            } else {
                $headerLabelRole = strtoupper($namaRoleUser);
            }
        @endphp
        <div class="px-3 pt-3 pb-2">
            <div class="text-uppercase fw-bolder mb-1"
                style="color: #f97316; font-size: 0.72rem; letter-spacing: 1.1px;">
                <i class="bi bi-geo-alt-fill me-1"></i>{{ $headerLabelRole }}
            </div>
            <div class="text-white fw-semibold mb-0" style="font-size: 0.92rem; line-height: 1.3;">
                {{ $namaCabangUser }}
            </div>
            @if ($kodeCabangUser)
                <div class="text-white-50 small mt-0" style="font-size: 0.74rem;">Kode Cabang: {{ $kodeCabangUser }}
                </div>
            @endif
        </div>
        <hr class="sidebar-divider my-1 mx-3" style="border-color: rgba(255,255,255,0.1);">
    @endif

    @if (auth()->check() && auth()->user()->hasRole('Anggota'))

        <div class="sidebar-heading mt-2 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            PORTAL ANGGOTA
        </div>

        @haspermission('ANGGOTA_DASHBOARD')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('ANGGOTA_DASHBOARD')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('anggota.*') ? 'active' : '' }}"
                href="{{ route('anggota.index') }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>Profil Saya</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('ANGGOTA_SIMPANAN_VIEW')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('simpanan.*') ? 'active' : '' }}"
                href="{{ route('simpanan.index') }}">
                <i class="bi bi-piggy-bank"></i>
                <span>Simpanan</span>
            </a>
        </li>
        @endhaspermission

        @hasanypermission(['ANGGOTA_PINJAMAN_VIEW','ANGGOTA_PINJAMAN_CREATE'])
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pinjaman.*') ? 'active' : '' }}"
                href="{{ route('pinjaman.index') }}">
                <i class="bi bi-cash-stack"></i>
                <span>Pinjaman</span>
            </a>
        </li>
        @endhasanypermission

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">

        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.75rem; font-weight: 600; letter-spacing: 1px; line-height: 1.4; padding-left: 0.25rem; padding-right: 0.25rem;">
            Anda login sebagai Anggota. Untuk perubahan data hubungi Teller Cabang.
        </div>
    @else
        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            KOPERASI
        </div>

        @haspermission('DASHBOARD_VIEW')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endhaspermission

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">

        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            MASTER DATA
        </div>

        @haspermission('CABANG_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('cabang.*') ? 'active' : '' }}"
                href="{{ route('cabang.index') }}">
                <i class="bi bi-building"></i>
                <span>Cabang</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('ANGGOTA_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('anggota.index') || request()->routeIs('anggota.create') || request()->routeIs('anggota.show') || request()->routeIs('anggota.edit') ? 'active' : '' }}"
                href="{{ route('anggota.index') }}">
                <i class="bi bi-people"></i>
                <span>Anggota</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('ANGGOTA_PENDAFTARAN_VERIFIKASI')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('anggota.pendaftaran.*') ? 'active' : '' }}"
                    href="{{ route('anggota.pendaftaran.menunggu') }}">
                    <i class="bi bi-person-check-fill"></i>
                    <span>Verifikasi</span>
                </a>
            </li>
        @endhaspermission

        @haspermission('JENIS_SIMPANAN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('jenis-simpanan.*') ? 'active' : '' }}"
                href="{{ route('jenis-simpanan.index') }}">
                <i class="bi bi-tags"></i>
                <span>Jenis Simpanan</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('JENIS_PINJAMAN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('jenis-pinjaman.*') ? 'active' : '' }}"
                href="{{ route('jenis-pinjaman.index') }}">
                <i class="bi bi-card-list"></i>
                <span>Jenis Pinjaman</span>
            </a>
        </li>
        @endhaspermission

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">

        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            TRANSAKSI
        </div>

        @haspermission('SIMPANAN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('simpanan.*') ? 'active' : '' }}"
                href="{{ route('simpanan.index') }}">
                <i class="bi bi-piggy-bank"></i>
                <span>Simpanan</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('PINJAMAN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pinjaman.index') || request()->routeIs('pinjaman.create') || request()->routeIs('pinjaman.show') || request()->routeIs('pinjaman.edit') ? 'active' : '' }}"
                href="{{ route('pinjaman.index') }}">
                <i class="bi bi-cash-stack"></i>
                <span>Pinjaman</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('ANGSURAN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('angsuran.*') ? 'active' : '' }}"
                href="{{ route('angsuran.index') }}">
                <i class="bi bi-calendar2-check"></i>
                <span>Angsuran</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('PEMBAYARAN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pembayaran-angsuran.*') ? 'active' : '' }}"
                href="{{ route('pembayaran-angsuran.index') }}">
                <i class="bi bi-receipt"></i>
                <span>Pembayaran Angsuran</span>
            </a>
        </li>
        @endhaspermission

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">

        @php
            $bisaJurnal = auth()->user()->hasPermission('JURNAL_INDEX');
            $bisaJurnalHarian = auth()->user()->hasPermission('JURNAL_HARIAN_VIEW');
            $bisaCoa = auth()->user()->hasPermission('COA_INDEX');
            $bisaBukuBesar = auth()->user()->hasPermission('BUKU_BESAR_INDEX');
            $bisaRekapKas = auth()->user()->hasPermission('REKAP_KAS_VIEW');
            $bisaBukuKas = auth()->user()->hasPermission('BUKU_KAS_VIEW');
            $bisaLRKum = auth()->user()->hasPermission('LABA_RUGI_KUMULATIF_VIEW');
            $bisaLRPer = auth()->user()->hasPermission('LABA_RUGI_PERIODE_VIEW');
            $bisaNeraca = auth()->user()->hasPermission('NERACA_VIEW');
            $bisaNeracaSaldo = auth()->user()->hasPermission('NERACA_SALDO_VIEW');
            $bisaTutupBuku = auth()->user()->hasPermission('TUTUP_BUKU_EXEC');
            $bisaCekNS = auth()->user()->hasPermission('CEK_NERACA_SALDO_VIEW');
            $bisaShu = auth()->user()->hasPermission('LAP_SHU_VIEW');
            $bisaArusKas = auth()->user()->hasPermission('LAP_ARUS_KAS_VIEW');
            $bisaMapping = auth()->user()->hasPermission('COA_MAPPING_INDEX');
            $bisaSaldoAwal = auth()->user()->hasPermission('COA_SALDO_AWAL_INDEX');
            $adaMenuAkuntansi = $bisaJurnal || $bisaJurnalHarian || $bisaCoa || $bisaBukuBesar || $bisaRekapKas || $bisaBukuKas
                || $bisaLRKum || $bisaLRPer || $bisaNeraca || $bisaNeracaSaldo || $bisaTutupBuku || $bisaCekNS
                || $bisaMapping || $bisaSaldoAwal;
            $adaMenuLapAkunting = $bisaNeraca || $bisaShu || $bisaArusKas;
            $adaSubAkuntansi = $bisaJurnal || $bisaJurnalHarian || $bisaCoa || $bisaBukuBesar || $bisaRekapKas || $bisaBukuKas
                || $bisaLRKum || $bisaLRPer || $bisaNeraca || $bisaNeracaSaldo || $bisaTutupBuku || $bisaCekNS;
        @endphp

        @if ($adaMenuAkuntansi)
        <style>
            #collapseAkuntansi .collapse-item,
            #collapseLapAkunting .collapse-item {
                color: #495057;
                padding: 0.5rem 1rem;
                margin: 2px 8px;
                border-radius: 0.375rem;
                text-decoration: none;
                display: flex;
                align-items: center;
                transition: background-color 0.15s ease, color 0.15s ease;
            }
            #collapseAkuntansi .collapse-item:hover,
            #collapseLapAkunting .collapse-item:hover {
                background-color: #f8f9fa;
                color: #212529;
            }
            #collapseAkuntansi .collapse-item.active,
            #collapseLapAkunting .collapse-item.active {
                background-color: #f97316;
                color: #ffffff !important;
                font-weight: 600;
            }
        </style>

        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            AKUNTANSI
        </div>

        @if ($adaSubAkuntansi)
        <li class="nav-item">
            @php
                $akuntansiActive = request()->routeIs('jurnal-umum.*')
                    || request()->routeIs('jurnal-harian.*')
                    || request()->routeIs('coa.*')
                    || request()->routeIs('coa-mapping.*')
                    || request()->routeIs('coa-saldo-awal.*')
                    || request()->routeIs('buku-besar.*')
                    || request()->routeIs('rekap-kas-non-kas.*')
                    || request()->routeIs('buku-kas-harian.*')
                    || request()->routeIs('laba-rugi.*')
                    || request()->routeIs('neraca.*')
                    || request()->routeIs('neraca-saldo.*')
                    || request()->routeIs('tutup-buku.*')
                    || request()->routeIs('cek-neraca-saldo.*');
            @endphp
            <a class="nav-link {{ $akuntansiActive ? 'active' : 'collapsed' }}" href="#" data-bs-toggle="collapse"
                data-bs-target="#collapseAkuntansi" aria-expanded="{{ $akuntansiActive ? 'true' : 'false' }}"
                aria-controls="collapseAkuntansi">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Akuntansi</span>
            </a>
            <div id="collapseAkuntansi" class="collapse {{ $akuntansiActive ? 'show' : '' }}"
                aria-labelledby="headingAkuntansi" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded"
                    style="background-color: #ffffff; border: 1px solid rgba(0,0,0,0.08);">
                    @if ($bisaJurnal)
                    <a class="collapse-item {{ request()->routeIs('jurnal-umum.*') ? 'active' : '' }}"
                        href="{{ route('jurnal-umum.index') }}">
                        <i class="bi bi-journal-text pe-2"></i>Jurnal
                    </a>
                    @endif

                    @if ($bisaJurnalHarian)
                    <a class="collapse-item {{ request()->routeIs('jurnal-harian.*') ? 'active' : '' }}"
                        href="{{ route('jurnal-harian.index') }}">
                        <i class="bi bi-calendar-event pe-2"></i>Jurnal Harian
                    </a>
                    @endif

                    @if ($bisaCoa)
                    <a class="collapse-item {{ request()->routeIs('coa.*') ? 'active' : '' }}"
                        href="{{ route('coa.index') }}">
                        <i class="bi bi-diagram-3 pe-2"></i>Perkiraan
                    </a>
                    @endif

                    @if ($bisaMapping)
                    <a class="collapse-item {{ request()->routeIs('coa-mapping.*') ? 'active' : '' }}"
                        href="{{ route('coa-mapping.index') }}">
                        <i class="bi bi-link-45deg pe-2"></i>Mapping Akun
                    </a>
                    @endif

                    @if ($bisaSaldoAwal)
                    <a class="collapse-item {{ request()->routeIs('coa-saldo-awal.*') ? 'active' : '' }}"
                        href="{{ route('coa-saldo-awal.index') }}">
                        <i class="bi bi-coin pe-2"></i>Saldo Awal
                    </a>
                    @endif

                    @if ($bisaBukuBesar)
                    <a class="collapse-item {{ request()->routeIs('buku-besar.*') ? 'active' : '' }}"
                        href="{{ route('buku-besar.index') }}">
                        <i class="bi bi-book pe-2"></i>Buku Besar
                    </a>
                    @endif

                    @if ($bisaRekapKas)
                    <a class="collapse-item {{ request()->routeIs('rekap-kas-non-kas.*') ? 'active' : '' }}"
                        href="{{ route('rekap-kas-non-kas.index') }}">
                        <i class="bi bi-cash-stack pe-2"></i>Rekap Kas dan Non Kas Harian
                    </a>
                    @endif

                    @if ($bisaBukuKas)
                    <a class="collapse-item {{ request()->routeIs('buku-kas-harian.*') ? 'active' : '' }}"
                        href="{{ route('buku-kas-harian.index') }}">
                        <i class="bi bi-wallet2 pe-2"></i>Buku Kas Harian
                    </a>
                    @endif

                    @if ($bisaLRKum)
                    <a class="collapse-item {{ request()->routeIs('laba-rugi.kumulatif') ? 'active' : '' }}"
                        href="{{ route('laba-rugi.kumulatif') }}">
                        <i class="bi bi-graph-up-arrow pe-2"></i>Laba Rugi Kumulatif
                    </a>
                    @endif

                    @if ($bisaLRPer)
                    <a class="collapse-item {{ request()->routeIs('laba-rugi.periode') ? 'active' : '' }}"
                        href="{{ route('laba-rugi.periode') }}">
                        <i class="bi bi-bar-chart-line pe-2"></i>Laba Rugi Periode
                    </a>
                    @endif

                    @if ($bisaNeraca)
                    <a class="collapse-item {{ request()->routeIs('neraca.*') && !request()->routeIs('laporan-akunting.*') ? 'active' : '' }}"
                        href="{{ route('neraca.index') }}">
                        <i class="bi bi-bar-chart-steps pe-2"></i>Neraca
                    </a>
                    @endif

                    @if ($bisaNeracaSaldo)
                    <a class="collapse-item {{ request()->routeIs('neraca-saldo.*') ? 'active' : '' }}"
                        href="{{ route('neraca-saldo.index') }}">
                        <i class="bi bi-arrow-left-right pe-2"></i>Neraca Saldo
                    </a>
                    @endif

                    @if ($bisaTutupBuku)
                    <a class="collapse-item {{ request()->routeIs('tutup-buku.*') ? 'active' : '' }}"
                        href="{{ route('tutup-buku.index') }}">
                        <i class="bi bi-door-closed pe-2"></i>Tutup Buku
                    </a>
                    @endif

                    @if ($bisaCekNS)
                    <a class="collapse-item {{ request()->routeIs('cek-neraca-saldo.*') ? 'active' : '' }}"
                        href="{{ route('cek-neraca-saldo.index') }}">
                        <i class="bi bi-check2-circle pe-2"></i>Cek Neraca dan Saldo
                    </a>
                    @endif
                </div>
            </div>
        </li>
        @endif

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">
        @endif

        @if ($adaMenuLapAkunting)
        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            LAPORAN AKUNTING
        </div>

        <li class="nav-item">
            @php
                $lapAkuntingActive = request()->routeIs('laporan-akunting.*');
            @endphp
            <a class="nav-link {{ $lapAkuntingActive ? 'active' : 'collapsed' }}" href="#" data-bs-toggle="collapse"
                data-bs-target="#collapseLapAkunting" aria-expanded="{{ $lapAkuntingActive ? 'true' : 'false' }}"
                aria-controls="collapseLapAkunting">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan Akunting</span>
            </a>
            <div id="collapseLapAkunting" class="collapse {{ $lapAkuntingActive ? 'show' : '' }}"
                aria-labelledby="headingLapAkunting" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded"
                    style="background-color: #ffffff; border: 1px solid rgba(0,0,0,0.08);">
                    @if ($bisaNeraca)
                    <a class="collapse-item {{ request()->routeIs('laporan-akunting.neraca') ? 'active' : '' }}"
                        href="{{ route('laporan-akunting.neraca') }}">
                        <i class="bi bi-bar-chart-steps pe-2"></i>Neraca
                    </a>
                    @endif

                    @if ($bisaShu)
                    <a class="collapse-item {{ request()->routeIs('laporan-akunting.shu') ? 'active' : '' }}"
                        href="{{ route('laporan-akunting.shu') }}">
                        <i class="bi bi-piggy-bank pe-2"></i>Sisa Hasil Usaha
                    </a>
                    @endif

                    @if ($bisaArusKas)
                    <a class="collapse-item {{ request()->routeIs('laporan-akunting.arus-kas') ? 'active' : '' }}"
                        href="{{ route('laporan-akunting.arus-kas') }}">
                        <i class="bi bi-send-exclamation pe-2"></i>Arus Kas
                    </a>
                    @endif
                </div>
            </div>
        </li>

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">
        @endif

        @php
            $bisaMasterDok = auth()->user()->hasPermission('MASTER_DOKUMEN_INDEX');
            $bisaSettingPer = auth()->user()->hasPermission('SETTING_PERSYARATAN_INDEX');
            $bisaSimulasi = auth()->user()->hasPermission('SIMULASI_PINJAMAN_VIEW');
            $adaMenuMasterDok = $bisaMasterDok || $bisaSettingPer;
        @endphp

        @if ($adaMenuMasterDok)
        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            MASTER DOKUMEN
        </div>

        @haspermission('MASTER_DOKUMEN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('master-dokumen.*') ? 'active' : '' }}"
                href="{{ route('master-dokumen.index') }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Daftar Master Dokumen</span>
            </a>
        </li>
        @endhaspermission

        @haspermission('SETTING_PERSYARATAN_INDEX')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('setting-persyaratan-pinjaman.*') ? 'active' : '' }}"
                href="{{ route('setting-persyaratan-pinjaman.index') }}">
                <i class="bi bi-list-check"></i>
                <span>Setting Persyaratan Pinjaman</span>
            </a>
        </li>
        @endhaspermission

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">
        @endif

        @if ($bisaSimulasi)
        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            SIMULASI
        </div>

        @haspermission('SIMULASI_PINJAMAN_VIEW')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('simulasi-pinjaman.*') ? 'active' : '' }}"
                href="{{ route('simulasi-pinjaman.index') }}">
                <i class="bi bi-calculator"></i>
                <span>Simulasi Pinjaman</span>
            </a>
        </li>
        @endhaspermission

        <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">
        @endif

        <style>
            #collapseLaporan .collapse-item {
                color: #495057;
                padding: 0.5rem 1rem;
                margin: 2px 8px;
                border-radius: 0.375rem;
                text-decoration: none;
                display: flex;
                align-items: center;
                transition: background-color 0.15s ease, color 0.15s ease;
            }

            #collapseLaporan .collapse-item:hover {
                background-color: #f8f9fa;
                color: #212529;
            }

            #collapseLaporan .collapse-item.active {
                background-color: #f97316;
                color: #ffffff !important;
                font-weight: 600;
            }
        </style>

        @php
            $bisaLaporanAnggota = auth()->user()->hasPermission('LAPORAN_ANGGOTA_INDEX');
            $bisaLaporanVerifikasi = auth()->user()->hasPermission('LAPORAN_VERIFIKASI_INDEX') || auth()->user()->hasPermission('ANGGOTA_PENDAFTARAN_VERIFIKASI');
            $bisaLaporanSimpanan = auth()->user()->hasPermission('LAPORAN_SIMPANAN_INDEX');
            $bisaLaporanPinjaman = auth()->user()->hasPermission('LAPORAN_PINJAMAN_INDEX');
            $bisaLaporanAngsuran = auth()->user()->hasPermission('LAPORAN_ANGSURAN_INDEX');
            $bisaLaporanPembayaran = auth()->user()->hasPermission('LAPORAN_PEMBAYARAN_INDEX');
            $bisaSemuaLaporan = $bisaLaporanAnggota || $bisaLaporanVerifikasi || $bisaLaporanSimpanan || $bisaLaporanPinjaman || $bisaLaporanAngsuran || $bisaLaporanPembayaran;
        @endphp

        @if ($bisaSemuaLaporan)
        <div class="sidebar-heading mt-3 mb-2"
            style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
            LAPORAN
        </div>

        <li class="nav-item">
            @php
                $laporanActive = request()->routeIs('laporan.*');
            @endphp
            <a class="nav-link {{ $laporanActive ? 'active' : 'collapsed' }}" href="#" data-bs-toggle="collapse"
                data-bs-target="#collapseLaporan" aria-expanded="{{ $laporanActive ? 'true' : 'false' }}"
                aria-controls="collapseLaporan">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse {{ $laporanActive ? 'show' : '' }}"
                aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded"
                    style="background-color: #ffffff; border: 1px solid rgba(0,0,0,0.08);">
                    @if ($bisaLaporanAnggota)
                    <a class="collapse-item {{ request()->routeIs('laporan.anggota') ? 'active' : '' }}"
                        href="{{ route('laporan.anggota') }}">
                        <i class="bi bi-people pe-2"></i>Anggota
                    </a>
                    @endif

                    @if ($bisaLaporanVerifikasi)
                        <a class="collapse-item {{ request()->routeIs('laporan.verifikasi') ? 'active' : '' }}"
                            href="{{ route('laporan.verifikasi') }}">
                            <i class="bi bi-person-check pe-2"></i>Verifikasi
                        </a>
                    @endif
                    @if ($bisaLaporanSimpanan)
                    <a class="collapse-item {{ request()->routeIs('laporan.simpanan') ? 'active' : '' }}"
                        href="{{ route('laporan.simpanan') }}">
                        <i class="bi bi-piggy-bank pe-2"></i>Simpanan
                    </a>
                    @endif
                    @if ($bisaLaporanPinjaman)
                    <a class="collapse-item {{ request()->routeIs('laporan.pinjaman') ? 'active' : '' }}"
                        href="{{ route('laporan.pinjaman') }}">
                        <i class="bi bi-cash-stack pe-2"></i>Pinjaman
                    </a>
                    @endif
                    @if ($bisaLaporanAngsuran)
                    <a class="collapse-item {{ request()->routeIs('laporan.angsuran') ? 'active' : '' }}"
                        href="{{ route('laporan.angsuran') }}">
                        <i class="bi bi-calendar2-check pe-2"></i>Angsuran
                    </a>
                    @endif
                    @if ($bisaLaporanPembayaran)
                    <a class="collapse-item {{ request()->routeIs('laporan.pembayaran') ? 'active' : '' }}"
                        href="{{ route('laporan.pembayaran') }}">
                        <i class="bi bi-receipt pe-2"></i>Pembayaran
                    </a>
                    @endif
                </div>
            </div>
        </li>
        @endif

        @if (auth()->check() && auth()->user()->hasRole('Administrator'))
            <hr class="sidebar-divider my-2" style="border-color: rgba(255,255,255,0.12);">

            <div class="sidebar-heading mt-3 mb-2"
                style="color: rgba(255,255,255,0.82); font-size: 0.8rem; font-weight: 700; letter-spacing: 1.2px;">
                MASTER SISTEM
            </div>

            @haspermission('USERS_INDEX')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                    href="{{ route('users.index') }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Users</span>
                </a>
            </li>
            @endhaspermission

            @haspermission('ROLE_INDEX')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                    href="{{ route('roles.index') }}">
                    <i class="bi bi-shield-lock"></i>
                    <span>Roles</span>
                </a>
            </li>
            @endhaspermission

            @haspermission('PERMISSION_INDEX')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
                    href="{{ route('permissions.index') }}">
                    <i class="bi bi-key"></i>
                    <span>Permissions</span>
                </a>
            </li>
            @endhaspermission

            @haspermission('AKTIVITAS_LOG_INDEX')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('aktivitas-log.*') ? 'active' : '' }}"
                    href="{{ route('aktivitas-log.index') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Aktivitas Log</span>
                </a>
            </li>
            @endhaspermission
        @endif

    @endif

    {{-- <hr class="sidebar-divider d-none d-md-block my-3" style="border-color: rgba(255,255,255,0.12);">

    <div class="text-center d-none d-md-inline mt-2 mb-3">
        <button class="rounded-circle border-0" id="sidebarToggle" style="background-color: rgba(255,255,255,0.25); width: 2.6rem; height: 2.6rem; cursor: pointer;"></button>
    </div> --}}

</ul>
