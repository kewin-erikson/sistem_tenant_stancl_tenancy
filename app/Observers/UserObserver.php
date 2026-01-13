<?php

namespace App\Observers;

use App\Models\User;
use Exception; // ← AGREGAR ESTA LÍNEA
use Illuminate\Support\Facades\Log; // ← AGREGAR ESTA LÍNEA TAMBIÉN
class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }
  /**
     * Se ejecuta ANTES de crear un usuario
     * Valida que no se exceda el límite de usuarios del tenant
     */
    public function creating(User $user): void
    {
        // Solo validar si estamos en contexto de tenant
        if (!function_exists('tenancy') || !tenancy()->initialized) {
            return;
        }

        // Obtener el tenant actual
        $tenant = tenancy()->tenant;
        
        // Obtener el límite configurado
        $limit = $tenant->user_limit ?? 10;
        
        // Contar cuántos usuarios existen ya
        $currentCount = User::count();

        Log::info("🔍 Validando límite de usuarios", [
            'tenant' => $tenant->id,
            'limite' => $limit,
            'actuales' => $currentCount,
        ]);

        // Si ya llegó o pasó el límite, bloqueamos la creación
        if ($currentCount >= $limit) {
            Log::warning("❌ Límite de usuarios alcanzado", [
                'tenant' => $tenant->id,
                'limite' => $limit,
            ]);

            // Lanzar excepción con mensaje amigable
            throw new \Illuminate\Validation\ValidationException(
                validator: \Illuminate\Support\Facades\Validator::make([], []),
                response: null,
                errorBag: 'default'
            );
        }

        Log::info("✅ Límite de usuarios OK - Procediendo a crear usuario");
    }
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
