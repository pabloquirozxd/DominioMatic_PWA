<?php

namespace Database\Seeders;

use App\Models\AccessRequest;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. EMPRESA PRINCIPAL
        // ==========================================

        $company = Company::updateOrCreate(
            ['slug' => 'dominiomatic'],
            [
                'name' => 'DominioMatic.com',
                'status' => 'active',
                'primary_color' => '#007AFF',
                'secondary_color' => '#5856D6',
            ]
        );

        // ==========================================
        // 2. SEGUNDA EMPRESA
        // ==========================================

        $companyDemo = Company::updateOrCreate(
            ['slug' => 'empresa-demo'],
            [
                'name' => 'Empresa Demo Corp',
                'status' => 'active',
                'primary_color' => '#10B981',
                'secondary_color' => '#059669',
            ]
        );

        // ==========================================
        // 3. USUARIOS
        // ==========================================

        $pablo = User::updateOrCreate(
            ['email' => 'pablo@quiroz.me'],
            [
                'name' => 'Pablo Quiroz',
                'password' => Hash::make('123456789'),
            ]
        );

        $ricardo = User::updateOrCreate(
            ['email' => 'ricardo@dominiomatic.com'],
            [
                'name' => 'Ricardo Quiroz',
                'password' => Hash::make('987654321'),
            ]
        );

        $juan = User::updateOrCreate(
            ['email' => 'juan.perez@cliente.com'],
            [
                'name' => 'Juan Pérez',
                'password' => Hash::make('123456789'),
            ]
        );

        // ==========================================
        // 4. MEMBRESÍAS Y ROLES EN EMPRESAS
        // ==========================================

        $company->users()->syncWithoutDetaching([
            $pablo->id => ['role' => 'owner'],
            $ricardo->id => ['role' => 'owner'],
        ]);

        $companyDemo->users()->syncWithoutDetaching([
            $pablo->id => ['role' => 'admin'],
        ]);

        // ==========================================
        // 5. CLIENTES Y CONTACTOS
        // ==========================================

        $clientBB = Client::updateOrCreate(
            [
                'company_id' => $company->id,
                'company_name' => 'Bolivian Business',
            ],
            [
                'type' => 'company',
                'language' => 'Español',
                'website' => 'bolivianbusiness.com.bo',
                'portal_enabled' => true,
            ]
        );

        $contactAlejandro = Contact::updateOrCreate(
            [
                'company_id' => $company->id,
                'email' => 'acalderon@bolivianbusiness.com.bo',
            ],
            [
                'first_name' => 'Alejandro',
                'last_name' => 'Calderón',
                'phone' => '70000000',
                'position' => 'Gerente General',
            ]
        );

        // Vincular en la tabla pivote client_contact
        $clientBB->contacts()->syncWithoutDetaching([
            $contactAlejandro->id => [
                'position' => 'Gerente General',
                'is_primary' => true,
            ],
        ]);

        // ==========================================
        // 6. CATÁLOGO BASE
        // ==========================================

        Product::updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Hosting Web',
            ],
            [
                'type' => 'service',
                'description' => 'Servicio de alojamiento web para sitios y aplicaciones.',
                'price_list' => 30.00,
                'is_infinite' => true,
                'stock' => 0,
            ]
        );

        Product::updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Desarrollo de Sitio Web',
            ],
            [
                'type' => 'service',
                'description' => 'Diseño, desarrollo e implementación de sitio web a medida.',
                'price_list' => 100.00,
                'is_infinite' => true,
                'stock' => 0,
            ]
        );

        Product::updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Registro de Dominio',
            ],
            [
                'type' => 'service',
                'description' => 'Gestión y registro anual de nombre de dominio.',
                'price_list' => 50.00,
                'is_infinite' => true,
                'stock' => 0,
            ]
        );

        // ==========================================
        // 7. SOLICITUD DE ACCESO
        // ==========================================

        AccessRequest::updateOrCreate(
            [
                'company_id' => $company->id,
                'email' => $juan->email,
            ],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $juan->id,
                'name' => $juan->name,
                'password' => $juan->password,
                'message' => 'Hola, me gustaría ingresar para revisar los servicios de hosting.',
                'status' => 'pending',
                'requested_at' => now(),
            ]
        );
    }
}