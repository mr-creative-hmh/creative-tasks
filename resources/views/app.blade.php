<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark" data-accent="sky">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title inertia>{{ config('app.name', 'Creative Tasks') }}</title>

    <!-- Preload fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS & JS Bundle -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Language, Theme & Accent Initializer (Anti-FOUC & Persistence across F5) -->
    <script>
        (function() {
            // Theme initialization
            const savedTheme = localStorage.getItem('app_theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            }

            // Language & Direction initialization
            const savedLocale = localStorage.getItem('app_locale') || 'ar';
            document.documentElement.lang = savedLocale;
            document.documentElement.dir = savedLocale === 'ar' ? 'rtl' : 'ltr';

            // Accent Palette initialization
            const savedAccent = localStorage.getItem('app_accent') || 'sky';
            document.documentElement.setAttribute('data-accent', savedAccent);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased selection:bg-sky-500 selection:text-white transition-colors duration-200">
    @inertia
</body>
</html>
