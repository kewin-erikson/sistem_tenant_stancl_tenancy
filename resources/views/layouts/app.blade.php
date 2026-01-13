<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'App')</title>

  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-full bg-[#f5f5f3] text-[#1b1b18] dark:bg-[#0f0f0e] dark:text-[#f5f5f3]">

    <main class="min-h-full flex items-center justify-center px-4">
        <div class="w-full max-w-4xl bg-white dark:bg-[#161615] rounded-xl shadow-sm p-8">
            @yield('content')
        </div>
    </main>

</body>
</html>
