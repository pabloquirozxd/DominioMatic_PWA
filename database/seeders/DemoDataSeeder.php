<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'dominiomatic'],
            [
                'name' => 'DominioMatic',
                'primary_color' => '#007AFF',
                'secondary_color' => '#5856D6',
            ]
        );

        User::updateOrCreate(
            ['email' => 'prueba@gmail.com'],
            [
                'name' => 'prueba',
                'company_id' => $company->id,
                'password' => Hash::make('Password123*'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'aguilar@gmail.com'],
            [
                'name' => 'Edwin',
                'company_id' => $company->id,
                'password' => Hash::make('Password123*'),
            ]
        );

        $contact = Contact::updateOrCreate(
            ['email' => 'pablo@quiroz.me'],
            [
                'company_id' => $company->id,
                'first_name' => 'Pablo',
                'last_name' => 'Quiroz',
                'phone' => '77072256',
                'type' => 'secondary',
                'position' => 'IT',
            ]
        );

        Contact::updateOrCreate(
            ['email' => 'aguilar@gmail.com'],
            [
                'company_id' => $company->id,
                'first_name' => 'Edwin',
                'last_name' => 'Aguilar',
                'phone' => '77077777',
                'type' => 'primary',
                'position' => 'Docente',
            ]
        );

        $product = Product::updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Hosting',
            ],
            [
                'description' => 'Hosting básico',
                'price_list' => 20,
                'is_infinite' => false,
                'stock' => 100,
            ]
        );

        Subscription::updateOrCreate(
            [
                'company_id' => $company->id,
                'contact_id' => $contact->id,
                'product_id' => $product->id,
            ],
            [
                'price_list' => 10,
                'discount' => 0,
                'starts_at' => '2026-06-19',
                'expires_at' => '2026-07-19',
                'status' => 'active',
            ]
        );
    }
}