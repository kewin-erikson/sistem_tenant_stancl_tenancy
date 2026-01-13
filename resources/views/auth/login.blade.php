@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="max-w-md mx-auto">

    <h1 class="text-xl font-medium mb-6">
        Iniciar sesión
    </h1>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email -->
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-md border px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border-[#e3e3e0] dark:border-[#3E3E3A]
                       focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white"
            >
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm mb-1">Contraseña</label>
            <input
                type="password"
                name="password"
                required
                class="w-full rounded-md border px-3 py-2 text-sm
                       bg-white dark:bg-[#161615]
                       border-[#e3e3e0] dark:border-[#3E3E3A]
                       focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white"
            >
        </div>

        <!-- Remember -->
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember">
            Recordarme
        </label>

        <!-- Button -->
        <button
            type="submit"
            class="w-full px-4 py-2 rounded-md border
                   hover:border-black dark:hover:border-white
                   transition"
        >
            Entrar
        </button>
    </form>

    <p class="text-sm text-center text-[#706f6c] mt-6">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" class="underline hover:text-black dark:hover:text-white">
            Regístrate
        </a>
    </p>

</div>
@endsection
