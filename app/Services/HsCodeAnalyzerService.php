<?php

namespace App\Services;

class HsCodeAnalyzerService
{
    /**
     * Analyze HS Code and extract logistics safety, risk, and handling characteristics.
     *
     * @param string $hsCode
     * @return array
     */
    public function analyze(string $hsCode): array
    {
        // 1. Sanitize code to numbers only
        $numericCode = preg_replace('/[^0-9]/', '', $hsCode);

        $chapter2    = substr($numericCode, 0, 2); // First 2 digits (Chapter)
        $heading4    = substr($numericCode, 0, 4); // First 4 digits (Heading)
        $subheading6 = substr($numericCode, 0, 6); // First 6 digits (Subheading)

        // 2. Product Attribute Flags Initialization
        $flags = [
            'is_hazardous'        => false,
            'is_fragile'          => false,
            'is_perishable'       => false,
            'requires_cold_chain' => false,
            'is_high_value'       => false,
            'is_liquid_or_gas'    => false,
        ];

        // --- 1. Fragile Cargo (is_fragile) ---
        // Chapters: 69 (Ceramics/Pottery), 70 (Glass & Mirrors)
        // Headings: 9405 (Chandeliers/Lamps), 8528 (Displays/TVs), 9001 (Optics/Lenses),
        //           9006 (Cameras), 9011 (Microscopes), 9013 (Optical Devices),
        //           9101, 9102 (Glass Cover Watches), 8517 (Smartphones), 8471 (Computers/Laptops)
        $fragileChapters = ['69', '70'];
        $fragileHeadings = ['9405', '8528', '9001', '9006', '9011', '9013', '9101', '9102', '8517', '8471'];
        $fragileSubheadings = ['851713', '847130'];

        if (in_array($chapter2, $fragileChapters) ||
            in_array($heading4, $fragileHeadings) ||
            in_array($subheading6, $fragileSubheadings)) {
            $flags['is_fragile'] = true;
        }

        // --- 2. Hazardous Cargo (is_hazardous) ---
        // Chapters: 28 (Inorganic chemicals), 29 (Organic chemicals), 36 (Explosives/Pyrotechnics), 38 (Chemical products)
        // Headings: 8507 (Batteries/Accumulators), 2710 (Petroleum/Fuels), 3208 (Flammable Paints),
        //           2804 (Compressed Gases), 3303 (Perfumes/Alcoholic Fragrances)
        $hazmatChapters = ['28', '29', '36', '38'];
        $hazmatHeadings = ['8507', '2710', '3208', '2804', '3303'];

        if (in_array($chapter2, $hazmatChapters) || in_array($heading4, $hazmatHeadings)) {
            $flags['is_hazardous'] = true;
        }

        // --- 3. Liquids or Gases (is_liquid_or_gas) ---
        // Chapters: 22 (Beverages/Liquids), 27 (Petroleum/Liquid Oils)
        // Headings: 2804 (Gases), 3303 (Liquid Perfumes), 3004 (Liquid Medicaments), 2710 (Petroleum Oils)
        $liquidGasChapters = ['22', '27'];
        $liquidGasHeadings = ['2804', '3303', '3004', '2710'];

        if (in_array($chapter2, $liquidGasChapters) || in_array($heading4, $liquidGasHeadings)) {
            $flags['is_liquid_or_gas'] = true;
        }

        // --- 4. Perishable Fresh Cargo & Cold Chain (is_perishable & requires_cold_chain) ---
        // Chapters: 02 (Meat), 03 (Fish), 04 (Dairy), 06 (Flowers/Plants), 07 (Vegetables), 08 (Fruits)
        $perishableChapters = ['02', '03', '04', '06', '07', '08'];
        if (in_array($chapter2, $perishableChapters)) {
            $flags['is_perishable'] = true;
        }

        // Cold Chain Chapters: 02, 03, 04 OR Medical Vaccines Heading 3002
        $coldChainChapters = ['02', '03', '04'];
        if (in_array($chapter2, $coldChainChapters) || $heading4 === '3002') {
            $flags['requires_cold_chain'] = true;
        }

        // --- 5. High-Value Cargo (is_high_value) ---
        // Chapter: 71 (Gold, Pearls, Precious Stones & Metals)
        // Headings: 8517 / 851713 (Smartphones), 8471 / 847130 (Laptops/Computers), 9101 (Precious Metal Watches)
        $highValueChapters = ['71'];
        $highValueHeadings = ['8517', '8471', '9101'];
        $highValueSubheadings = ['851713', '847130'];

        if (in_array($chapter2, $highValueChapters) ||
            in_array($heading4, $highValueHeadings) ||
            in_array($subheading6, $highValueSubheadings)) {
            $flags['is_high_value'] = true;
        }

        // --- 6. Risk Level Assessment Logic ---
        // HIGH: If is_hazardous = true OR is_high_value = true
        // MEDIUM: If (is_fragile = true OR requires_cold_chain = true) AND NOT HIGH
        // LOW: Otherwise
        $riskLevel = 'LOW';
        $riskBadgeClass = 'bg-success text-success';
        $riskLabelAr = 'منخفض الخطر (بضائع تجارية قياسية)';
        $riskLabelEn = 'Low Risk (Standard Cargo)';

        if ($flags['is_hazardous'] || $flags['is_high_value']) {
            $riskLevel = 'HIGH';
            $riskBadgeClass = 'bg-danger text-danger';
            $riskLabelAr = 'مخاطر عالية (تتطلب تصاريح ومرافقة أمنية)';
            $riskLabelEn = 'High Risk (Requires Special Permits & Escort)';
        } elseif ($flags['is_fragile'] || $flags['requires_cold_chain'] || $flags['is_perishable'] || $flags['is_liquid_or_gas']) {
            $riskLevel = 'MEDIUM';
            $riskBadgeClass = 'bg-warning text-warning';
            $riskLabelAr = 'مخاطر متوسطة (تتطلب مناولة ورعاية مخصصة)';
            $riskLabelEn = 'Medium Risk (Specialized Handling & Transport)';
        }

        // --- 7. Dynamic Handling & Packaging Instructions ---
        $instructions = [];

        if ($flags['is_hazardous']) {
            $instructions[] = [
                'en' => 'Apply Hazmat Warning Label (UN Dangerous Goods Standard Required)',
                'ar' => 'وضع ملصقات التحذير للمواد الخطرة وتصاريح النقل والمناولة المعتمدة (UN Standard)'
            ];
            $instructions[] = [
                'en' => 'UN Certified Protective Packaging & Spill Kit Mandatory on Transport Vehicle',
                'ar' => 'استخدام حاويات وتغليف واقٍ معتمد دولياً وتوفير أدوات التعامل مع التسرب بالحافلة'
            ];
        }

        if ($flags['is_fragile']) {
            $instructions[] = [
                'en' => 'Use Heavy Duty Bubble Wrap & Shock Absorption Cushioning',
                'ar' => 'استخدام تغليف الفقاعات المقوى ووسائد امتصاص صدمات واهتزازات الطريق'
            ];
            $instructions[] = [
                'en' => 'Fragile Cargo Labeling - Handle With Extreme Care (This Side Up)',
                'ar' => 'وضع ملصق (قابل للكسر - مناولة بحذر شديد وإبقاء التغليف للأعلى)'
            ];
        }

        if ($flags['requires_cold_chain']) {
            $instructions[] = [
                'en' => 'Temperature-Controlled Refrigerated Transport (Reefer Truck 2°C to 8°C / Frozen -18°C)',
                'ar' => 'شحن عبر شاحنة مبردة مضبوطة الحرارة (2° إلى 8° مئوية أو تجميد -18° مئوية)'
            ];
            $instructions[] = [
                'en' => 'Continuous IoT Temperature & Humidity Logger Active Tracking Mandatory',
                'ar' => 'تفعيل تتبع مستمر لمسجل درجات الحرارة والرطوبة الرقمي أثناء رحلة الشحن'
            ];
        }

        if ($flags['is_perishable'] && !$flags['requires_cold_chain']) {
            $instructions[] = [
                'en' => 'Priority Rapid Transit & Express Customs Clearance Required',
                'ar' => 'شحن سريع ذو أولوية وتخليص جمركي فوري للمواد القابلة للتلف'
            ];
        }

        if ($flags['is_high_value']) {
            $instructions[] = [
                'en' => 'Secure Sealed Transit & Armored / Escorted Transport Recommended',
                'ar' => 'تأمين أقفال الأمان وتوصية بالنقل المؤمن أو الحراسة للبضائع الثمينة'
            ];
            $instructions[] = [
                'en' => 'High-Value Cargo Insurance Coverage & GPS Smart Lock Seal Mandatory',
                'ar' => 'التأمين الشامل على البضائع عالية القيمة وتفعيل القفل الذكي المزود بـ GPS'
            ];
        }

        if ($flags['is_liquid_or_gas']) {
            $instructions[] = [
                'en' => 'Pressurized Tanker & Leak-Proof Container Inspection Required',
                'ar' => 'فحص سلامة الصهاريج والحاويات المقاومة للتسرب والضغط قبل التعبئة والتحميل'
            ];
        }

        if (empty($instructions)) {
            $instructions[] = [
                'en' => 'Standard Commercial Cargo Packaging & Palletization Guidelines Apply',
                'ar' => 'تطبيق معايير التغليف والشحن التجاري القياسي وتثبيت البضاعة على طبالي'
            ];
        }

        return [
            'flags'                 => $flags,
            'risk_level'            => $riskLevel,
            'risk_badge_class'      => $riskBadgeClass,
            'risk_label_en'         => $riskLabelEn,
            'risk_label_ar'         => $riskLabelAr,
            'handling_instructions' => $instructions,
        ];
    }
}
