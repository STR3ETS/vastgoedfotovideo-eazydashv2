<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Boyd Halfman',
            'email' => 'boyd@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'admin',
        ]);
        User::create([
            'name' => 'Yael Scholten',
            'email' => 'yael@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'admin',
        ]);
        User::create([
            'name' => 'Raphael Muskitta',
            'email' => 'raphael@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'admin',
        ]);
        User::create([
            'name' => 'Martijn Visser',
            'email' => 'martijn@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'admin',
        ]);
        User::create([
            'name' => 'Johnny Muskitta',
            'email' => 'johnny@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'medewerker',
        ]);
        User::create([
            'name' => 'Joris Lindner',
            'email' => 'joris@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'medewerker',
        ]);
        User::create([
            'name' => 'Laurien Pesulima',
            'email' => 'laurina@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'medewerker',
        ]);
        User::create([
            'name' => 'Laurenzo Soemopawiro',
            'email' => 'laurenzo@eazyonline.nl',
            'password' => Hash::make('password'),
            'rol' => 'medewerker',
        ]);
    }
}
