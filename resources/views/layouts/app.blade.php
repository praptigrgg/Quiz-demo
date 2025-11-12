<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Dashboard')</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="/favicon.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">






  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Summernote -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet" />

  <!-- Cropper.js -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
  <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



  <!-- Custom Flex Layout CSS -->
  <style>
    body, html {
      margin: 0;
      padding: 0;
      font-family: 'Public Sans', sans-serif;
      height: 100%;
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    nav.sidebar {
      width: 260px;
      min-height: 100vh;
      border-right: 1px solid #ddd;
      background-color: #ffffff;
    }

    main.content {
      flex: 1;
      padding: 20px;
      overflow-x: hidden;
    background-color: #f9f9f9;

    }

    /* Optional: submenu toggle */
    .menu-toggle {
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
      @include('layouts.sidebar')
    </nav>

    <!-- Main content -->
    <main class="content">
      @yield('content')
    </main>
  </div>

  <!-- Scripts -->
  <script>
    // Toggle submenu open/close
    document.querySelectorAll('.menu-toggle').forEach(item => {
      item.addEventListener('click', e => {
        e.preventDefault();
        const parent = item.parentElement;
        parent.classList.toggle('open');
      });
    });
  </script>
</body>
</html>
