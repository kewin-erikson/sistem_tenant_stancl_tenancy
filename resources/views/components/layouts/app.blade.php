<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gesthor Admin Central</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-slate-100 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>