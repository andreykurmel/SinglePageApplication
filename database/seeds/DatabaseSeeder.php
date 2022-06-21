<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(CountriesSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(TableSeeder::class);
        $this->call(TableFieldsSeeder::class);
        $this->call(AdditionalFieldsSeeder::class);
        $this->call(UsagesAndPaymentsSeeder::class);
        $this->call(PlansAndAddonsSeeder::class);
        $this->call(PlanFeaturesSeeder::class);
        $this->call(TableFieldLinksSeeder::class);
        $this->call(UnitConversionSeeder::class);
        $this->call(UserConnectonsAndCloudsSeeder::class);
        $this->call(TableBackupsSeeder::class);
        $this->call(UnitsSeeder::class);
        $this->call(UserInvitationsSeeder::class);
        $this->call(CorrespondencesSeeder::class);
        $this->call(LinkSysTablesSeeder::class);
        $this->call(ThemesSeeder::class);
        $this->call(EmailSettingSeeder::class);
        $this->call(UploadingFileFormatsSeeder::class);

        Model::reguard();
    }
}
