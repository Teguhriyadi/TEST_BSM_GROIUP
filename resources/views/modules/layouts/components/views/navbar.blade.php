<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="bi bi-list text-primary" style="font-size: 1.4rem;"></i>
    </button>

    <div class="d-none d-sm-flex align-items-center mr-auto ml-2 ml-md-4">
        <h1 class="h6 mb-0 text-orange fw-bold" style="font-size: 1.05rem; font-weight: 700;">
            Sistem Koperasi Simpan Pinjam
        </h1>
    </div>

    <ul class="navbar-nav ml-auto">

        <li class="nav-item d-flex align-items-center">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button id="btn-logout" type="button" class="btn btn-sm btn-orange text-white px-3 py-1.5" style="font-weight: 600;">
                    <i class="bi bi-box-arrow-right mr-1"></i>
                    Logout
                </button>
            </form>
        </li>

    </ul>

</nav>
