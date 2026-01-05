<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ 1) Company aanmaken (Vastgoed Foto Video)
        $company = Company::updateOrCreate(
            ['name' => 'Vastgoed Foto Video'],
            [
                'country_code' => 'NL',
                'website'      => 'https://www.vastgoedfotovideo.nl/',
                'email'        => 'info@vastgoedfotovideo.nl',
                'phone'        => '0318 891 586',
                'street'       => 'De Smalle Zijde',
                'house_number' => '5-10',
                'postal_code'  => '3903 LL',
                'city'         => 'Veenendaal',
                'kvk_number'   => '51978458',
                'vat_number'   => null,
                'trade_name'   => 'VastgoedFotoVideo',
                'legal_form'   => 'Eenmanszaak',
            ]
        );

        // ✅ 2) Users aanmaken + koppelen aan company
        $users = [
            [
                'name'  => 'Test Klant',
                'email' => 'test@klant.nl',
                'rol'   => 'klant',
            ],
            [
                'name'  => 'Test Team Manager',
                'email' => 'test@teammanager.nl',
                'rol'   => 'team-manager',
            ],
            [
                'name'  => 'Test Klant Manager',
                'email' => 'test@klantmanager.nl',
                'rol'   => 'client-manager',
            ],
            [
                'name'  => 'Test Fotograaf',
                'email' => 'test@fotograaf.nl',
                'rol'   => 'fotograaf',
            ],
            [
                'name'  => 'Test Admin',
                'email' => 'test@admin.nl',
                'rol'   => 'admin',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'       => $u['name'],
                    'password'   => Hash::make('password'),
                    'rol'        => $u['rol'],
                    'company_id' => $company->id,
                ]
            );
        }
    }
}
