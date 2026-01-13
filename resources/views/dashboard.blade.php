@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <h1 class="text-xl font-medium mb-6">Dashboard</h1>
    <div class="flex justify-end mb-4">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="px-4 py-2 text-sm font-medium rounded-md
                   bg-red-600 text-white
                   hover:bg-red-700
                   transition">
            Cerrar sesión
        </button>
    </form>
</div>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="p-6 bg-white dark:bg-[#161615] rounded-lg shadow-sm border dark:border-[#3E3E3A]">
            <div class="text-sm text-[#706f6c]">Usuarios</div>
            <div class="text-2xl font-medium">{{ \App\Models\User::count() }}</div>
        </div>

        <div class="p-6 bg-white dark:bg-[#161615] rounded-lg shadow-sm border dark:border-[#3E3E3A]">
            <div class="text-sm text-[#706f6c]">Tenant</div>
            <div class="text-sm font-medium break-all">
                {{ tenant()?->id }}
            </div>
        </div>

        <div class="p-6 bg-white dark:bg-[#161615] rounded-lg shadow-sm border dark:border-[#3E3E3A]">
            <div class="text-sm text-[#706f6c]">Email</div>
            <div class="text-sm font-medium">
                {{ auth()->user()->email }}
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-[#161615] rounded-lg shadow-sm border dark:border-[#3E3E3A] p-6">
        <h2 class="font-medium mb-4">Usuarios del tenant</h2>

        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="border-b dark:border-[#3E3E3A] text-left">
                    <th class="py-2">ID</th>
                    <th class="py-2">Nombre</th>
                    <th class="py-2">Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse (\App\Models\User::all() as $user)
                    <tr class="border-b last:border-0 dark:border-[#3E3E3A]">
                        <td class="py-2">{{ $user->id }}</td>
                        <td class="py-2">{{ $user->name }}</td>
                        <td class="py-2">{{ $user->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-4 text-center text-[#706f6c]">
                            No hay usuarios en este tenant
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
