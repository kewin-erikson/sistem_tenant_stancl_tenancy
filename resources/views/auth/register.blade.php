@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('content')
<div class="max-w-md mx-auto">

    
    <h1 class="text-xl font-medium mb-6">
        Crear cuenta
    </h1>
      @php
        $tenant = tenancy()->tenant?? null;
        $currentUsers = \App\Models\User::count();
    @endphp
    @if(!empty($tenant) && ( $currentUsers < $tenant->user_limit))
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-sm mb-1">Nombre</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full rounded-md border px-3 py-2 text-sm
                        bg-white dark:bg-[#161615]
                        border-[#e3e3e0] dark:border-[#3E3E3A]
                        focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white"
                >
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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

            <!-- Confirm -->
            <div>
                <label class="block text-sm mb-1">Confirmar contraseña</label>
                <input
                    type="password"
                    name="password_confirmation"
                    required
                    class="w-full rounded-md border px-3 py-2 text-sm
                        bg-white dark:bg-[#161615]
                        border-[#e3e3e0] dark:border-[#3E3E3A]
                        focus:outline-none focus:ring-1 focus:ring-black dark:focus:ring-white"
                >
            </div>

            <button
                type="submit"
                class="w-full px-4 py-2 rounded-md border
                    hover:border-black dark:hover:border-white
                    transition"
            >
                Registrarme
            </button>
        </form>
       @endif

    <p class="text-sm text-center text-[#706f6c] mt-6">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="underline hover:text-black dark:hover:text-white">
            Iniciar sesión
        </a>
    </p>

</div>
@endsection
