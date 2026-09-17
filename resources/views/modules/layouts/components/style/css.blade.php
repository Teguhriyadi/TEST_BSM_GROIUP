<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('css/sb-admin-2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }

    .bg-gradient-primary { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; }
    .bg-gradient-orange { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important; }

    .text-primary { color: #0284c7 !important; }
    .bg-primary { background-color: #0284c7 !important; }
    .btn-primary { background-color: #0284c7 !important; border-color: #0284c7 !important; }
    .btn-primary:hover { background-color: #0369a1 !important; border-color: #0369a1 !important; }
    .border-primary { border-color: #0284c7 !important; }

    .btn-warning { background-color: #f97316 !important; border-color: #f97316 !important; color: #ffffff !important; }
    .btn-warning:hover { background-color: #ea580c !important; border-color: #ea580c !important; color: #ffffff !important; }
    .btn-orange { background-color: #f97316 !important; border-color: #f97316 !important; color: #ffffff !important; }
    .btn-orange:hover { background-color: #ea580c !important; border-color: #ea580c !important; color: #ffffff !important; }

    .sidebar { overflow-x: hidden; overflow-y: auto; }
    .sidebar .nav-item { margin: 2px 10px; position: relative; }
    .sidebar .nav-item .nav-link {
        padding: 0.85rem 1.1rem;
        border-radius: 0.55rem;
        margin-bottom: 2px;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        color: rgba(255, 255, 255, 0.88);
        display: flex;
        align-items: center;
    }
    .sidebar .nav-item .nav-link i { font-size: 1.15rem; margin-right: 0.65rem; flex-shrink: 0; }
    .sidebar .nav-item .nav-link span { font-size: 0.92rem; letter-spacing: 0.2px; }
    .sidebar .nav-item .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        transform: translateX(2px);
    }
    .sidebar .nav-link.active {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35);
    }
    .sidebar .nav-link.active::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60%;
        background-color: #ffffff;
        border-radius: 0 2px 2px 0;
        pointer-events: none;
    }
    .sidebar.toggled .nav-link.active::before { display: none; }
    .sidebar .nav-link.active i,
    .sidebar .nav-link.active span { color: #ffffff !important; }
    .sidebar .sidebar-heading { padding: 0 1.5rem; }
    .sidebar.toggled .nav-item .nav-link span { display: none; }
    .sidebar.toggled .sidebar-heading { font-size: 0.55rem !important; padding: 0 0.5rem; text-align: center; }

    .card {
        border: none;
        border-radius: 0.6rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .btn {
        border-radius: 0.45rem;
    }

    table.dataTable thead th {
        background-color: #0284c7 !important;
        color: white !important;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .badge-status {
        padding: 0.25rem 0.6rem;
        border-radius: 0.3rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .text-orange { color: #f97316 !important; }
    .bg-orange { background-color: #f97316 !important; }
    .border-orange { border-color: #f97316 !important; }

    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item.active .page-link {
        background-color: #f97316;
        border-color: #f97316;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.page-item .page-link {
        color: #0284c7;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_length label {
        font-size: 0.875rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
    }
</style>
