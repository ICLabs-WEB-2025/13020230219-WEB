<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teamwork Homepage - @yield('title', 'DMS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* Sidebar styling */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            padding-top: 1.5rem;
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }

        /* Mobile sidebar hidden by default */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0 !important;
            }
        }

        .sidebar a {
            padding: 0.75rem 1rem;
            text-decoration: none;
            font-size: 1.125rem;
            color: white;
            display: block;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .sidebar a:hover {
            background-color: #495057;
            transform: translateX(5px);
        }

        /* Dropdown styling with animation */
        .dropdown-menu {
            background-color: #495057;
            border: none;
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.3s ease, opacity 0.3s ease;
            opacity: 0;
        }

        .dropdown-menu.show {
            transform: scaleY(1);
            opacity: 1;
        }

        .dropdown-item {
            color: white;
            padding: 0.75rem 1rem;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #6c757d;
            color: white;
        }

        /* Content styling */
        .content {
            margin-left: 250px;
            padding: 1.5rem;
            transition: margin-left 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
        }

        /* Navbar for mobile toggle */
        .navbar {
            background-color: #343a40;
            padding: 0.75rem 1rem;
        }

        .navbar-brand,
        .nav-link {
            color: white !important;
        }

        /* Card styling */
        .card {
            margin-top: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Toggle button animation */
        .toggle-btn i {
            transition: transform 0.3s ease;
        }

        .toggle-btn.active i {
            transform: rotate(90deg);
        }

        /* Dropdown toggle icon animation */
        .dropdown-toggle::after {
            display: none !important;
            /* Ensure Bootstrap's default arrow is hidden */
        }

        .dropdown-toggle i {
            transition: transform 0.3s ease;
        }

        .dropdown-toggle.show i {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>
    <!-- Navbar for mobile -->
    <nav class="navbar navbar-expand-lg d-md-none">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">DMS</a>
            <button class="toggle-btn navbar-toggler text-white" type="button" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2 class="text-center mb-4 fs-4 fw-bold">DocuVault</h2>
        <a href="{{ route('dashboard') }}" class="mb-2">Dashboard</a>
        <a href="{{ route('documents.index') }}" class="mb-2">Kelola Dokumen</a>
        <a href="{{ route('documentShare.index') }}" class="mb-2">Shared Document</a>

        <!-- Settings Dropdown -->
        <div class="dropdown">
            <a href="#" class="dropdown-toggle text-white d-flex align-items-center" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                <span>Pengaturan</span>
                <i class="fas fa-chevron-down ms-2"></i>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('profile.index') }}">Profile</a>
                <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            </div>
        </div>

        <!-- Logout Form -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.toggle-btn');
            sidebar.classList.toggle('active');
            toggleBtn.classList.toggle('active');
        }
    </script>
</body>

</html>