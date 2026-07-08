<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <script>
            // Runs before first paint so the page never flashes the wrong
            // theme; ThemeProvider re-derives and reconciles this in React
            // state on mount rather than trusting the DOM class as-is.
            (function () {
                var stored = localStorage.getItem('shift_distribution.theme');
                var isDark = stored === 'dark' || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            })();
        </script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @fonts
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/src/main.tsx'])
    </head>
    <body class="font-sans antialiased">
        <div id="app"></div>
    </body>
</html>
