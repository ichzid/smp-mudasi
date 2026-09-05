<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Absen Kartu RFID' }} | SMP Muhammadiyah Danau Sijabut</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/smp_mudasi.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Apply dark mode based on localStorage to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
    @yield('content')
</body>
</html>