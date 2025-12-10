{{-- resources/views/layouts/sidebar.blade.php --}}

<style>
    /* ========================
       Custom Sneat Dashboard Styles
       ======================== */

    .bs-toast {
        position: fixed;
        top: 30px;
        right: 30px;
        z-index: 999999;
        border: none;
    }

    :root {
        --primary: #6183b0 !important;
        --secondary: #BA1616 !important;
    }

    .bg-primary {
        background-color: var(--primary) !important;
        color: #fff !important;
    }

    .btn-primary {
        background-color: var(--primary) !important;
        color: #fff !important;
        border-color: var(--primary) !important;
    }

    .menu-item a {
        color: var(--primary) !important;
        text-decoration: none;
    }

    .menu-item a:hover {
        color: var(--secondary) !important;
    }

    .menu-item.active a {
        color: var(--secondary) !important;
    }


    .btn-outline-primary {
        color: var(--primary) !important;
        border-color: var(--primary) !important;
        background-color: transparent !important;
    }

    .btn-outline-primary:hover {
        background-color: var(--primary) !important;
        color: #fff !important;
    }

    .text-primary {
        color: var(--primary) !important;
    }

    .text-secondary {
        color: var(--secondary) !important;
    }

    .btn-secondary {
        background-color: var(--secondary) !important;
        color: #fff !important;
        border-color: var(--secondary) !important;
    }

    .btn-outline-secondary {
        color: var(--secondary) !important;
        border-color: var(--secondary) !important;
        background-color: transparent !important;
    }

    .btn-outline-secondary:hover {
        background-color: var(--secondary) !important;
        color: #fff !important;
    }



    .breadcrumb-item+.breadcrumb-item::before {
        padding-right: 0rem !important;
        content: "";
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary) !important;
    }

    .form-label,
    .form-check-label {
        color: var(--primary) !important;
    }

    .active {
        color: var(--secondary) !important;
    }

    .editor-container {
        margin-bottom: 20px;
    }

    .content-output {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        min-height: 100px;
    }

    .note-editor.fullscreen {
        position: fixed;
        top: 0;
        left: 260px !important;
        width: 100%;
        height: 100%;
        z-index: 9999 !important;
        background: white;
    }

    .note-editor.fullscreen .note-editable {
        max-height: calc(100vh - 100px);
        width: fit-content;
        overflow-y: auto;
        z-index: 9999;
    }



    /* Remove bullets from main menu */
    .menu-inner>.menu-item {
        list-style: none;
        /* no bullet for main menu items */
        padding-left: 0;
        /* remove default indentation */
    }

    /* Optional: bullets for sub-menu items */
    .menu-sub>.menu-item {
        margin-bottom: 16px;
        list-style: disc outside;
        /* bullet outside the content box */
        padding-left: 20px;
        /* indent submenu items */
    }

    /* submenu link text */
    .menu-sub>.menu-item>.menu-link>div,
    .menu-sub>.menu-item>.menu-link>span {
        display: inline;
        /* inline so bullet and text are on same line */
    }

    .menu-inner>.menu-item {
        margin-bottom: 16px;
        /* vertical spacing between main menus */
        padding-left: 0;
    }

    /* Remove default margin/padding on all ULs inside sidebar */
    .menu-inner {
        margin: 10px;
        padding: 20px;
    }

    .menu-sub {
        margin: 10px;
        padding: 10px;
    }

    .menu-link {
        display: flex;
        align-items: center;
        /* vertically center icon and text */
        gap: 10px;
        /* spacing between icon and text */
    }
</style>

<ul class="menu-inner py-2">

    {{-- Dashboard --}}
    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <span>Dashboard</span>
        </a>
    </li>

    {{-- Course Management --}}
    @php $courseOpen = request()->is('admin/courses*'); @endphp
    <li class="menu-item {{ $courseOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-book"></i>
            <div>Course Management</div>
        </a>

        <ul class="menu-sub" style="display: {{ $courseOpen ? 'block' : 'none' }};">
            <li class="menu-item">
                <a href="{{ route('admin.courses.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ul"></i>
                    <div>Courses List</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.courses.create') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-plus"></i>
                    <div>Add Course</div>
                </a>
            </li>
        </ul>
    </li>

    {{-- Quiz Management --}}
    @php $quizOpen = request()->is('admin/quizzes*'); @endphp
    <li class="menu-item {{ $quizOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-clipboard"></i>
            <div>Quiz Management</div>
        </a>

        <ul class="menu-sub" style="display: {{ $quizOpen ? 'block' : 'none' }};">
            <li class="menu-item">
                <a href="{{ route('admin.quizzes.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ul"></i>
                    <span>Quizzes List</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.quizzes.create') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-plus"></i>
                    <span>Add Quiz</span>
                </a>
            </li>
        </ul>
    </li>

    {{-- Live Assignment --}}
    @php $liveOpen = request()->is('admin/live/assign*'); @endphp
    <li class="menu-item {{ $liveOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-book"></i>
            <div>Live Assignment</div>
        </a>

        <ul class="menu-sub" style="display: {{ $liveOpen ? 'block' : 'none' }};">
            <li class="menu-item">
                <a href="{{ route('admin.live.assign.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ul"></i>
                    <div>Assignment List</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.live.assign.page') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-plus"></i>
                    <div>Assign Live</div>
                </a>
            </li>
        </ul>
    </li>

    {{-- Certificate Management --}}
    @php $certOpen = request()->is('admin/certificates*'); @endphp
    <li class="menu-item {{ $certOpen ? 'open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-award"></i>
            <div>Certificate Management</div>
        </a>

        <ul class="menu-sub" style="display: {{ $certOpen ? 'block' : 'none' }};">
            <li class="menu-item">
                <a href="{{ route('admin.certificates.list') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ul"></i>
                    <div>Certificates List</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('admin.certificates.form') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-plus"></i>
                    <div>Create Certificate</div>
                </a>
            </li>
        </ul>
    </li>

</ul>

<script>
    // Sidebar submenu toggle
    document.querySelectorAll('.menu-toggle').forEach(item => {
        item.addEventListener('click', e => {
            e.preventDefault();
            const parent = item.parentElement;
            const submenu = parent.querySelector('.menu-sub');

            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
                parent.classList.remove('open');
            } else {
                submenu.style.display = 'block';
                parent.classList.add('open');
            }
        });
    });
</script>
