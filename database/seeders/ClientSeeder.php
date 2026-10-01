<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Company;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // Empresa 1: BS Logistics
            Client::withoutGlobalScopes()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'company_name' => "BS Logistics ({$company->name})",
                ],
                [
                    'type' => 'company',
                    'language' => 'Español',
                    'website' => 'https://bslogistics.com',
                    'portal_enabled' => false,
                ]
            );

            // Empresa 2: Torre Fuerte
            Client::withoutGlobalScopes()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'company_name' => "Torre Fuerte Ekklesia ({$company->name})",
                ],
                [
                    'type' => 'company',
                    'language' => 'Español',
                    'website' => 'https://torrefuerte.bo',
                    'portal_enabled' => false,
                ]
            );
        }
    }
}