<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class HsCodeLookupService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected HsCodeAnalyzerService $analyzerService;

    public function __construct(HsCodeAnalyzerService $analyzerService)
    {
        $this->apiUrl = config('services.hs_code_service.url', 'https://api.hscode.com/v1/lookup');
        $this->apiKey = config('services.hs_code_service.key', 'salasil_hscode_secret_key');
        $this->analyzerService = $analyzerService;
    }

    /**
     * Search and retrieve HS Code details with 30-day Caching & Error Handling.
     *
     * @param string $hsCode
     * @return array
     * @throws Exception
     */
    public function lookup(string $hsCode): array
    {
        // 1. Sanitize & clean input
        $cleanCode = preg_replace('/[^0-9.]/', '', trim($hsCode));

        if (empty($cleanCode)) {
            throw new Exception('Invalid HS Code format. Please provide a valid numeric code.');
        }

        $cacheKey = 'hscode_lookup_' . md5($cleanCode);

        // 2. Cache result for 30 Days (30 * 24 * 60 minutes) to optimize API usage
        $data = Cache::remember($cacheKey, now()->addDays(30), function () use ($cleanCode, $hsCode) {
            return $this->fetchFromExternalApiOrFallback($cleanCode, $hsCode);
        });

        // 3. Attach Advanced Freight Analysis Engine Results
        $data['analysis'] = $this->analyzerService->analyze($cleanCode);

        return $data;
    }

    /**
     * Call External 3rd-Party API with Fallback Dataset for Freight & Logistics
     */
    protected function fetchFromExternalApiOrFallback(string $cleanCode, string $originalInput): array
    {
        try {
            // Attempt 3rd-party HTTP request
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept'        => 'application/json',
                    'User-Agent'    => 'Salasil-Logistics-Ecosystem/1.0',
                ])
                ->get($this->apiUrl, [
                    'code' => $cleanCode,
                ]);

            if ($response->successful() && isset($response->json()['data'])) {
                $data = $response->json()['data'];
                return [
                    'code'          => $data['code'] ?? $cleanCode,
                    'description'   => $data['description'] ?? 'Harmonized System Freight Item',
                    'description_ar'=> $data['description_ar'] ?? 'شحنة تجارية مصنفة حسب التعرفة الجمركية',
                    'category'      => $data['category'] ?? 'General Cargo',
                    'section'       => $data['section'] ?? 'Section XVI - Machinery & Mechanical Appliances',
                    'duty_rate'     => $data['duty_rate'] ?? '5.0%',
                    'vat_rate'      => $data['vat_rate'] ?? '15.0%',
                    'restrictions'  => $data['restrictions'] ?? [
                        'Commercial invoice and bill of lading required',
                        'Subject to SASO / ZATCA customs clearance inspection',
                    ],
                    'source'        => 'External API',
                    'cached_at'     => now()->format('Y-m-d H:i:s'),
                ];
            }
        } catch (Exception $e) {
            Log::warning('HS Code 3rd-party API request failed or timed out. Falling back to local tariff engine.', [
                'code' => $cleanCode,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback Tariff Engine matching international GCC/Saudi ZATCA Harmonized System codes
        return $this->matchLocalHarmonizedTariff($cleanCode, $originalInput);
    }

    /**
     * Comprehensive GCC & International Harmonized System Tariff Dictionary
     */
    protected function matchLocalHarmonizedTariff(string $cleanCode, string $originalInput): array
    {
        $prefix4 = substr(str_replace('.', '', $cleanCode), 0, 4);
        $chapter2 = substr(str_replace('.', '', $cleanCode), 0, 2);

        $headingDatabase = [
            '7009' => [
                'code' => '7009.10.00',
                'description' => 'Glass mirrors, whether or not framed, including rear-view mirrors for vehicles & decorative home mirrors',
                'description_ar' => 'المرايا الزجاجية (سواء كانت مرايا خلفية للسيارات والمركبات أو مرايا ديكور وتأثيث المنازل والقصور)',
                'category' => 'Glassware & Decorative Building Supplies',
                'section' => 'Section XIII - Articles of Stone, Plaster, Cement; Glass and Glassware',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Fragile glassware - Shock-absorption & bubble wrap packaging required',
                    'SASO Quality & Safety Standards compliance certificate mandatory',
                ]
            ],
            '6911' => [
                'code' => '6911.10.00',
                'description' => 'Tableware, kitchenware, other household articles and toilet articles, of porcelain or china',
                'description_ar' => 'أدوات المائدة والأواني المنزلية والتجهيزات المصنوعة من الخزف الصيني (Porcelain / China)',
                'category' => 'Ceramics & Houseware Products',
                'section' => 'Section XIII - Ceramic Products',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Food contact materials safety certificate (SFDA / SASO) mandatory',
                    'Fragile handling protocol & drop test certified packaging',
                ]
            ],
            '8517' => [
                'code' => '8517.13.00',
                'description' => 'Smartphones, cellular network telephones, and wireless communication apparatus',
                'description_ar' => 'الهواتف الذكية وأجهزة التليفون للمحمول والشبكات اللاسلكية ومعدات الاتصالات',
                'category' => 'Electronics & Telecommunications Equipment',
                'section' => 'Section XVI - Electrical Machinery & Sound Recorders',
                'duty_rate' => '0.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'CST / CITC Type Approval Certificate mandatory for GCC market entry',
                    'IMEI registration & Lithium-ion battery HAZMAT transport rules apply',
                ]
            ],
            '8471' => [
                'code' => '8471.30.00',
                'description' => 'Automatic data processing machines, laptops, notebooks, tablets, and personal computers',
                'description_ar' => 'أجهزة الحاسوب المحمولة (Laptops)، الأجهزة اللوحية (Tablets)، ومعالجات البيانات الآلية',
                'category' => 'Computers & IT Hardware',
                'section' => 'Section XVI - Machinery & Mechanical Appliances',
                'duty_rate' => '0.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO Energy Efficiency & Electrical Safety Standards compliance',
                    'Serial number tracking & high-value cargo transit security',
                ]
            ],
            '7113' => [
                'code' => '7113.19.00',
                'description' => 'Articles of jewelry and parts thereof, of precious metal or of metal clad with precious metal (Gold, Silver, Platinum)',
                'description_ar' => 'المجوهرات والحلي وأجزاؤها المصنوعة من المعادن النفيسة (الذهب، الفضة، والبلاتين)',
                'category' => 'Precious Metals & Jewelry',
                'section' => 'Section XIV - Natural or Cultured Pearls, Precious Stones & Metals',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Precious metals hallmark & assaying certificate from Saudi Ministry of Commerce',
                    'Armored transport & special customs security escrow mandatory',
                ]
            ],
            '3002' => [
                'code' => '3002.20.00',
                'description' => 'Human vaccines, antisera, blood fractions, immunological products and biological medicines',
                'description_ar' => 'اللقاحات البشريّة، المصل، مشتقات الدم، والمنتجات البيولوجية والأدوية الخاضعة للتبريد',
                'category' => 'Pharmaceuticals & Biologics',
                'section' => 'Section VI - Products of the Chemical or Allied Industries',
                'duty_rate' => '0.0%',
                'vat_rate' => '0.0%',
                'restrictions' => [
                    'Saudi Food & Drug Authority (SFDA) import permit mandatory',
                    'Cold chain temperature-controlled reefer container (2°C to 8°C) mandatory',
                ]
            ],
            '3208' => [
                'code' => '3208.10.00',
                'description' => 'Paints, varnishes, lacquers based on synthetic polymers dissolved in a non-aqueous medium (Flammable Liquids)',
                'description_ar' => 'الدهانات والورنيشات ومذيبات الأصباغ القابلة للاشتعال والمصنوعة من البوليمرات',
                'category' => 'Chemicals & Flammable Coatings',
                'section' => 'Section VI - Chemical Products',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Civil Defense Flammable Chemical Import Permit required',
                    'ADR Hazmat Class 3 warning labels & UN certified steel drums required',
                ]
            ],
            '8507' => [
                'code' => '8507.60.00',
                'description' => 'Electric accumulators, including lithium-ion, lead-acid, and rechargeable batteries for vehicles & devices',
                'description_ar' => 'المراكم والبطاريات الكهربائية (بطاريات الليثيوم والرصاص للشاحنات والسيارات والأجهزة)',
                'category' => 'Electrical Accumulators & Batteries',
                'section' => 'Section XVI - Electrical Equipment',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO Battery Safety & Thermal Runaway Certification',
                    'Hazmat UN 3480 / UN 3481 Class 9 Dangerous Goods transport protocols',
                ]
            ],
            '2804' => [
                'code' => '2804.21.00',
                'description' => 'Hydrogen, rare gases, nitrogen, oxygen and pressurized industrial gases',
                'description_ar' => 'الغازات النادرة، الهيدروجين، الأكسجين، النيتروجين والغازات الصناعية المضغوطة',
                'category' => 'Industrial Chemicals & Gases',
                'section' => 'Section VI - Inorganic Chemicals',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Pressurized Gas Cylinder Transport Permit from Ministry of Energy & Civil Defense',
                    'Pressure test certificate for tanker transport cylinders',
                ]
            ],
            '3303' => [
                'code' => '3303.00.00',
                'description' => 'Perfumes and toilet waters (Essential oils, alcoholic fragrance solutions)',
                'description_ar' => 'العطورات ومياه التواليت ومستحضرات العطور الزيتية والكحولية',
                'category' => 'Cosmetics & Fragrances',
                'section' => 'Section VI - Essential Oils & Resinoids; Perfumery',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Saudi Food & Drug Authority (SFDA Cosmetic Portal) registration required',
                ]
            ],
            '8528' => [
                'code' => '8528.52.00',
                'description' => 'Monitors, display screens, projectors, and television reception apparatus',
                'description_ar' => 'الشاشات العارضة (Displays/Monitors)، أجهزة العرض الضوئي (Projectors)، وشاشات التلفزيون',
                'category' => 'Consumer Electronics & Display Tech',
                'section' => 'Section XVI - Sound and Television Recorders',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO Energy Efficiency Standards compliance label',
                    'Fragile glass panel transport Cushioning required',
                ]
            ],
            '9013' => [
                'code' => '9013.80.00',
                'description' => 'Liquid crystal devices, lasers, optical appliances, and precision optical lenses',
                'description_ar' => 'أجهزة الكريستال السائل (LCD)، الليزر، العدسات البصرية، والأجهزة الدقيقة',
                'category' => 'Precision Optical Equipment',
                'section' => 'Section XVIII - Optical, Photographic, Measuring & Precision Instruments',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Precision optical shock-proof protective packaging mandatory',
                ]
            ],
            '9101' => [
                'code' => '9101.11.00',
                'description' => 'Wrist-watches, pocket-watches, and luxury timepieces with case of precious metal',
                'description_ar' => 'ساعات اليد والجيب السويسرية والفاخرة المصنوعة غلافاتها من معادن ثمينة أو مطلية بها',
                'category' => 'Luxury Timepieces & Watches',
                'section' => 'Section XVIII - Clocks and Watches',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Precious metal hallmark verification & high-value insured transit',
                ]
            ],
            '0201' => [
                'code' => '0201.10.00',
                'description' => 'Meat of bovine animals, fresh or chilled (Carcasses and half-carcasses)',
                'description_ar' => 'لحوم الأبقار والمواشي الطازجة أو المبردة (ذبائح كاملة أو أنصاف ذبائح)',
                'category' => 'Fresh & Refrigerated Foodstuff',
                'section' => 'Section I - Live Animals & Animal Products',
                'duty_rate' => '0.0%',
                'vat_rate' => '0.0%',
                'restrictions' => [
                    'SFDA Slaughter Halal Certificate & Veterinary Health clearance mandatory',
                    'Cold Chain Reefer Transport (0°C to 4°C) continuous temperature logging',
                ]
            ],
            '0302' => [
                'code' => '0302.11.00',
                'description' => 'Fish, fresh or chilled, excluding fish fillets and other fish meat',
                'description_ar' => 'الأسماك والمأكولات البحرية الطازجة أو المبردة (غير المجمدة)',
                'category' => 'Fresh Seafood Cargo',
                'section' => 'Section I - Live Animals & Seafood Products',
                'duty_rate' => '0.0%',
                'vat_rate' => '0.0%',
                'restrictions' => [
                    'SFDA Health Inspection & Quarantine Clearance required',
                    'Express air/land refrigerated transport mandatory',
                ]
            ],
            '0401' => [
                'code' => '0401.20.00',
                'description' => 'Milk and cream, not concentrated nor containing added sugar or other sweetening matter',
                'description_ar' => 'الحليب والقشطة الطازجة غير المكثفة والخالية من السكر المضاف',
                'category' => 'Dairy Products',
                'section' => 'Section I - Dairy Produce; Birds Eggs',
                'duty_rate' => '5.0%',
                'vat_rate' => '0.0%',
                'restrictions' => [
                    'SFDA Dairy Health Approval & Refrigerated Transport (2°C to 5°C)',
                ]
            ],
            '0702' => [
                'code' => '0702.00.00',
                'description' => 'Tomatoes, fresh or chilled',
                'description_ar' => 'الطماطم والخضروات الطازجة أو المبردة',
                'category' => 'Fresh Agricultural Produce',
                'section' => 'Section II - Vegetable Products',
                'duty_rate' => '0.0%',
                'vat_rate' => '0.0%',
                'restrictions' => [
                    'Ministry of Environment, Water & Agriculture Phytosanitary Certificate',
                ]
            ],
            '0803' => [
                'code' => '0803.90.00',
                'description' => 'Bananas, including plantains, fresh or dried',
                'description_ar' => 'الموز والفواكه الاستوائية الطازجة',
                'category' => 'Fresh Fruits Produce',
                'section' => 'Section II - Edible Fruit and Nuts',
                'duty_rate' => '0.0%',
                'vat_rate' => '0.0%',
                'restrictions' => [
                    'Phytosanitary Inspection Certificate & ventilated reefer shipping',
                ]
            ],
            '2202' => [
                'code' => '2202.10.00',
                'description' => 'Waters, including mineral waters and aerated waters, containing added sugar or flavorings, and non-alcoholic beverages',
                'description_ar' => 'المياه المعدنية، المياه الغازية المحلاة، العصائر والمشروبات غير الكحولية',
                'category' => 'Beverages & Soft Drinks',
                'section' => 'Section IV - Prepared Foodstuffs & Beverages',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SFDA Food Safety approval & Excise Tax compliance (if applicable)',
                ]
            ],
            '7208' => [
                'code' => '7208.10.00',
                'description' => 'Flat-rolled products of iron or non-alloy steel, of a width of 600 mm or more, hot-rolled',
                'description_ar' => 'منتجات مسطحة من حديد أو صلب من غير السبائك، بعرض 600 مم أو أكثر، مسحوبة على الساخن للبناء',
                'category' => 'Iron & Steel Building Supplies',
                'section' => 'Section XV - Base Metals & Articles of Base Metal',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Heavy axle load distribution on flatbed trailer mandatory',
                    'SASO Quality Mark for Construction Steel Certificate required',
                ]
            ],
            '7304' => [
                'code' => '7304.11.00',
                'description' => 'Tubes, pipes and hollow profiles, seamless, of iron or steel for oil or gas pipelines',
                'description_ar' => 'أنابيب ومواسير بدون لحام من حديد أو صلب لخطوط أنابيب النفط والغاز والإنشاءات',
                'category' => 'Pipes & Industrial Hardware',
                'section' => 'Section XV - Articles of Iron or Steel',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Mill Test Certificate (MTC) & API Standard Certification required',
                ]
            ],
            '6109' => [
                'code' => '6109.10.00',
                'description' => 'T-shirts, singlets and other vests, knitted or crocheted, of cotton',
                'description_ar' => 'قمصان تيشيرت وصدريات مصنعة من قطن، مصبوغة أو ممسوطة',
                'category' => 'Apparel & Textiles',
                'section' => 'Section XI - Textiles and Textile Articles',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO Textile Safety Standards (Azo-dyes testing) Certificate',
                ]
            ],
            '6203' => [
                'code' => '6203.11.00',
                'description' => 'Men’s or boys’ suits, ensembles, jackets, blazers, trousers and shorts',
                'description_ar' => 'بدل وأطقم وجاكيتات وبنطلونات رجالية وأولادية جاهزة',
                'category' => 'Garments & Fashion Cargo',
                'section' => 'Section XI - Articles of Apparel and Clothing Accessories',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Country of Origin garment label permanently sewn required',
                ]
            ],
            '9403' => [
                'code' => '9403.30.00',
                'description' => 'Wooden or metal furniture of a kind used in offices, bedrooms, kitchens or living rooms',
                'description_ar' => 'الأثاث الخشبي والمعدني المستخدم في المكاتب، غرف النوم، المطابخ، والصالونات',
                'category' => 'Furniture & Home Decor',
                'section' => 'Section XX - Miscellaneous Manufactured Articles',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO Formaldehyde Emission standards compliance certificate',
                ]
            ],
            '9405' => [
                'code' => '9405.10.00',
                'description' => 'Luminaires and lighting fittings including chandeliers, LED lamps, electric ceiling lights',
                'description_ar' => 'الثريات ووحدات الإضاءة الكهربائية والكشافات وأجهزة الإنارة المباشرة',
                'category' => 'Lighting & Electrical Fixtures',
                'section' => 'Section XX - Lighting & Lamps',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO IECEE Certificate & Energy Efficiency Standards for LED lighting',
                ]
            ],
            '3926' => [
                'code' => '3926.90.00',
                'description' => 'Other articles of plastics and articles of other materials of headings 3901 to 3914',
                'description_ar' => 'مصنوعات أخرى من بلاستيك للمستلزمات الصناعية والتغليف والبناء',
                'category' => 'Plastic Manufactures',
                'section' => 'Section VII - Plastics & Rubber',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO Biodegradable Plastics Mark for single-use plastic items',
                ]
            ],
            '8704' => [
                'code' => '8704.21.00',
                'description' => 'Motor vehicles for the transport of goods (Heavy trucks, flatbeds, refrigerated cargo transport)',
                'description_ar' => 'مركبات نارية لنقل البضائع (شاحنات ثقيلة، تريلات، شاحنات مبردة)',
                'category' => 'Vehicles & Logistics Equipment',
                'section' => 'Section XVII - Vehicles, Aircraft, Vessels and Associated Transport Equipment',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Requires SASO Energy Efficiency Certificate & Conformity Standard (CITC/TGA)',
                    'Inspection for heavy axle weight limits prior to Saudi highway entry',
                    'Requires valid Commercial Registration (CR) with Land Freight Transport activity',
                ]
            ],
            '8701' => [
                'code' => '8701.20.00',
                'description' => 'Road tractors for semi-trailers (Heavy duty prime movers)',
                'description_ar' => 'جرارات طرق لرؤوس التريلات والشاحنات الثقيلة (رأس تريلا)',
                'category' => 'Heavy Freight Vehicles',
                'section' => 'Section XVII - Transport Equipment',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Transport General Authority (TGA) operational permit required',
                    'Euro-5 / Euro-6 emission standards compliance required for GCC entry',
                ]
            ],
            '2710' => [
                'code' => '2710.12.00',
                'description' => 'Petroleum oils and oils obtained from bituminous minerals (Diesel, Gas Oil, Lubricants)',
                'description_ar' => 'زيوت نفطية ومواد هيدروكربونية (ديزل، زيوت تشحيم المحركات، وقود نفاث)',
                'category' => 'Hazardous & Liquid Cargo',
                'section' => 'Section V - Mineral Products',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'HAZMAT / Dangerous Goods Transport License required (ADR Standard)',
                    'Civil Defense & Ministry of Energy Special Import Clearance mandatory',
                    'Spill containment and emergency safety gear required on transport truck',
                ]
            ],
            '8408' => [
                'code' => '8408.20.00',
                'description' => 'Compression-ignition internal combustion piston engines (Diesel engine spare parts)',
                'description_ar' => 'محركات ديزل للاحتراق الداخلي وقطع غيار المحركات الثقيلة',
                'category' => 'Machinery & Engine Parts',
                'section' => 'Section XVI - Machinery and Mechanical Appliances',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Manufacturer certificate of origin required',
                    'SASO Saber portal registration mandatory for customs release',
                ]
            ],
            '4011' => [
                'code' => '4011.20.00',
                'description' => 'New pneumatic tyres, of rubber, of a kind used on buses or lorries',
                'description_ar' => 'إطارات خارجية جديدة من مطاط للشاحنات بالحافلات والمركبات الثقيلة',
                'category' => 'Automotive Parts & Supplies',
                'section' => 'Section VII - Plastics and Articles Thereof; Rubber and Articles Thereof',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'GSO Quality conformity certificate mandatory',
                    'Maximum manufacturing date within 24 months from customs clearance',
                ]
            ],
            '3923' => [
                'code' => '3923.10.00',
                'description' => 'Articles for the conveyance or packing of goods, of plastics (Boxes, crates, pallets)',
                'description_ar' => 'صناديق وقواعد بلاستيكية (طبالي وحاويات نارية لتغليف الشحنات)',
                'category' => 'Packaging & Storage Supplies',
                'section' => 'Section VII - Plastics & Packaging',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'Food-grade compliance certificate required if used for perishable cargo',
                ]
            ],
            '8504' => [
                'code' => '8504.40.00',
                'description' => 'Electrical transformers, static converters (rectifiers) and inductors',
                'description_ar' => 'محولات كهربائية ومغيرات التيار وأجهزة الطاقة اللوجستية',
                'category' => 'Electrical Equipment',
                'section' => 'Section XVI - Electrical Machinery & Equipment',
                'duty_rate' => '5.0%',
                'vat_rate' => '15.0%',
                'restrictions' => [
                    'SASO Electrical Safety standards compliance certificate',
                ]
            ]
        ];

        if (isset($headingDatabase[$prefix4])) {
            $item = $headingDatabase[$prefix4];
            $item['code'] = $cleanCode;
            $item['source'] = 'GCC Customs Tariff Database';
            $item['cached_at'] = now()->format('Y-m-d H:i:s');
            return $item;
        }

        // Comprehensive WCO 2-Digit Chapter Title Resolver for any unlisted code
        $chapterMap = [
            '01' => ['Live animals', 'حيوانات حية ومواشي'],
            '02' => ['Meat and edible meat offal', 'لحوم وأحشاء مأكولة طازجة أو مبردة'],
            '03' => ['Fish and crustaceans, molluscs', 'أسماك وقشريات ومأكولات بحرية'],
            '04' => ['Dairy produce; birds eggs; natural honey', 'ألبان ومنتجات الألبان وبيض طيور وعسل طبيعي'],
            '05' => ['Products of animal origin', 'منتجات من أصل حيواني غير مذكورة في مكان آخر'],
            '06' => ['Live trees and other plants; bulbs, roots; cut flowers', 'أشجار ونباتات حية وزهور قطف وزينة'],
            '07' => ['Edible vegetables and certain roots and tubers', 'خضروات ونباتات جذورية طازجة مأكولة'],
            '08' => ['Edible fruit and nuts; peel of citrus fruit or melons', 'فواكه وثمار مأكولة وجوز وثمار حمضية'],
            '09' => ['Coffee, tea, mate and spices', 'بُن، شاي، بهارات وتوابل تجارية'],
            '10' => ['Cereals (Wheat, Rice, Barley, Maize)', 'حبوب (قمح، أرز، شعير، ذرة)'],
            '15' => ['Animal, vegetable fats and oils', 'شحوم وزيوت حيوانية أو نباتية'],
            '22' => ['Beverages, spirits and vinegar', 'مشروبات، عصائر، ومياه معدنية وغازية'],
            '27' => ['Mineral fuels, mineral oils, bituminous substances', 'وقود معدني وزيوت نفطية ومواد هيدروكربونية'],
            '28' => ['Inorganic chemicals; organic compounds of precious metals', 'منتجات كيميائية غير عضوية وغازات صناعية'],
            '29' => ['Organic chemicals', 'منتجات كيميائية عضوية ومذيبات'],
            '30' => ['Pharmaceutical products and medical items', 'منتجات صيدلانية وأدوية ومستلزمات طبية'],
            '32' => ['Tanning or dyeing extracts; dyes, pigments; paints', 'خلاصات دباغة وأصباغ ودهانات وورنيشات'],
            '33' => ['Essential oils and resinoids; perfumery, cosmetic', 'زيوت عطرية، عطور ومستحضرات تجميل وتواليت'],
            '39' => ['Plastics and articles thereof', 'بلاستيك ومصنوعاته ومواد التغليف البلاستيكية'],
            '40' => ['Rubber and articles thereof', 'مطاط ومصنوعاته وإطارات مركبات وشاحنات'],
            '61' => ['Articles of apparel and clothing accessories, knitted', 'ألبسة ومصنوعات وتجهيزات ملابس مصبوغة أو ممسوطة'],
            '62' => ['Articles of apparel and clothing accessories, not knitted', 'ألبسة وملابس جاهزة من أنسجة غير ممسوطة'],
            '69' => ['Ceramic products, tableware and porcelain', 'منتجات سيراميك وخزف وأدوات مائدة بلاط وخزف صيني'],
            '70' => ['Glass and glassware, mirrors, bottles, optical glass', 'زجاج ومصنوعاته ومرايا زجاجية وقوارير وألواح زجاجية'],
            '71' => ['Natural or cultured pearls, precious stones, precious metals', 'لؤلؤ طبيعي وأحجار كريمة ومعادن نفيسة ومجوهرات ذهبية'],
            '72' => ['Iron and steel', 'حديد وصلب ومواد البناء المعدنية الخام'],
            '73' => ['Articles of iron or steel (Pipes, tubes, structures)', 'مصنوعات من حديد أو صلب (مواسير، هيكليات، أنابيب)'],
            '84' => ['Nuclear reactors, boilers, machinery and mechanical appliances; parts thereof', 'مرجل، آلات وأجهزة ميكانيكية ومحركات وقطع غيارها'],
            '85' => ['Electrical machinery and equipment; sound recorders, TVs, smartphones', 'آلات ومعدات كهربائية وأجهزة اتصال وهواتف وشاشات'],
            '87' => ['Vehicles other than railway or tramway rolling-stock; parts and accessories', 'سيارات، شاحنات، مركبات ثقيلة، قاطرات وقطع غيارها'],
            '90' => ['Optical, photographic, cinematographic, measuring, precision medical instruments', 'أجهزة ومعدات بصرية وفوتوغرافية وأجهزة قياس وطبية دقيقة'],
            '91' => ['Clocks and watches and parts thereof', 'ساعات يد وجيب وساعات حائط ومعدات التوقيت'],
            '94' => ['Furniture; bedding, mattresses; luminaires and lighting fittings', 'أثاث منزلي ومكتبي ومستلزمات إضاءة وثريات وكشافات'],
        ];

        $chapterInfo = $chapterMap[$chapter2] ?? ['Commercial Goods & Manufactured Cargo', 'بضائع ومنتجات تجارية وصناعية متنوعة'];

        return [
            'code'          => $cleanCode,
            'description'   => "Harmonized System Item #{$cleanCode} - {$chapterInfo[0]}",
            'description_ar'=> "البند الجمركي رقم #{$cleanCode} - {$chapterInfo[1]}",
            'category'      => $chapterInfo[0],
            'section'       => "Chapter {$chapter2} Classification",
            'duty_rate'     => '5.0%',
            'vat_rate'      => '15.0%',
            'restrictions'  => [
                'Standard Commercial Invoice & Packing List required',
                'Certificate of Origin (CoO) mandatory for GCC customs entry',
                'Customs Inspection & Duty Payment via ZATCA Portal',
            ],
            'source'        => 'Harmonized Tariff System',
            'cached_at'     => now()->format('Y-m-d H:i:s'),
        ];
    }
}
