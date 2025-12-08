<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Challenge')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Laravel Challenge</span>
        </div>
        <div>
            <a href="/login" class="btn btn-outline-light">Login</a>
        </div>
    </nav>
    @if (session('error'))
        <div class="alert alert-danger m-3">{{ session('error') }}</div>
    @endif

    @if (session('status'))
        <div class="alert alert-success m-3">{{ session('status') }}</div>
    @endif

    <main class="py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>