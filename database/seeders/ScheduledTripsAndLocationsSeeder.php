<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\City;
use App\Models\Route;
use App\Models\TruckType;
use App\Models\ScheduledTrip;

class ScheduledTripsAndLocationsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Countries
        $kuwait = Country::firstOrCreate(
            ['name_en' => 'Kuwait'],
            ['name_ar' => 'الكويت', 'code' => 'KW', 'is_active' => 1]
        );

        $saudi = Country::firstOrCreate(
            ['name_en' => 'Saudi Arabia'],
            ['name_ar' => 'السعودية', 'code' => 'SA', 'is_active' => 1]
        );

        $uae = Country::firstOrCreate(
            ['name_en' => 'UAE'],
            ['name_ar' => 'الإمارات', 'code' => 'AE', 'is_active' => 1]
        );

        $qatar = Country::firstOrCreate(
            ['name_en' => 'Qatar'],
            ['name_ar' => 'قطر', 'code' => 'QA', 'is_active' => 1]
        );

        // 2. Cities
        $jahraa = City::firstOrCreate(
            ['country_id' => $kuwait->id, 'name_en' => 'Jahraa'],
            ['name_ar' => 'الجهراء', 'is_active' => 1]
        );

        $farwaniya = City::firstOrCreate(
            ['country_id' => $kuwait->id, 'name_en' => 'Farwaniya'],
            ['name_ar' => 'الفروانية', 'is_active' => 1]
        );
        // Also update Frawnya name_en to Farwaniya if id=1
        City::where('id', 1)->update(['name_en' => 'Farwaniya', 'name_ar' => 'الفروانية']);

        $shuwaikh = City::firstOrCreate(
            ['country_id' => $kuwait->id, 'name_en' => 'Shuwaikh'],
            ['name_ar' => 'الشويخ', 'is_active' => 1]
        );

        $ahmadi = City::firstOrCreate(
            ['country_id' => $kuwait->id, 'name_en' => 'Ahmadi'],
            ['name_ar' => 'الأحمدي', 'is_active' => 1]
        );

        $salmiya = City::firstOrCreate(
            ['country_id' => $kuwait->id, 'name_en' => 'Salmiya'],
            ['name_ar' => 'السالمية', 'is_active' => 1]
        );

        $riyadh = City::firstOrCreate(
            ['country_id' => $saudi->id, 'name_en' => 'Riyadh'],
            ['name_ar' => 'الرياض', 'is_active' => 1]
        );

        $dammam = City::firstOrCreate(
            ['country_id' => $saudi->id, 'name_en' => 'Dammam'],
            ['name_ar' => 'الدمام', 'is_active' => 1]
        );

        $dubai = City::firstOrCreate(
            ['country_id' => $uae->id, 'name_en' => 'Dubai'],
            ['name_ar' => 'دبي', 'is_active' => 1]
        );

        $doha = City::firstOrCreate(
            ['country_id' => $qatar->id, 'name_en' => 'Doha'],
            ['name_ar' => 'الدوحة', 'is_active' => 1]
        );

        // 3. Truck Types
        $trailer = TruckType::where('name_en', 'Trailer')->first();
        $flatbed = TruckType::where('name_en', 'Flatbed')->first();
        $reefer  = TruckType::where('name_en', 'Reefer')->first();

        // 4. Routes & Scheduled Trips
        // Route A: Jahraa -> Farwaniya (Local Kuwait)
        $routeJahraaFarwaniya = Route::firstOrCreate(
            [
                'origin_city_id'      => $jahraa->id,
                'destination_city_id' => $farwaniya->id,
            ],
            [
                'quote_type'             => 'local',
                'origin_country_id'      => $kuwait->id,
                'destination_country_id' => $kuwait->id,
                'estimated_distance'     => '35.00',
                'status'                 => 'active',
            ]
        );

        if ($trailer) {
            ScheduledTrip::firstOrCreate(
                [
                    'route_id'      => $routeJahraaFarwaniya->id,
                    'truck_type_id' => $trailer->id,
                    'price'         => 450.00,
                ],
                [
                    'number_of_trucks'   => 1,
                    'total_weight_ton'   => 24.00,
                    'trip_date'          => date('Y-m-d', strtotime('+2 days')),
                    'trip_time'          => '08:00:00',
                    'total_capacity'     => 10,
                    'available_capacity' => 6,
                    'status'             => 'published',
                    'customs_clearance'  => true,
                    'notes'              => 'رحلة مجدولة سريعة بين الجهراء والفروانية',
                ]
            );
        }

        if ($flatbed) {
            ScheduledTrip::firstOrCreate(
                [
                    'route_id'      => $routeJahraaFarwaniya->id,
                    'truck_type_id' => $flatbed->id,
                    'price'         => 480.00,
                ],
                [
                    'number_of_trucks'   => 1,
                    'total_weight_ton'   => 20.00,
                    'trip_date'          => date('Y-m-d', strtotime('+3 days')),
                    'trip_time'          => '14:00:00',
                    'total_capacity'     => 8,
                    'available_capacity' => 4,
                    'status'             => 'published',
                    'customs_clearance'  => false,
                    'notes'              => 'رحلة مسطحة بدون تخليص جمركي',
                ]
            );
        }

        if ($reefer) {
            ScheduledTrip::firstOrCreate(
                [
                    'route_id'      => $routeJahraaFarwaniya->id,
                    'truck_type_id' => $reefer->id,
                    'price'         => 520.00,
                ],
                [
                    'number_of_trucks'   => 1,
                    'total_weight_ton'   => 18.00,
                    'trip_date'          => date('Y-m-d', strtotime('+4 days')),
                    'trip_time'          => '06:00:00',
                    'total_capacity'     => 6,
                    'available_capacity' => 5,
                    'status'             => 'published',
                    'customs_clearance'  => true,
                    'notes'              => 'رحلة مبردة مع تخليص جمركي',
                ]
            );
        }

        // Route B: Farwaniya -> Dammam (International)
        $routeFarwaniyaDammam = Route::firstOrCreate(
            [
                'origin_city_id'      => $farwaniya->id,
                'destination_city_id' => $dammam->id,
            ],
            [
                'quote_type'             => 'international',
                'origin_country_id'      => $kuwait->id,
                'destination_country_id' => $saudi->id,
                'estimated_distance'     => '420.00',
                'status'                 => 'active',
            ]
        );

        if ($trailer) {
            ScheduledTrip::firstOrCreate(
                [
                    'route_id'      => $routeFarwaniyaDammam->id,
                    'truck_type_id' => $trailer->id,
                    'price'         => 680.00,
                ],
                [
                    'number_of_trucks'   => 1,
                    'total_weight_ton'   => 24.00,
                    'trip_date'          => date('Y-m-d', strtotime('+1 day')),
                    'trip_time'          => '08:00:00',
                    'total_capacity'     => 10,
                    'available_capacity' => 6,
                    'status'             => 'published',
                    'customs_clearance'  => true,
                    'notes'              => 'خط الكويت الدمام المباشر',
                ]
            );
        }

        if ($flatbed) {
            ScheduledTrip::firstOrCreate(
                [
                    'route_id'      => $routeFarwaniyaDammam->id,
                    'truck_type_id' => $flatbed->id,
                    'price'         => 720.00,
                ],
                [
                    'number_of_trucks'   => 1,
                    'total_weight_ton'   => 20.00,
                    'trip_date'          => date('Y-m-d', strtotime('+2 days')),
                    'trip_time'          => '14:00:00',
                    'total_capacity'     => 8,
                    'available_capacity' => 6,
                    'status'             => 'published',
                    'customs_clearance'  => true,
                    'notes'              => 'خط الكويت الدمام مسطحة',
                ]
            );
        }

        // Route C: Jahraa -> Dammam (International)
        $routeJahraaDammam = Route::firstOrCreate(
            [
                'origin_city_id'      => $jahraa->id,
                'destination_city_id' => $dammam->id,
            ],
            [
                'quote_type'             => 'international',
                'origin_country_id'      => $kuwait->id,
                'destination_country_id' => $saudi->id,
                'estimated_distance'     => '440.00',
                'status'                 => 'active',
            ]
        );

        if ($trailer) {
            ScheduledTrip::firstOrCreate(
                [
                    'route_id'      => $routeJahraaDammam->id,
                    'truck_type_id' => $trailer->id,
                    'price'         => 690.00,
                ],
                [
                    'number_of_trucks'   => 1,
                    'total_weight_ton'   => 24.00,
                    'trip_date'          => date('Y-m-d', strtotime('+2 days')),
                    'trip_time'          => '09:00:00',
                    'total_capacity'     => 10,
                    'available_capacity' => 7,
                    'status'             => 'published',
                    'customs_clearance'  => true,
                    'notes'              => 'من الجهراء إلى الدمام مباشرة',
                ]
            );
        }

        // Route D: Jahraa -> Riyadh (International)
        $routeJahraaRiyadh = Route::firstOrCreate(
            [
                'origin_city_id'      => $jahraa->id,
                'destination_city_id' => $riyadh->id,
            ],
            [
                'quote_type'             => 'international',
                'origin_country_id'      => $kuwait->id,
                'destination_country_id' => $saudi->id,
                'estimated_distance'     => '790.00',
                'status'                 => 'active',
            ]
        );

        if ($trailer) {
            ScheduledTrip::firstOrCreate(
                [
                    'route_id'      => $routeJahraaRiyadh->id,
                    'truck_type_id' => $trailer->id,
                    'price'         => 800.00,
                ],
                [
                    'number_of_trucks'   => 2,
                    'total_weight_ton'   => 48.00,
                    'trip_date'          => date('Y-m-d', strtotime('+2 days')),
                    'trip_time'          => '10:00:00',
                    'total_capacity'     => 12,
                    'available_capacity' => 8,
                    'status'             => 'published',
                    'customs_clearance'  => true,
                    'notes'              => 'شاحنتان تريلا من الجهراء إلى الرياض',
                ]
            );
        }
    }
}
