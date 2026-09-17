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
