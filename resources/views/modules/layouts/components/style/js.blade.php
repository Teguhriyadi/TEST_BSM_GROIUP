<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ session('error') }}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
        @endif

        const btnLogout = document.getElementById('btn-logout');
        const formLogout = document.getElementById('logout-form');
        if (btnLogout && formLogout) {
            btnLogout.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Logout',
                    text: 'Apakah Anda yakin ingin keluar dari akun ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f97316',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        formLogout.submit();
                    }
                });
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-delete');
            const form = btn.closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#0284c7',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    $(function() {
        if ($.fn.DataTable) {
            $('.datatable').each(function() {
                const thead = $(this).find('thead tr:first th');
                const tbodyRows = $(this).find('tbody tr');
                let colCount = thead.length;
                if (colCount === 0 && tbodyRows.length > 0) {
                    colCount = tbodyRows.first().children('td, th').length;
                }
                let useDT = true;
                if (tbodyRows.length === 1) {
                    const firstTd = tbodyRows.first().children('td:first');
                    if (firstTd.length && firstTd.attr('colspan') !== undefined && parseInt(firstTd.attr('colspan'), 10) > 1) {
                        useDT = false;
                    }
                }
                if (colCount < 2) {
                    useDT = false;
                }
                if (!useDT) {
                    return;
                }
                const cols = [];
                for (let i = 0; i < colCount; i++) cols.push({ data: i, defaultContent: '' });
                $(this).DataTable({
                    columns: cols,
                    scrollX: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                    language: {
                        processing: "Memproses...",
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(difilter dari _MAX_ total data)",
                        zeroRecords: "Tidak ada data yang tersedia",
                        emptyTable: "Tidak ada data di tabel ini",
                        paginate: {
                            first: "Pertama",
                            previous: "Sebelumnya",
                            next: "Berikutnya",
                            last: "Terakhir"
                        },
                        aria: {
                            sortAscending: ": aktifkan untuk mengurutkan kolom dari kecil ke besar",
                            sortDescending: ": aktifkan untuk mengurutkan kolom dari besar ke kecil"
                        }
                    }
                });
            });
        }

        if ($.fn.select2) {
            const selectors = '.select2, .select2-single, .select2-multiple, .select2-advanced, .select-coa';
            $(document).find(selectors).each(function() {
                const el = $(this);
                const isMultiple = el.prop('multiple') || el.hasClass('select2-multiple');
                const placeholder = el.data('placeholder') || el.find('option[value=""]').text() || '-- Pilih --';
                el.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: !isMultiple,
                    closeOnSelect: !isMultiple,
                    language: {
                        noResults: function() { return 'Tidak ada hasil yang ditemukan'; },
                        searching: function() { return 'Mencari...'; },
                        errorLoading: function() { return 'Gagal memuat data'; },
                        inputTooShort: function(args) { return 'Masukkan minimal ' + (args.minimum - args.input.length) + ' karakter lagi'; },
                        clear: function() { return 'Hapus'; }
                    }
                });
            });
        }
    });
</script>
