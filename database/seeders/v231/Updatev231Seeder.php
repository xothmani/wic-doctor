<?php

namespace Database\Seeders\v231;

use Illuminate\Database\Seeder;

class Updatev231Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call( UpdateAppointmentsSeeder::class);
        $this->call( AddJitsiMeeLinkAttributeAppSettingsTable::class);
    }
}
