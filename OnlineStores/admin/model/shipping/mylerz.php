

<?php
class ModelShippingMylerz extends Model {

  	/**
  	 * @const strings API URLs.
  	 */
    const BASE_API_TESTING_URL = 'http://41.33.122.61:58639/';
    const BASE_API_LIVE_URL    = 'https://integration.mylerz.net/';

    public function install(){
        $cities = [
          0 => ['Code' => 'CA', 'ArName' => 'القاهرة', 'EnName' => 'Cairo'],
          1 => ['Code' => 'Giza', 'ArName' => 'الجيزة', 'EnName' => 'Giza'],
          2 => ['Code' => 'ALX', 'ArName' => 'الاسكندرية', 'EnName' => 'Alexandria'],
          3 => ['Code' => 'ASYT', 'ArName' => 'أسيوط', 'EnName' => 'Asyut'],
          4 => ['Code' => 'ASWN', 'ArName' => 'أسوان', 'EnName' => 'Aswan'],
          5 => ['Code' => 'BEHR', 'ArName' => 'البحيرة', 'EnName' => 'Beheira'],
          6 => ['Code' => 'BENS', 'ArName' => 'بنى سويف', 'EnName' => 'Beni Suef'],
          7 => ['Code' => 'DAKH', 'ArName' => 'الدقهلية', 'EnName' => 'Dakahlia'],
          8 => ['Code' => 'DAMT', 'ArName' => 'دمياط', 'EnName' => 'Damietta'],
          9 => ['Code' => 'FAYM', 'ArName' => 'الفيوم', 'EnName' => 'Faiyum'],
          10 => ['Code' => 'GHRB', 'ArName' => 'الغربية', 'EnName' => 'Gharbia'],
          11 => ['Code' => 'ISML', 'ArName' => 'الإسماعيلية', 'EnName' => 'Ismailia'],
          12 => ['Code' => 'SHKH', 'ArName' => 'كفر الشيخ', 'EnName' => 'Kafr El Sheikh'],
          13 => ['Code' => 'LUXR', 'ArName' => 'الأقصر', 'EnName' => 'Luxor'],
          14 => ['Code' => 'MTRH', 'ArName' => 'مرسى مطروح', 'EnName' => 'Matruh'],
          15 => ['Code' => 'MNYA', 'ArName' => 'المنيا', 'EnName' => 'Minya'],
          16 => ['Code' => 'MONF', 'ArName' => 'المنوفية', 'EnName' => 'Monufia'],
          17 => ['Code' => 'WADI', 'ArName' => 'الوادى الجديد', 'EnName' => 'El Wadi el Gedid'],
          18 => ['Code' => 'NSNA', 'ArName' => 'شمال سيناء', 'EnName' => 'North Sinai'],
          19 => ['Code' => 'PORS', 'ArName' => 'بورسعيد', 'EnName' => 'Port Said'],
          20 => ['Code' => 'QLYB', 'ArName' => 'القليوبية', 'EnName' => 'Qalyubia'],
          21 => ['Code' => 'QENA', 'ArName' => 'قنا', 'EnName' => 'Qena'],
          22 => ['Code' => 'REDS', 'ArName' => 'البحر الأحمر', 'EnName' => 'El Bahr El Ahmar'],
          23 => ['Code' => 'SHRK', 'ArName' => 'الشرقية', 'EnName' => 'Sharqia'],
          24 => ['Code' => 'SOHG', 'ArName' => 'سوهاج', 'EnName' => 'Sohag'],
          25 => ['Code' => 'SSINA', 'ArName' => 'جنوب سيناء', 'EnName' => 'South Sinai'],
          26 => ['Code' => 'SUEZ', 'ArName' => 'السويس', 'EnName' => 'Suez'],
        ];

        $neighborhoods = [
              0 => ['code' => 'AS', 'EnName' => 'Ain Shams', 'ArName' => 'عين شمس', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              1 => ['code' => 'Al-S', 'EnName' => 'Al Salam', 'ArName' => 'السلام', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              2 => ['code' => 'El-M', 'EnName' => 'El Marg', 'ArName' => 'المرج', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              3 => ['code' => 'HEl', 'EnName' => 'Heliopolis', 'ArName' => 'هيليوبلس', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              4 => ['code' => 'Nozha', 'EnName' => 'Nozha', 'ArName' => 'النزهه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              5 => ['code' => 'Nasr City', 'EnName' => 'Nasr City', 'ArName' => 'مدينة نصر', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              6 => ['code' => 'Zamalek', 'EnName' => 'Zamalek', 'ArName' => 'الزمالك', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              7 => ['code' => 'El-Azbakia', 'EnName' => 'El Azbakia', 'ArName' => 'الازبكيه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              8 => ['code' => 'El-Mosky', 'EnName' => 'El Mosky', 'ArName' => 'الموسكي', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              9 => ['code' => 'El-Waily', 'EnName' => 'El Waily', 'ArName' => 'الوايلي', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              10 => ['code' => 'Abdeen', 'EnName' => 'Abdeen', 'ArName' => 'عابدين', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              11 => ['code' => 'Bab El-Shaeria', 'EnName' => 'Bab El Shaeria', 'ArName' => 'باب الشعريه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              12 => ['code' => 'El-Gamalia', 'EnName' => 'El Gamalia', 'ArName' => 'الجماليه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              13 => ['code' => 'Boulak', 'EnName' => 'Boulak', 'ArName' => 'بولاق', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              14 => ['code' => 'Qasr elneil', 'EnName' => 'Qasr elneil', 'ArName' => 'قصر النيل', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              15 => ['code' => 'ELdarb Elahmar', 'EnName' => 'ELdarb Elahmar', 'ArName' => 'الدرب الاحمر', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              16 => ['code' => 'Badr City', 'EnName' => 'Badr City', 'ArName' => 'مدينة بدر', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              17 => ['code' => 'Basateen', 'EnName' => 'Basateen', 'ArName' => 'البساتين', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              18 => ['code' => 'Elsayeda Zeinab', 'EnName' => 'Elsayeda Zeinab', 'ArName' => 'السيده زينب', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              19 => ['code' => 'Hadayek El Qobah', 'EnName' => 'Hadayek El Qobah', 'ArName' => 'حدايق القبه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              20 => ['code' => '15th of May', 'EnName' => '15th of May', 'ArName' => '15 مايو', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              21 => ['code' => 'Helwan', 'EnName' => 'Helwan', 'ArName' => 'حلوان', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              22 => ['code' => 'Eltebeen', 'EnName' => 'Eltebeen', 'ArName' => 'التبيين', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              23 => ['code' => 'Maadi', 'EnName' => 'Maadi', 'ArName' => 'المعادي', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              24 => ['code' => 'Madinaty', 'EnName' => 'Madinaty', 'ArName' => 'مدينتي', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              25 => ['code' => 'Manial', 'EnName' => 'Manial', 'ArName' => 'المنيل', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              26 => ['code' => 'Masr El Qadeema', 'EnName' => 'Masr El Qadeema', 'ArName' => 'مصر القديمه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              27 => ['code' => 'New Cairo', 'EnName' => 'New Cairo', 'ArName' => 'القاهره الجديده', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              28 => ['code' => 'El Shorouk', 'EnName' => 'El Shorouk', 'ArName' => 'الشروق', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              29 => ['code' => 'Shoubra', 'EnName' => 'Shoubra', 'ArName' => 'شبرا', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              30 => ['code' => 'Zeitoun', 'EnName' => 'Zeitoun', 'ArName' => 'الزيتون', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              31 => ['code' => 'AMRY', 'EnName' => 'Al Amiriyyah', 'ArName' => 'الأميرية', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              32 => ['code' => 'ABAG', 'EnName' => 'Al Abageyah', 'ArName' => 'الأباجية', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              33 => ['code' => 'TURA', 'EnName' => 'Tura', 'ArName' => 'طره', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              34 => ['code' => 'HWMD', 'EnName' => 'El Hawamdeyya', 'ArName' => 'الحوامدية', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              35 => ['code' => 'BDRA', 'EnName' => 'Al Badrashin', 'ArName' => 'البدرشين', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              36 => ['code' => 'HDBA', 'EnName' => 'El Hadba EL wosta', 'ArName' => 'الهضبة الوسطى', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              37 => ['code' => 'AYAT', 'EnName' => 'Al Ayat', 'ArName' => 'العياط', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              38 => ['code' => 'SAF', 'EnName' => 'El Saf', 'ArName' => 'الصف', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              39 => ['code' => 'GHMR', 'EnName' => 'Ghamra', 'ArName' => 'غمره', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              40 => ['code' => 'ZAHR', 'EnName' => 'El Zaher', 'ArName' => 'الظاهر', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              41 => ['code' => 'ABAS', 'EnName' => 'Abbaseya', 'ArName' => 'العباسية', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              42 => ['code' => 'SHRB', 'EnName' => 'Al Sharabiya', 'ArName' => 'الشرابية', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              43 => ['code' => 'FRAG', 'EnName' => 'Rod El Farag', 'ArName' => 'روض الفرج', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              44 => ['code' => 'KHSU', 'EnName' => 'Al Khusus', 'ArName' => 'الخصوص', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              45 => ['code' => 'ZWYA', 'EnName' => 'El Zawya El Hamra', 'ArName' => 'الزاوية الحمرا', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              46 => ['code' => 'KHM1', 'EnName' => 'Awal Shubra Al Kheimah', 'ArName' => 'أول شبرا الخيمه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              47 => ['code' => 'KHM2', 'EnName' => 'Shubra El Kheima 2', 'ArName' => '2 شبرا الخيمه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              48 => ['code' => 'FUTR', 'EnName' => 'Future City', 'ArName' => 'مدينة المستقبل', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              49 => ['code' => 'NHEL', 'EnName' => 'New Heliopolis City', 'ArName' => 'مدينة هيليوبلس الجديدة', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              50 => ['code' => 'OBOR', 'EnName' => 'El Obour City', 'ArName' => 'مدينة العبور', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              51 => ['code' => 'RMDN', 'EnName' => '10th of Ramadan', 'ArName' => 'مدينة العاشر من رمضان', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              52 => ['code' => 'ESHA', 'EnName' => 'Elsayeda Aisha', 'ArName' => 'السيدة عائشه', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              53 => ['code' => 'NKHL', 'EnName' => 'Izbat an Nakhl', 'ArName' => 'عزبة النخل', 'city_code' => 'CA', 'city_en_name' => 'Cairo' ],
              54 => ['code' => 'LWAA', 'EnName' => 'Ard El Lewa', 'ArName' => 'أرض اللواء', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              55 => ['code' => 'BRAG', 'EnName' => 'Al Baragel', 'ArName' => 'البراجيل', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              56 => ['code' => 'MOTM', 'EnName' => 'Al Moatamadeyah', 'ArName' => 'المعتمدية', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              57 => ['code' => 'KFRH', 'EnName' => 'Kafr Hakim', 'ArName' => 'كفر حكيم', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              58 => ['code' => 'OSIM', 'EnName' => 'Ossim', 'ArName' => 'أوسيم', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              59 => ['code' => 'GIZA', 'EnName' => 'El Giza', 'ArName' => 'الجيزة', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              60 => ['code' => 'MUNB', 'EnName' => 'Al Munib', 'ArName' => 'المنيب', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              61 => ['code' => 'MEKI', 'EnName' => 'Saqiyet Mekki', 'ArName' => 'ساقية مكى', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              62 => ['code' => 'NMRS', 'EnName' => 'Abu an Numros', 'ArName' => 'أبو النمرس', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              63 => ['code' => 'MANT', 'EnName' => 'Shubra Ment', 'ArName' => 'شبرا منت', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              64 => ['code' => 'MNWT', 'EnName' => 'Al Manawat', 'ArName' => 'المناوات', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              65 => ['code' => 'SMAN', 'EnName' => 'Nazlet El Semman', 'ArName' => 'نزلة السمان', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              66 => ['code' => 'BTRN', 'EnName' => 'Nazlet Al Batran', 'ArName' => 'نزلة البطران', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              67 => ['code' => 'TLBA', 'EnName' => 'El Talbia', 'ArName' => 'الطالبية', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              68 => ['code' => 'TRMS', 'EnName' => 'Kafr Tuhurmis', 'ArName' => 'كفر طهرمس', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              69 => ['code' => 'NASR', 'EnName' => 'Kafr Nassar', 'ArName' => 'كفر نصار', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              70 => ['code' => 'RWSH', 'EnName' => 'Abou Rawash', 'ArName' => 'أبو رواش', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              71 => ['code' => 'Agouza', 'EnName' => 'Agouza', 'ArName' => 'العجوزه', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              72 => ['code' => 'Dokki', 'EnName' => 'Dokki', 'ArName' => 'الدقي', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              73 => ['code' => 'Boulak Eldakrour', 'EnName' => 'Boulak Eldakrour', 'ArName' => 'بولاق الدكرور', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              74 => ['code' => 'Hadayek Al Ahram', 'EnName' => 'Hadayek Al Ahram', 'ArName' => 'حدايق الاهرام', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              75 => ['code' => 'Haram', 'EnName' => 'Haram', 'ArName' => 'هرم', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              76 => ['code' => 'Imbaba', 'EnName' => 'Imbaba', 'ArName' => 'امبابه', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              77 => ['code' => 'Mohandseen', 'EnName' => 'Mohandseen', 'ArName' => 'مهندسين', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              78 => ['code' => 'Nahia', 'EnName' => 'Nahia', 'ArName' => 'ناهيا', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              79 => ['code' => '6th of Oct', 'EnName' => '6th of Oct', 'ArName' => 'السادس من إكتوبر', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              80 => ['code' => 'Sheikh Zayed', 'EnName' => 'Sheikh Zayed', 'ArName' => 'الشيخ زايد', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              81 => ['code' => 'Omraneya', 'EnName' => 'Omraneya', 'ArName' => 'عمرانيه', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              82 => ['code' => 'Saft El Laban', 'EnName' => 'Saft El Laban', 'ArName' => 'صفط اللبن', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              83 => ['code' => 'Warraq', 'EnName' => 'Warraq', 'ArName' => 'وراق', 'city_code' => 'Giza', 'city_en_name' => 'Giza' ],
              84 => ['code' => 'Alexandria', 'EnName' => 'Alexandria', 'ArName' => 'الاسكندريه', 'city_code' => 'ALX', 'city_en_name' => 'Alexandria' ],
              85 => ['code' => 'ASYT', 'EnName' => 'Asyut', 'ArName' => 'أسيوط', 'city_code' => 'ASYT', 'city_en_name' => 'Asyut' ],
              86 => ['code' => 'ASWN', 'EnName' => 'Aswan', 'ArName' => 'أسوان', 'city_code' => 'ASWN', 'city_en_name' => 'Aswan' ],
              87 => ['code' => 'BEHR', 'EnName' => 'Beheira', 'ArName' => 'البحيرة', 'city_code' => 'BEHR', 'city_en_name' => 'Beheira' ],
              88 => ['code' => 'BENS', 'EnName' => 'Beni Suef', 'ArName' => 'بنى سويف', 'city_code' => 'BENS', 'city_en_name' => 'Beni Suef' ],
              89 => ['code' => 'DAKH', 'EnName' => 'Dakahlia', 'ArName' => 'الدقهلية', 'city_code' => 'DAKH', 'city_en_name' => 'Dakahlia' ],
              90 => ['code' => 'DAMT', 'EnName' => 'Damietta', 'ArName' => 'دمياط', 'city_code' => 'DAMT', 'city_en_name' => 'Damietta' ],
              91 => ['code' => 'FAYM', 'EnName' => 'Faiyum', 'ArName' => 'الفيوم', 'city_code' => 'FAYM', 'city_en_name' => 'Faiyum' ],
              92 => ['code' => 'GHRB', 'EnName' => 'Gharbia', 'ArName' => 'الغربية', 'city_code' => 'GHRB', 'city_en_name' => 'Gharbia' ],
              93 => ['code' => 'ISML', 'EnName' => 'Ismailia', 'ArName' => 'الإسماعيلية', 'city_code' => 'ISML', 'city_en_name' => 'Ismailia' ],
              94 => ['code' => 'SHKH', 'EnName' => 'Kafr El Sheikh', 'ArName' => 'كفر الشيخ', 'city_code' => 'SHKH', 'city_en_name' => 'Kafr El Sheikh' ],
              95 => ['code' => 'LUXR', 'EnName' => 'Luxor', 'ArName' => 'الأقصر', 'city_code' => 'LUXR', 'city_en_name' => 'Luxor' ],
              96 => ['code' => 'MTRH', 'EnName' => 'Matruh', 'ArName' => 'مرسى مطروح', 'city_code' => 'MTRH', 'city_en_name' => 'Matruh' ],
              97 => ['code' => 'MNYA', 'EnName' => 'Minya', 'ArName' => 'المنيا', 'city_code' => 'MNYA', 'city_en_name' => 'Minya' ],
              98 => ['code' => 'MONF', 'EnName' => 'Monufia', 'ArName' => 'المنوفية', 'city_code' => 'MONF', 'city_en_name' => 'Monufia' ],
              99 => ['code' => 'WADI', 'EnName' => 'El Wadi el Gedid', 'ArName' => 'الوادى الجديد', 'city_code' => 'WADI', 'city_en_name' => 'El Wadi el Gedid' ],
              100 => ['code' => 'NSNA', 'EnName' => 'North Sinai', 'ArName' => 'شمال سيناء', 'city_code' => 'NSNA', 'city_en_name' => 'North Sinai' ],
              101 => ['code' => 'PORS', 'EnName' => 'Port Said', 'ArName' => 'بورسعيد', 'city_code' => 'PORS', 'city_en_name' => 'Port Said' ],
              102 => ['code' => 'QLYB', 'EnName' => 'Qalyubia', 'ArName' => 'القليوبية', 'city_code' => 'QLYB', 'city_en_name' => 'Qalyubia' ],
              103 => ['code' => 'QENA', 'EnName' => 'Qena', 'ArName' => 'قنا', 'city_code' => 'QENA', 'city_en_name' => 'Qena' ],
              104 => ['code' => 'REDS', 'EnName' => 'El Bahr El Ahmar', 'ArName' => 'البحر الأحمر', 'city_code' => 'REDS', 'city_en_name' => 'El Bahr El Ahmar' ],
              105 => ['code' => 'SHRK', 'EnName' => 'Sharqia', 'ArName' => 'الشرقية', 'city_code' => 'SHRK', 'city_en_name' => 'Sharqia' ],
              106 => ['code' => 'SOHG', 'EnName' => 'Sohag', 'ArName' => 'سوهاج', 'city_code' => 'SOHG', 'city_en_name' => 'Sohag' ],
              107 => ['code' => 'SSINA', 'EnName' => 'South Sinai', 'ArName' => 'جنوب سيناء', 'city_code' => 'SSINA', 'city_en_name' => 'South Sinai' ],
              108 => ['code' => 'SUEZ', 'EnName' => 'Suez', 'ArName' => 'السويس', 'city_code' => 'SUEZ', 'city_en_name' => 'Suez' ],
        ];
     
        //insert neighborhoods as Areas
        $this->load->model('localisation/country');
        $this->load->model('localisation/area');
        $this->load->model('localisation/zone');
        //Check supported Countries ["Egypt"] and required cities...
        if( $country_id = $this->model_localisation_country->isCountryExist('Egypt') ){            
            foreach ($neighborhoods as $neighborhood) {

                //Add Area Only if it is not exist..
                if( ($area = $this->model_localisation_area->isAreaExist($neighborhood['EnName'])) == FALSE ){
                    //Check if it's City/Zone Exist.. if not, add city                
                    if( ($zone_id = $this->model_localisation_zone->isZoneExist($neighborhood['city_en_name'])) == FALSE ){
                        $key = array_search($neighborhood['city_code'], array_column($cities, 'Code'));        
                        $zone_id = $this->model_localisation_zone->addZone([
                          'names' => [ '1' =>  $cities[$key]['EnName'], '2' => $cities[$key]['ArName']],
                          'code'  => $cities[$key]['Code'],
                          'country_id' => $country_id,
                          'status' => 1
                        ]);
                    }
                    //Adding new Area to zone_id
                    $this->model_localisation_area->addArea([
                        'status'     => 1,
                        'names'      => [ '1' => $neighborhood['EnName'], '2' => $neighborhood['ArName']],
                        'country_id' => $country_id, 
                        'zone_id'    => $zone_id,
                        'code'       => $neighborhood['code'],
                    ]);
                }else{
                    //if area exist, update it's code...
                     $this->model_localisation_area->editArea($area['area_id'],[
                        'status'     => $area['status'],
                        //Name and not Name(s)
                        'name'       => [ '1' => $neighborhood['EnName'], '2' => $neighborhood['ArName']],// or just 'name' => [ '1' => $area['name'] ],
                        'country_id' => $area['country_id'], 
                        'zone_id'    => $area['zone_id'],
                        'code'       => $neighborhood['code'],                        
                    ]);
                }
                
            }
        }


     		//Add Tracking column in Order table if it doesn't exist
    		$query = $this->db->query("SELECT COUNT(*) colcount
    		                          FROM INFORMATION_SCHEMA.COLUMNS
    		                          WHERE  table_name = 'order'
    		                          AND table_schema = DATABASE()
    		                          AND column_name = 'tracking'");
    		$result   = $query->row;
    		$colcount = $result['colcount'];

    		if($colcount <=0 ) {
    		  $this->db->query("ALTER TABLE `order` ADD COLUMN `tracking`  varchar(3000) NULL");
    		}

    }

  	/**
  	* [POST]Create new shipment Order.
  	*
    * @param Array   $order data to be shipped.
  	*
  	* @return Response Object contains newly created order details
  	*/
    public function createShipment($order){

      //Authenticate API
      $authentication = $this->auth();

      if( $authentication['error'] ) return $authentication;

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->_getBaseUrl() . 'api/Orders/AddOrders',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($order),
        CURLOPT_HTTPHEADER => array(
          "Authorization: " . $authentication['token_type'] . ' ' . $authentication['access_token'] ,
          "Content-Type: application/json",
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      $err      = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return ['error' => $err];
      } else {
        return [
        			'status_code' => $httpcode,
        			'result'      => json_decode($response, true)
        		];
      }

    }

    public function auth(){
      $data = "username=" . urlencode($this->config->get('mylerz_username')) ."&password=" . urlencode($this->config->get('mylerz_password')) ."&grant_type=password";

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->_getBaseUrl() . 'Token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/x-www-form-urlencoded",
          "cache-control: no-cache"
        ),
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      if ($err) {
        return ['error' => $err];
      } else {
        return json_decode($response, true);
      }
    }    

    /**
     * Get ONE Order info from DB to push.
     *
     * @return Array order info .
     */
    public function getOrdersToPush($orders_ids){
        $query = $this->db->query("
          SELECT order_id, total, store_id, currency_code, 
          firstname, lastname, telephone, geo_area.code AS shipping_area_code, zone.code AS shipping_zone_code ,
          payment_code, 
          shipping_country,
          CONCAT(shipping_address_1, shipping_address_2) AS shipping_address 

          FROM `" . DB_PREFIX . "order` 
              LEFT JOIN order_status os ON order.order_status_id = os.order_status_id
              LEFT JOIN zone ON order.shipping_zone_id = zone.zone_id
              LEFT JOIN geo_area ON order.shipping_area_id = geo_area.area_id

          WHERE `order`.`order_id` IN (" . implode(',', $orders_ids) . ") AND (tracking IS NULL OR tracking = '') AND os.language_id = " . (int)$this->config->get('config_language_id') );

        return $query->rows;
    }

    /*  Helper Methods */
    private function _getBaseUrl(){
      //Check if API is in Debugging Mode..
      $is_debugging_mode = $this->config->get('mylerz_debugging_mode');
      return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }
}
