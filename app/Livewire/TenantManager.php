<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tenant;
use App\Models\DbNode;
use Illuminate\Support\Str;

class TenantManager extends Component
{
    // Variables de Nodos
    public $node_name, $node_host, $node_user, $node_pass, $node_port = 3306;

    // Variables Tenant
    public $t_id, $t_domain, $t_db, $t_node_id;
    public $t_existing = false;
    public $t_run_migrations = false;
    public $t_run_seeders = false;
    public $t_user_limit = 10;
    public $t_admin_password;

    public function mount()
    {
        // Generar contraseña aleatoria por defecto
        $this->t_admin_password = Str::random(12);
    }

    public function updatedTExisting($value)
    {
        // Si marca "usar DB existente", dejar que el usuario decida
        // Por defecto desactivado
        if ($value) {
            $this->t_run_migrations = false;
            $this->t_run_seeders = false;
        }
    }

    public function generatePassword()
    {
        $this->t_admin_password = Str::random(12);
    }

    public function saveTenant()
    {
        $rules = [
            't_id' => 'required|unique:tenants,id|alpha_dash',
            't_domain' => 'required|alpha_dash',
            't_user_limit' => 'required|integer|min:1|max:1000',
            't_admin_password' => 'required|min:8',
        ];

        // Si es DB existente, obligamos a llenar t_db y t_node_id
        if ($this->t_existing) {
            $rules['t_db'] = 'required';
            $rules['t_node_id'] = 'required'; // Obligatorio si es existente
        }

        // Definimos mensajes personalizados
        $messages = [
            't_node_id.required' => 'Debes escoger un servidor de base de datos específico para conectar una DB existente.',
            't_db.required' => 'Escribe el nombre de la base de datos que ya existe en el servidor seleccionado.',
        ];

        $this->validate($rules, $messages);

        $dbName = $this->t_existing ? $this->t_db : 'tennat_' . $this->t_id; //aqui podras  nombar el inicio de   tus bases de datos

        // Crear tenant
        $tenant = new Tenant([
            'id' => $this->t_id,
            'tenancy_db_name' => $dbName,
            'db_node_id' => $this->t_node_id ?? null,
            'is_existing_db' => (bool) $this->t_existing,
            'user_limit' => $this->t_user_limit,
        ]);

        // ✅ Guardar configuraciones temporales usando setInternal()
        $tenant->setInternal('admin_password', $this->t_admin_password);
        
        // Si es DB existente, guardar las opciones del usuario
        // Si es DB nueva, siempre ejecutar migraciones y seeders
        if ($this->t_existing) {
            $tenant->setInternal('run_migrations', $this->t_run_migrations);
            $tenant->setInternal('run_seeders', $this->t_run_seeders);
        } else {
            // DB nueva: siempre true
            $tenant->setInternal('run_migrations', true);
            $tenant->setInternal('run_seeders', true);
        }

        // Pre-cargar el nodo si existe
        if ($tenant->db_node_id) {
            $tenant->setRelation('db_node', DbNode::find($tenant->db_node_id));
        }

        $tenant->save();

        // Crear dominio
        $tenant->domains()->create([
            'domain' => $this->t_domain . '.'.env('CENTRAL_DOMAIN')
        ]);

        // Preparar mensaje de éxito
        $shouldShowCredentials = !$this->t_existing || $this->t_run_seeders;
        
        if ($shouldShowCredentials) {
            session()->flash('credentials', [
                'email' => 'admin@' . $tenant->id . '.com',
                'password' => $this->t_admin_password,
                'url' => 'http://' . $this->t_domain . '.'.env('CENTRAL_DOMAIN')
            ]);
        }

        session()->flash('success', "✅ Tenant '{$tenant->id}' creado exitosamente.");

        $this->reset([
            't_id', 
            't_domain', 
            't_db', 
            't_node_id', 
            't_existing',
            't_run_migrations',
            't_run_seeders',
            't_user_limit',
            't_admin_password'
        ]);
        
        // Generar nueva contraseña para el próximo tenant
        $this->t_admin_password = Str::random(12);
        $this->t_user_limit = 10;
    }

    public function saveNode() 
    {
        $this->validate([
            'node_name' => 'required',
            'node_host' => 'required',
            'node_user' => 'required',
            'node_port' => 'required|integer',
        ]);

        DbNode::create([
            'name' => $this->node_name,
            'host' => $this->node_host,
            'username' => $this->node_user,
            'password' => $this->node_pass,
            'port' => $this->node_port,
        ]);
        
        $this->reset(['node_name', 'node_host', 'node_user', 'node_pass', 'node_port']);
        $this->node_port = 3306;
        
        session()->flash('success', '✅ Nodo guardado exitosamente.');
    }

    public function render()
    {
        return view('livewire.tenant-manager', [
            'tenants' => Tenant::with('domains', 'db_node')->latest()->get(),
            'nodes' => DbNode::all()
        ]);
    }
}