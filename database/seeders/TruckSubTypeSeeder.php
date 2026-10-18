<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TruckSubType;

class TruckSubTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // 1. Trailer (جَرَّار)
            [
                'truck_type_id' => 1,
                'name_en'       => 'Flatbed Trailer (13.6m)',
                'name_ar'       => 'تريلا سطحة (13.6 متر)',
                'max_payload'   => 28.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 1,
                'name_en'       => 'Lowbed Heavy Equipment Trailer',
                'name_ar'       => 'تريلا لوبد للمعدات الثقيلة والإنشائية',
                'max_payload'   => 60.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 1,
                'name_en'       => 'Tipper / Dump Trailer',
                'name_ar'       => 'تريلا قلاب لمواد البناء والحصى',
                'max_payload'   => 32.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 1,
                'name_en'       => 'Curtainside Trailer (Tautliner)',
                'name_ar'       => 'تريلا ستارة لتحميل جانبي سريع',
                'max_payload'   => 26.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 1,
                'name_en'       => 'Liquid & Fuel Tanker Trailer',
                'name_ar'       => 'تريلا صهريج محروقات وسوائل',
                'max_payload'   => 30.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 1,
                'name_en'       => 'Side Wall Cargo Trailer',
                'name_ar'       => 'تريلا جوانب شبكية لنقل البضائع',
                'max_payload'   => 28.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 1,
                'name_en'       => 'Extendable Mega Trailer',
                'name_ar'       => 'تريلا ممتدة للأحمال الطويلة والخاصة',
                'max_payload'   => 45.00,
                'is_active'     => 1,
            ],

            // 2. Container (حاوية)
            [
                'truck_type_id' => 2,
                'name_en'       => '20ft Standard Dry Container',
                'name_ar'       => 'حاوية 20 قدم جافة قياسية',
                'max_payload'   => 22.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 2,
                'name_en'       => '40ft Standard Dry Container',
                'name_ar'       => 'حاوية 40 قدم جافة قياسية',
                'max_payload'   => 26.50,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 2,
                'name_en'       => '40ft High Cube Container (HQ)',
                'name_ar'       => 'حاوية 40 قدم هاي كيوب مرتفعة',
                'max_payload'   => 28.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 2,
                'name_en'       => '20ft ISO Tank Container',
                'name_ar'       => 'حاوية خزان إيزوتانك 20 قدم للمواد السائلة',
                'max_payload'   => 24.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 2,
                'name_en'       => '40ft Refrigerated Container (Reefer)',
                'name_ar'       => 'حاوية 40 قدم مبردة للمنتجات الغذائية',
                'max_payload'   => 27.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 2,
                'name_en'       => 'Flat Rack Container (Special Cargo)',
                'name_ar'       => 'حاوية فلات راك للمعدات والآليات الضخمة',
                'max_payload'   => 30.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 2,
                'name_en'       => 'Open Top Container',
                'name_ar'       => 'حاوية مكشوفة السقف للتحميل الرأسي',
                'max_payload'   => 28.00,
                'is_active'     => 1,
            ],

            // 3. Flatbed (مسطحة)
            [
                'truck_type_id' => 3,
                'name_en'       => 'Standard Flatbed (13.6m)',
                'name_ar'       => 'سطحة عادية قياسية (13.6 متر)',
                'max_payload'   => 25.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 3,
                'name_en'       => 'Side Wall Flatbed (Drop Sides)',
                'name_ar'       => 'سطحة بجوانب منخفضة قابلة للفتح',
                'max_payload'   => 25.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 3,
                'name_en'       => 'Step Deck / Drop Deck Flatbed',
                'name_ar'       => 'سطحة دروب ديك ثنائية المستويات',
                'max_payload'   => 28.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 3,
                'name_en'       => 'Hydraulic Recovery Flatbed',
                'name_ar'       => 'سطحة هيدروليك لنقل السيارات والمعدات',
                'max_payload'   => 7.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 3,
                'name_en'       => 'Multi-Car Carrier Flatbed',
                'name_ar'       => 'سطحة ناقلة سيارات متعددة المركبات',
                'max_payload'   => 15.00,
                'is_active'     => 1,
            ],

            // 4. Reefer (المبردة)
            [
                'truck_type_id' => 4,
                'name_en'       => 'Chilled Reefer Box (+2°C to +8°C)',
                'name_ar'       => 'مبردة تبريد خفيف (ألبان، خضار، فواكه)',
                'max_payload'   => 20.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 4,
                'name_en'       => 'Deep Freeze Reefer (-18°C to -25°C)',
                'name_ar'       => 'مبردة تجميد عميق (لحوم، أسماك، مثلجات)',
                'max_payload'   => 22.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 4,
                'name_en'       => 'Multi-Temperature Dual-Zone Reefer',
                'name_ar'       => 'مبردة ثنائية المناطق لتبريد وتجميد معاً',
                'max_payload'   => 20.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 4,
                'name_en'       => 'Pharma Certified Reefer',
                'name_ar'       => 'مبردة معتمدة ومراقبة حرارياً للأدوية',
                'max_payload'   => 18.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 4,
                'name_en'       => 'Thermal Insulated Box (No Cooler)',
                'name_ar'       => 'صندوق معزول حرارياً بدون تبريد ميكانيكي',
                'max_payload'   => 15.00,
                'is_active'     => 1,
            ],

            // 5. Closed (مغلق)
            [
                'truck_type_id' => 5,
                'name_en'       => 'Heavy Closed Box Truck (Dry Van)',
                'name_ar'       => 'شاحنة صندوق مقفل للبضائع الجافة والأجهزة',
                'max_payload'   => 15.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 5,
                'name_en'       => 'Curtainside Closed Box',
                'name_ar'       => 'شاحنة صندوق جوانب ستارة لسهولة التحميل',
                'max_payload'   => 14.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 5,
                'name_en'       => 'Insulated Dry Cargo Box',
                'name_ar'       => 'صندوق مقفل معزول مقاوم للحرارة والرطوبة',
                'max_payload'   => 12.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 5,
                'name_en'       => 'Furniture & Moving Box',
                'name_ar'       => 'صندوق مبطن مجهز لنقل العفش والمفروشات',
                'max_payload'   => 10.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 5,
                'name_en'       => 'High Volume Jumbo Box',
                'name_ar'       => 'صندوق جامبو عالي السعة التخزينية للبضائع الخفيفة',
                'max_payload'   => 15.00,
                'is_active'     => 1,
            ],

            // 6. Truck (شاحنة)
            [
                'truck_type_id' => 6,
                'name_en'       => 'Medium Cargo Truck (7-8 Ton)',
                'name_ar'       => 'شاحنة بضائع متوسطة (7-8 طن)',
                'max_payload'   => 8.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 6,
                'name_en'       => 'Dina Cargo Truck (4-5 Ton)',
                'name_ar'       => 'دينا نقل بضائع خفيفة ومتوسطة (4-5 طن)',
                'max_payload'   => 4.50,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 6,
                'name_en'       => 'Half Lorry (2.5-3.5 Ton)',
                'name_ar'       => 'هاف لوري نقل بضائع ونقل داخلي (2.5-3.5 طن)',
                'max_payload'   => 3.50,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 6,
                'name_en'       => 'Pickup Truck (1-1.5 Ton)',
                'name_ar'       => 'ونيت بيك آب نقل خفيف (1.5 طن)',
                'max_payload'   => 1.50,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 6,
                'name_en'       => 'Hydraulic Crane / Hiab Truck',
                'name_ar'       => 'شاحنة ونش كرين هيدروليكي ذاتية الرفع',
                'max_payload'   => 6.00,
                'is_active'     => 1,
            ],
            [
                'truck_type_id' => 6,
                'name_en'       => 'Small Dump / Tipper Truck',
                'name_ar'       => 'شاحنة قلاب صغيرة لمواد البناء والأتربة',
                'max_payload'   => 7.00,
                'is_active'     => 1,
            ],
        ];

        foreach ($data as $item) {
            TruckSubType::updateOrCreate(
                [
                    'truck_type_id' => $item['truck_type_id'],
                    'name_en'       => $item['name_en'],
                ],
                $item
            );
        }
    }
}
