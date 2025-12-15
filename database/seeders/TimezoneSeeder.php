<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimezoneSeeder extends Seeder
{
    protected $command;

    /**
     * Set the command instance.
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    public function run(): void
    {
        $timezones = [
            // Africa
            ['name' => 'West Africa Time', 'zone_name' => 'Africa/Lagos', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'WAT'],
            ['name' => 'Central Africa Time', 'zone_name' => 'Africa/Maputo', 'gmt_offset' => 7200, 'gmt_offset_name' => 'UTC+02:00', 'abbreviation' => 'CAT'],
            ['name' => 'Eastern European Time', 'zone_name' => 'Africa/Cairo', 'gmt_offset' => 7200, 'gmt_offset_name' => 'UTC+02:00', 'abbreviation' => 'EET'],
            ['name' => 'South Africa Standard Time', 'zone_name' => 'Africa/Johannesburg', 'gmt_offset' => 7200, 'gmt_offset_name' => 'UTC+02:00', 'abbreviation' => 'SAST'],
            ['name' => 'East Africa Time', 'zone_name' => 'Africa/Nairobi', 'gmt_offset' => 10800, 'gmt_offset_name' => 'UTC+03:00', 'abbreviation' => 'EAT'],
            ['name' => 'West Africa Time', 'zone_name' => 'Africa/Accra', 'gmt_offset' => 0, 'gmt_offset_name' => 'UTC+00:00', 'abbreviation' => 'GMT'],
            ['name' => 'Morocco Standard Time', 'zone_name' => 'Africa/Casablanca', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'WEST'],

            // America - North America
            ['name' => 'Eastern Standard Time', 'zone_name' => 'America/New_York', 'gmt_offset' => -18000, 'gmt_offset_name' => 'UTC-05:00', 'abbreviation' => 'EST'],
            ['name' => 'Central Standard Time', 'zone_name' => 'America/Chicago', 'gmt_offset' => -21600, 'gmt_offset_name' => 'UTC-06:00', 'abbreviation' => 'CST'],
            ['name' => 'Mountain Standard Time', 'zone_name' => 'America/Denver', 'gmt_offset' => -25200, 'gmt_offset_name' => 'UTC-07:00', 'abbreviation' => 'MST'],
            ['name' => 'Pacific Standard Time', 'zone_name' => 'America/Los_Angeles', 'gmt_offset' => -28800, 'gmt_offset_name' => 'UTC-08:00', 'abbreviation' => 'PST'],
            ['name' => 'Alaska Standard Time', 'zone_name' => 'America/Anchorage', 'gmt_offset' => -32400, 'gmt_offset_name' => 'UTC-09:00', 'abbreviation' => 'AKST'],
            ['name' => 'Hawaii-Aleutian Standard Time', 'zone_name' => 'Pacific/Honolulu', 'gmt_offset' => -36000, 'gmt_offset_name' => 'UTC-10:00', 'abbreviation' => 'HST'],
            ['name' => 'Atlantic Standard Time', 'zone_name' => 'America/Halifax', 'gmt_offset' => -14400, 'gmt_offset_name' => 'UTC-04:00', 'abbreviation' => 'AST'],
            ['name' => 'Newfoundland Standard Time', 'zone_name' => 'America/St_Johns', 'gmt_offset' => -12600, 'gmt_offset_name' => 'UTC-03:30', 'abbreviation' => 'NST'],

            // America - Central & South America
            ['name' => 'Mexico City Time', 'zone_name' => 'America/Mexico_City', 'gmt_offset' => -21600, 'gmt_offset_name' => 'UTC-06:00', 'abbreviation' => 'CST'],
            ['name' => 'Colombia Time', 'zone_name' => 'America/Bogota', 'gmt_offset' => -18000, 'gmt_offset_name' => 'UTC-05:00', 'abbreviation' => 'COT'],
            ['name' => 'Peru Time', 'zone_name' => 'America/Lima', 'gmt_offset' => -18000, 'gmt_offset_name' => 'UTC-05:00', 'abbreviation' => 'PET'],
            ['name' => 'Argentina Time', 'zone_name' => 'America/Argentina/Buenos_Aires', 'gmt_offset' => -10800, 'gmt_offset_name' => 'UTC-03:00', 'abbreviation' => 'ART'],
            ['name' => 'Brasilia Time', 'zone_name' => 'America/Sao_Paulo', 'gmt_offset' => -10800, 'gmt_offset_name' => 'UTC-03:00', 'abbreviation' => 'BRT'],
            ['name' => 'Chile Standard Time', 'zone_name' => 'America/Santiago', 'gmt_offset' => -10800, 'gmt_offset_name' => 'UTC-03:00', 'abbreviation' => 'CLT'],
            ['name' => 'Venezuela Time', 'zone_name' => 'America/Caracas', 'gmt_offset' => -14400, 'gmt_offset_name' => 'UTC-04:00', 'abbreviation' => 'VET'],

            // Asia - Middle East
            ['name' => 'Arabia Standard Time', 'zone_name' => 'Asia/Riyadh', 'gmt_offset' => 10800, 'gmt_offset_name' => 'UTC+03:00', 'abbreviation' => 'AST'],
            ['name' => 'Gulf Standard Time', 'zone_name' => 'Asia/Dubai', 'gmt_offset' => 14400, 'gmt_offset_name' => 'UTC+04:00', 'abbreviation' => 'GST'],
            ['name' => 'Iran Standard Time', 'zone_name' => 'Asia/Tehran', 'gmt_offset' => 12600, 'gmt_offset_name' => 'UTC+03:30', 'abbreviation' => 'IRST'],
            ['name' => 'Israel Standard Time', 'zone_name' => 'Asia/Jerusalem', 'gmt_offset' => 7200, 'gmt_offset_name' => 'UTC+02:00', 'abbreviation' => 'IST'],
            ['name' => 'Turkey Time', 'zone_name' => 'Europe/Istanbul', 'gmt_offset' => 10800, 'gmt_offset_name' => 'UTC+03:00', 'abbreviation' => 'TRT'],

            // Asia - South & Southeast Asia
            ['name' => 'Pakistan Standard Time', 'zone_name' => 'Asia/Karachi', 'gmt_offset' => 18000, 'gmt_offset_name' => 'UTC+05:00', 'abbreviation' => 'PKT'],
            ['name' => 'India Standard Time', 'zone_name' => 'Asia/Kolkata', 'gmt_offset' => 19800, 'gmt_offset_name' => 'UTC+05:30', 'abbreviation' => 'IST'],
            ['name' => 'Nepal Time', 'zone_name' => 'Asia/Kathmandu', 'gmt_offset' => 20700, 'gmt_offset_name' => 'UTC+05:45', 'abbreviation' => 'NPT'],
            ['name' => 'Bangladesh Standard Time', 'zone_name' => 'Asia/Dhaka', 'gmt_offset' => 21600, 'gmt_offset_name' => 'UTC+06:00', 'abbreviation' => 'BST'],
            ['name' => 'Myanmar Time', 'zone_name' => 'Asia/Yangon', 'gmt_offset' => 23400, 'gmt_offset_name' => 'UTC+06:30', 'abbreviation' => 'MMT'],
            ['name' => 'Indochina Time', 'zone_name' => 'Asia/Bangkok', 'gmt_offset' => 25200, 'gmt_offset_name' => 'UTC+07:00', 'abbreviation' => 'ICT'],
            ['name' => 'Vietnam Standard Time', 'zone_name' => 'Asia/Ho_Chi_Minh', 'gmt_offset' => 25200, 'gmt_offset_name' => 'UTC+07:00', 'abbreviation' => 'ICT'],
            ['name' => 'Indonesia Western Time', 'zone_name' => 'Asia/Jakarta', 'gmt_offset' => 25200, 'gmt_offset_name' => 'UTC+07:00', 'abbreviation' => 'WIB'],

            // Asia - East Asia
            ['name' => 'China Standard Time', 'zone_name' => 'Asia/Shanghai', 'gmt_offset' => 28800, 'gmt_offset_name' => 'UTC+08:00', 'abbreviation' => 'CST'],
            ['name' => 'Hong Kong Time', 'zone_name' => 'Asia/Hong_Kong', 'gmt_offset' => 28800, 'gmt_offset_name' => 'UTC+08:00', 'abbreviation' => 'HKT'],
            ['name' => 'Singapore Standard Time', 'zone_name' => 'Asia/Singapore', 'gmt_offset' => 28800, 'gmt_offset_name' => 'UTC+08:00', 'abbreviation' => 'SGT'],
            ['name' => 'Philippine Time', 'zone_name' => 'Asia/Manila', 'gmt_offset' => 28800, 'gmt_offset_name' => 'UTC+08:00', 'abbreviation' => 'PHT'],
            ['name' => 'Malaysia Time', 'zone_name' => 'Asia/Kuala_Lumpur', 'gmt_offset' => 28800, 'gmt_offset_name' => 'UTC+08:00', 'abbreviation' => 'MYT'],
            ['name' => 'Taiwan Standard Time', 'zone_name' => 'Asia/Taipei', 'gmt_offset' => 28800, 'gmt_offset_name' => 'UTC+08:00', 'abbreviation' => 'CST'],
            ['name' => 'Japan Standard Time', 'zone_name' => 'Asia/Tokyo', 'gmt_offset' => 32400, 'gmt_offset_name' => 'UTC+09:00', 'abbreviation' => 'JST'],
            ['name' => 'Korea Standard Time', 'zone_name' => 'Asia/Seoul', 'gmt_offset' => 32400, 'gmt_offset_name' => 'UTC+09:00', 'abbreviation' => 'KST'],

            // Europe - Western Europe
            ['name' => 'Greenwich Mean Time', 'zone_name' => 'Europe/London', 'gmt_offset' => 0, 'gmt_offset_name' => 'UTC+00:00', 'abbreviation' => 'GMT'],
            ['name' => 'Irish Standard Time', 'zone_name' => 'Europe/Dublin', 'gmt_offset' => 0, 'gmt_offset_name' => 'UTC+00:00', 'abbreviation' => 'GMT'],
            ['name' => 'Western European Time', 'zone_name' => 'Europe/Lisbon', 'gmt_offset' => 0, 'gmt_offset_name' => 'UTC+00:00', 'abbreviation' => 'WET'],

            // Europe - Central Europe
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Paris', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Berlin', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Rome', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Madrid', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Amsterdam', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Brussels', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Zurich', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Vienna', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],
            ['name' => 'Central European Time', 'zone_name' => 'Europe/Warsaw', 'gmt_offset' => 3600, 'gmt_offset_name' => 'UTC+01:00', 'abbreviation' => 'CET'],

            // Europe - Eastern Europe
            ['name' => 'Eastern European Time', 'zone_name' => 'Europe/Athens', 'gmt_offset' => 7200, 'gmt_offset_name' => 'UTC+02:00', 'abbreviation' => 'EET'],
            ['name' => 'Eastern European Time', 'zone_name' => 'Europe/Bucharest', 'gmt_offset' => 7200, 'gmt_offset_name' => 'UTC+02:00', 'abbreviation' => 'EET'],
            ['name' => 'Eastern European Time', 'zone_name' => 'Europe/Helsinki', 'gmt_offset' => 7200, 'gmt_offset_name' => 'UTC+02:00', 'abbreviation' => 'EET'],
            ['name' => 'Moscow Standard Time', 'zone_name' => 'Europe/Moscow', 'gmt_offset' => 10800, 'gmt_offset_name' => 'UTC+03:00', 'abbreviation' => 'MSK'],

            // Oceania
            ['name' => 'Australian Western Standard Time', 'zone_name' => 'Australia/Perth', 'gmt_offset' => 28800, 'gmt_offset_name' => 'UTC+08:00', 'abbreviation' => 'AWST'],
            ['name' => 'Australian Central Standard Time', 'zone_name' => 'Australia/Adelaide', 'gmt_offset' => 37800, 'gmt_offset_name' => 'UTC+10:30', 'abbreviation' => 'ACST'],
            ['name' => 'Australian Eastern Standard Time', 'zone_name' => 'Australia/Sydney', 'gmt_offset' => 39600, 'gmt_offset_name' => 'UTC+11:00', 'abbreviation' => 'AEST'],
            ['name' => 'Australian Eastern Standard Time', 'zone_name' => 'Australia/Melbourne', 'gmt_offset' => 39600, 'gmt_offset_name' => 'UTC+11:00', 'abbreviation' => 'AEST'],
            ['name' => 'Australian Eastern Standard Time', 'zone_name' => 'Australia/Brisbane', 'gmt_offset' => 36000, 'gmt_offset_name' => 'UTC+10:00', 'abbreviation' => 'AEST'],
            ['name' => 'New Zealand Standard Time', 'zone_name' => 'Pacific/Auckland', 'gmt_offset' => 46800, 'gmt_offset_name' => 'UTC+13:00', 'abbreviation' => 'NZST'],
            ['name' => 'Fiji Time', 'zone_name' => 'Pacific/Fiji', 'gmt_offset' => 43200, 'gmt_offset_name' => 'UTC+12:00', 'abbreviation' => 'FJT'],

            // UTC & Special
            ['name' => 'Coordinated Universal Time', 'zone_name' => 'UTC', 'gmt_offset' => 0, 'gmt_offset_name' => 'UTC+00:00', 'abbreviation' => 'UTC'],
        ];

        $timezonesTable = config('worldable.tables.timezones', 'world_timezones');

        $this->command?->info('Seeding '.count($timezones).' timezones...');

        foreach ($timezones as $timezone) {
            DB::table($timezonesTable)->insertOrIgnore([
                'name' => $timezone['name'],
                'zone_name' => $timezone['zone_name'],
                'gmt_offset' => $timezone['gmt_offset'],
                'gmt_offset_name' => $timezone['gmt_offset_name'],
                'abbreviation' => $timezone['abbreviation'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command?->info('Seeded '.count($timezones).' timezones successfully.');
    }
}
