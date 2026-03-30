<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, text
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed publicly
            $table->boolean('is_encrypted')->default(false); // Should value be encrypted
            $table->timestamps();

            $table->index(['key', 'group']);
        });

        // Insert default settings
        $this->insertDefaultSettings();
    }

    private function insertDefaultSettings()
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'KTVTC System',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name',
                'is_public' => true,
            ],
            [
                'key' => 'app_url',
                'value' => config('app.url'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application URL',
                'is_public' => true,
            ],
            [
                'key' => 'timezone',
                'value' => config('app.timezone'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'System timezone',
                'is_public' => false,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Maintenance mode status',
                'is_public' => true,
            ],
            [
                'key' => 'registration_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'auth',
                'description' => 'Allow user registration',
                'is_public' => true,
            ],
            [
                'key' => 'email_verification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'auth',
                'description' => 'Require email verification',
                'is_public' => true,
            ],
            [
                'key' => 'super_admin_email',
                'value' => 'superadmin@ktvtc.ac.ke',
                'type' => 'string',
                'group' => 'security',
                'description' => 'Primary super admin email',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'backup_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable automatic backups',
                'is_public' => false,
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Backup frequency (daily, weekly, monthly)',
                'is_public' => false,
            ],
            [
                'key' => 'log_retention_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Number of days to keep logs',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->insert($setting);
        }
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
};
