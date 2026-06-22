<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Size;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تحديد مسار ملف الـ JSON
        $filePath = base_path('../HAJA TANIYA forntend/public/js/products.json');
        if (!file_exists($filePath)) {
            $filePath = 'E:\Full Stack Projects\Full Stack\HAJA TANIYA forntend\public\js\products.json';
        }

        if (!file_exists($filePath)) {
            $this->command->error("الملف JSON غير موجود في المسارات المحددة: {$filePath}");
            return;
        }

        $json = json_decode(file_get_contents($filePath), true);
        if (!$json) {
            $this->command->error("فشل في تحليل محتوى ملف JSON أو الملف فارغ.");
            return;
        }

        // 2. قواميس الترجمة إلى العربية لضمان جودة البيانات ومطابقتها للتصميم
        $categoriesTranslations = [
            'Clothing' => 'ملابس',
            'Accessories' => 'إكسسوارات',
            'Footwear' => 'أحذية',
            'Bags' => 'حقائب',
            'Watches' => 'ساعات',
            'Jewelry' => 'مجوهرات',
        ];

        $subCategoriesTranslations = [
            'Women' => 'نساء',
            'Men' => 'رجال',
            'Sunglasses' => 'نظارات شمسية',
            'Sneakers' => 'أحذية رياضية',
            'Totes' => 'حقائب يد كبيرة',
            'Analog' => 'ساعات عقارب',
            'Kids' => 'أطفال',
            'Hats' => 'قبعات',
            'Belts' => 'أحزمة',
            'Scarves' => 'أوشحة',
            'Wallets' => 'محافظ',
            'Boots' => 'أحذية طويلة',
            'Loafers' => 'لوفرز',
            'Formal' => 'رسمي',
            'Sandals' => 'صنادل',
            'Backpacks' => 'حقائب ظهر',
            'Clutches' => 'حقائب يد صغيرة (كلتش)',
            'Travel' => 'سفر',
            'Handbags' => 'حقائب يد',
            'Rings' => 'خواتم',
            'Bracelets' => 'أساور',
            'Earrings' => 'أقراط',
            'Necklaces' => 'قلادات',
            'Smartwatches' => 'ساعات ذكية',
            'Digital' => 'رقمي',
            'Luxury' => 'فاخر',
        ];

        $sizesTranslations = [
            'Standard' => 'قياسي',
            'Large Fit' => 'مقاس واسع',
            'Small Fit' => 'مقاس ضيق',
            'One Size' => 'مقاس موحد',
            'Small' => 'صغير',
            'Medium' => 'متوسط',
            'Large' => 'كبير',
            'Expressive XL' => 'إكس لارج معبر',
            'Premium XL' => 'إكس لارج فاخر',
            'S/M' => 'صغير/متوسط',
            'L/XL' => 'كبير/إكس لارج',
            'Universal' => 'عالمي',
            'Standard Drop' => 'طول قياسي',
            'Long Drop' => 'طول طويل',
        ];

        $colorsTranslations = [
            'Charcoal' => 'فحمي',
            'Navy Blue' => 'كحلي',
            'Classic Camel' => 'جملي كلاسيكي',
            'Black' => 'أسود',
            'Deep Red' => 'أحمر داكن',
            'Arctic White' => 'أبيض ثلجي',
            'Midnight Black' => 'أسود منتصف الليل',
            'Heather Grey' => 'رمادي',
            'Soft Olive' => 'زيتوني ناعم',
            'Navy' => 'كحلي',
            'Bordeaux' => 'بورودو',
            'Gold / Green' => 'ذهبي / أخضر',
            'Silver / Blue' => 'فضي / أزرق',
            'Matte Black' => 'أسود مطفي',
            'Rose Gold' => 'ذهبي وردي',
            'Cloud White' => 'أبيض سحابي',
            'Cognac Tan' => 'بني كوجناك',
            'Onyx Black' => 'أسود أونيكس',
            'Navy Suede' => 'كحلي شمواه',
            'Sand' => 'رملي',
            'Deep Noir' => 'أسود داكن',
            'Vintage Burgundy' => 'برغندي كلاسيكي',
            'Forest Green' => 'أخضر الغابة',
            'Sand Beige' => 'بيج رملي',
            'Midnight Blue' => 'أزرق داكن',
            'Silver Steel' => 'فضة فولاذية',
            'Gold Steel' => 'ذهب فولاذي',
            'Black Obsidian' => 'أسود أوبسيديان',
            'Emerald Green' => 'أخضر زمردي',
            'Crimson Red' => 'أحمر قرمزي',
            'Royal Purple' => 'بنفسجي ملكي',
            'Midnight' => 'أزرق داكن',
            'Mustard' => 'خردلي',
            'Dusty Blue' => 'أزرق غباري',
            'Soft Pink' => 'وردي ناعم',
            'Mint Green' => 'أخضر نعناعي',
            'Lemon' => 'ليموني',
            'Khaki' => 'كاكي',
            'Olive' => 'زيتوني',
            'Stone' => 'حجري',
            'Deep Plum' => 'برقوقي داكن',
            'Classic Black' => 'أسود كلاسيكي',
            'Camel' => 'جملي',
            'Soft White' => 'أبيض ناعم',
            'Sun Yellow' => 'أصفر شمس',
            'Ocean Blue' => 'أزرق المحيط',
            'Bright Red' => 'أحمر ساطع',
            'Grass Green' => 'أخضر عشبي',
            'Light Blue' => 'أزرق فاتح',
            'Off-White' => 'أوف وايت',
            'Tan' => 'بيج داكن',
            'White' => 'أبيض',
            'Grey' => 'رمادي',
            'Royal Blue' => 'أزرق ملكي',
            'Orange' => 'برتقالي',
            'Sky Blue' => 'أزرق سماوي',
            'Pink' => 'وردي',
            'Light Grey' => 'رمادي فاتح',
            'Pink Floral' => 'وردي مشجر',
            'Blue Floral' => 'أزرق مشجر',
            'Yellow Floral' => 'أصفر مشجر',
            'Green Floral' => 'أخضر مشجر',
            'Stealth Black' => 'أسود متخفي',
            'Walnut' => 'جوزي',
            'Cognac' => 'كوجناك',
            'Dark Brown' => 'بني داكن',
            'Heritage Plaid' => 'كاروهات كلاسيكية',
            'Blue/Grey Plaid' => 'كاروهات أزرق/رمادي',
            'Monochrome Plaid' => 'كاروهات أحادية اللون',
            'Espresso' => 'إسبريسو',
            'Burgundy' => 'برغندي',
            'Tobacco' => 'تبغي',
            'Dark Walnut' => 'جوز داكن',
            'Jet Black' => 'أسود نفاث',
            'Grey Suede' => 'رمادي شمواه',
            'Tan Suede' => 'بيج شمواه',
            'Olive Suede' => 'زيتوني شمواه',
            'High Gloss Black' => 'أسود لامع',
            'Deep Midnight Blue' => 'أزرق داكن عميق',
            'Burgundy Lace' => 'دانتيل برغندي',
            'Gold' => 'ذهبي',
            'Matte Grey' => 'رمادي مطفي',
            'Military Green' => 'أخضر عسكري',
            'Champagne' => 'شامبانيا',
            'Pearl White' => 'أبيض لؤلؤي',
            'Vintage Brown' => 'بني كلاسيكي',
            'Mahogany' => 'ماهوجني',
            'Ebony' => 'أبنوس',
            'Nude' => 'لحمي',
            'White Gold' => 'ذهب أبيض',
            'Yellow Gold' => 'ذهب أصفر',
            'Cream / Gold' => 'كريمي / ذهبي',
            'White / Silver' => 'أبيض / فضي',
            'Pink / Gold' => 'وردي / ذهبي',
            'Space Grey' => 'رمادي فضائي',
            'Military Olive' => 'زيتوني عسكري',
            'Desert Tan' => 'رملي صحراوي',
            'Steel / Blue' => 'فولاذي / أزرق',
            'Gold / Silver' => 'ذهبي / فضي',
            'Rose / Brown' => 'وردي / بني',
            'Full Black' => 'أسود كامل',
        ];

        $titleTranslations = [
            'Tailored Wool Coat' => 'معطف صوف محبوك',
            'Essential Cotton Tee' => 'تي شيرت قطني أساسي',
            'Aviator Sunglasses' => 'نظارة شمسية أفياتور',
            'Premium Leather Sneakers' => 'حذاء رياضي جلدي فاخر',
            'Luxury Tote Bag' => 'حقيبة يد كبيرة فاخرة (Tote)',
            'Classic Chronograph' => 'ساعة كرونوغراف كلاسيكية',
            'Silk Evening Gown' => 'فستان سهرة حريري',
            'Men\'s Urban Parka' => 'سترة باركا حضرية للرجال',
            'Kids\' Organic Pajama Set' => 'طقم بيجامة قطن عضوي للأطفال',
            'Men\'s Slim Fit Chinos' => 'بنطال تشينو ضيق للرجال',
            'Women\'s Cashmere Wrap' => 'شال كشمير نسائي',
            'Kids\' Hooded Raincoat' => 'معطف مطر بقلنسوة للأطفال',
            'Men\'s Linen Summer Suit' => 'بدلة صيفية كتان للرجال',
            'Women\'s Leather Biker Jacket' => 'سترة جلدية بايكر للنساء',
            'Kids\' Graphic Cotton Tee' => 'تي شيرت قطني برسومات للأطفال',
            'Men\'s Oxford Button-Down' => 'قميص أكسفورد بأزرار للرجال',
            'Women\'s Floral Maxi Dress' => 'فستان ماكسي مشجر للنساء',
            'Wool Fedora Hat' => 'قبعة فيدورا صوفية',
            'Handcrafted Leather Belt' => 'حزام جلدي مصنوع يدوياً',
            'Cashmere Plaid Scarf' => 'وشاح كشمير كاروهات',
            'Slim Bi-fold Wallet' => 'محفظة ثنائية الطي ضيقة',
            'Rugged Work Boots' => 'حذاء عمل متين',
            'Classic Suede Loafers' => 'حذاء لوفر شمواه كلاسيكي',
            'Patent Formal Shoes' => 'حذاء رسمي جلد لامع',
            'Leather Strappy Sandals' => 'صندل جلدي بسيور',
            'City Tech Backpack' => 'حقيبة ظهر تقنية للمدينة',
            'Quilted Evening Clutch' => 'حقيبة يد سهرة مبطنة',
            'Leather Weekender Bag' => 'حقيبة سفر جلدية لعطلة نهاية الأسبوع',
            'Minimalist Handbag' => 'حقيبة يد كلاسيكية بسيطة',
            'Diamond Solitaire Ring' => 'خاتم سوليتير ألماس',
            'Gold Link Bracelet' => 'سوار ذهبي حلقات',
            'Pearl Drop Earrings' => 'أقراط لؤلؤ متدلية',
            'Initial Pendant Necklace' => 'قلادة مع تعليقة حرف',
            'Modern Smartwatch' => 'ساعة ذكية حديثة',
            'Digital Explorer Watch' => 'ساعة مستكشف رقمية',
            'Luxury Sapphire Watch' => 'ساعة ياقوت فاخرة',
        ];

        $descTranslations = [
            'Tailored Wool Coat' => 'مصنوع بدقة متناهية من مزيج الصوف الإيطالي الفاخر، يوفر هذا المعطف المحبوك مظهراً راقياً ومميزاً للمهنيين العصريين.',
            'Essential Cotton Tee' => 'قطعة أساسية فاخرة مصنوعة من قطن البيما العضوي 100%. يسمح بمرور الهواء، متين وذو مقاس مثالي.',
            'Aviator Sunglasses' => 'تصميم كلاسيكي مع عدسات مستقطبة وإطارات من التيتانيوم ذات لون ذهبي. حماية فائقة من الأشعة فوق البنفسجية وأناقة لا مثيل لها.',
            'Premium Leather Sneakers' => 'حذاء رياضي جلدي مصنوع يدوياً بمظهر أنيق. مصمم لراحة طوال اليوم دون التضحية بالأناقة.',
            'Luxury Tote Bag' => 'حقيبة يد واسعة وأنيقة، مصنوعة من أجود أنواع جلد سافيانو. مصممة للمرأة النشيطة وكثيرة التنقل.',
            'Classic Chronograph' => 'دقة حركة تلتقي مع البساطة الأنيقة. زجاج من كريستال الياقوت وحزام من الفولاذ المقاوم للصدأ الفاخر.',
            'Silk Evening Gown' => 'فستان سهرة مذهل يصل إلى الأرض مصنوع من حرير التوت النقي 100%. أناقة سهلة لياليك الأكثر تميزاً.',
            'Men\'s Urban Parka' => 'سترة باركا مقاومة للعوامل الجوية مصممة للمستكشف الحضري. تجمع بين الأناقة والحماية القصوى.',
            'Kids\' Organic Pajama Set' => 'بيجامة ناعمة للغاية مصنوعة من قطن عضوي 100% لنوم مريح وهادئ.',
            'Men\'s Slim Fit Chinos' => 'بنطال تشينو ضيق ومتعدد الاستخدامات مصنوع من قطن مرن عالي الجودة لراحة طوال اليوم.',
            'Women\'s Cashmere Wrap' => 'شال فاخر من الكشمير النقي، مثالي للارتداء فوق الملابس خلال الأمسيات الباردة.',
            'Kids\' Hooded Raincoat' => 'معطف مطر ملون ومقاوم للماء لإبقاء أطفالك جافين أثناء وقت اللعب.',
            'Men\'s Linen Summer Suit' => 'بدلة كتان تسمح بمرور الهواء، مثالية لحفلات الزفاف الخارجية والمناسبات الصيفية.',
            'Women\'s Leather Biker Jacket' => 'سترة جلدية عصرية وكلاسيكية مع إكسسوارات فضية اللون.',
            'Kids\' Graphic Cotton Tee' => 'تي شيرت مرح برسومات ممتعة مصنوع من قطن ناعم ومريح للأطفال.',
            'Men\'s Oxford Button-Down' => 'قطعة أساسية كلاسيكية في خزانة الملابس، قميص أكسفورد مصنوع من قطن متين.',
            'Women\'s Floral Maxi Dress' => 'فستان ماكسي أنيق بنقشة الزهور وبتصميم ملفوف جذاب، مثالي لأيام الصيف.',
            'Wool Fedora Hat' => 'قبعة فيدورا كلاسيكية بحافة عريضة مصنوعة من الصوف اللباد الفاخر.',
            'Handcrafted Leather Belt' => 'حزام من الجلد الطبيعي الكامل مع إبزيم من النحاس الصلب.',
            'Cashmere Plaid Scarf' => 'وشاح كشمير ناعم للغاية بنمط كاروهات كلاسيكي أنيق.',
            'Slim Bi-fold Wallet' => 'محفظة جلدية بسيطة مصممة لمن يفضلون الأساسيات العصرية.',
            'Rugged Work Boots' => 'أحذية جلدية متينة بنعل مانع للانزلاق وهيكل متين للعمل الشاق.',
            'Classic Suede Loafers' => 'حذاء لوفر شمواه مخيط يدوياً لمظهر أنيق ومريح في آن واحد.',
            'Patent Formal Shoes' => 'حذاء أكسفورد رسمي أنيق من الجلد اللامع للمناسبات الرسمية والخاصة.',
            'Leather Strappy Sandals' => 'صندل جلدي مصنوع يدوياً بسيور قابلة للتعديل.',
            'City Tech Backpack' => 'حقيبة ظهر عملية تحتوي على قسم مخصص للكمبيوتر المحمول بمقاس 16 بوصة.',
            'Quilted Evening Clutch' => 'حقيبة سهرة أنيقة من الجلد المبطن مع حزام سلسلة ذهبي اللون.',
            'Leather Weekender Bag' => 'حقيبة سفر واسعة مصنوعة من الجلد القوي الفاخر.',
            'Minimalist Handbag' => 'حقيبة يد أنيقة ومنظمة لجمال يومي راقٍ.',
            'Diamond Solitaire Ring' => 'خاتم ألماس رائع عيار 1 قيراط مرصع بالذهب الأبيض عيار 18 قيراط.',
            'Gold Link Bracelet' => 'سوار من الذهب الخالص عيار 14 قيراط بتصميم حلقات مع لمسة نهائية مصقولة للغاية.',
            'Pearl Drop Earrings' => 'أقراط من لؤلؤ المياه العذبة اللامع المطلي بالذهب عيار 18 قيراط.',
            'Initial Pendant Necklace' => 'قلادة مخصصة من الذهب عيار 14 قيراط مع تعليقة للحرف الخاص بك.',
            'Modern Smartwatch' => 'تتبع صحي متطور للغاية مع شاشة OLED فاخرة.',
            'Digital Explorer Watch' => 'ساعة رقمية متينة مزودة بنظام تحديد المواقع العالمي (GPS) ومقاومة للماء.',
            'Luxury Sapphire Watch' => 'ساعة سويسرية فاخرة مزودة بزجاج ياقوتي مقاوم للخدش.',
        ];

        // 3. البدء في المعالجة والإدخال
        $this->command->info("بدء استيراد المنتجات من ملف JSON...");

        foreach ($json as $p) {
            $categoryName = $p['category'] ?? 'General';
            $subCategoryName = $p['subCategory'] ?? null;

            // أ. إدخال القسم الرئيسي
            $parentCategory = Category::where('name_en', $categoryName)->whereNull('parent_id')->first();
            if (!$parentCategory) {
                $parentCategory = Category::create([
                    'name_en' => $categoryName,
                    'name_ar' => $categoriesTranslations[$categoryName] ?? $categoryName,
                    'slug' => Str::slug($categoryName),
                    'parent_id' => null
                ]);
            }

            $targetCategoryId = $parentCategory->id;

            // ب. إدخال القسم الفرعي إذا وُجد
            if ($subCategoryName) {
                $subCategory = Category::where('name_en', $subCategoryName)->where('parent_id', $parentCategory->id)->first();
                if (!$subCategory) {
                    $subCategory = Category::create([
                        'name_en' => $subCategoryName,
                        'name_ar' => $subCategoriesTranslations[$subCategoryName] ?? $subCategoryName,
                        'slug' => Str::slug($categoryName . '-' . $subCategoryName),
                        'parent_id' => $parentCategory->id
                    ]);
                }
                $targetCategoryId = $subCategory->id;
            }

            // ج. إدخال المقاسات
            $sizeIds = [];
            if (isset($p['sizes']) && is_array($p['sizes'])) {
                foreach ($p['sizes'] as $sizeName) {
                    $size = Size::where('name_en', $sizeName)->first();
                    if (!$size) {
                        $sizeAr = $sizeName;
                        if (isset($sizesTranslations[$sizeName])) {
                            $sizeAr = $sizesTranslations[$sizeName];
                        } elseif (preg_match('/^(\d+)mm Case$/i', $sizeName, $matches)) {
                            $sizeAr = 'علبة ' . $matches[1] . ' مم';
                        } elseif (preg_match('/^(\d+)L$/i', $sizeName, $matches)) {
                            $sizeAr = $matches[1] . ' لتر';
                        } elseif (preg_match('/^(\d+) inch$/i', $sizeName, $matches)) {
                            $sizeAr = $matches[1] . ' بوصة';
                        } elseif (preg_match('/^(\d+)mm$/i', $sizeName, $matches)) {
                            $sizeAr = $matches[1] . ' مم';
                        }

                        $size = Size::create([
                            'name_en' => $sizeName,
                            'name_ar' => $sizeAr
                        ]);
                    }
                    $sizeIds[] = $size->id;
                }
            }

            // د. إدخال الألوان
            $colorIds = [];
            if (isset($p['colors']) && is_array($p['colors'])) {
                foreach ($p['colors'] as $colorData) {
                    $colorName = $colorData['name'];
                    $hex = $colorData['hex'] ?? '#000000';

                    $color = Color::where('name_en', $colorName)->first();
                    if (!$color) {
                        $color = Color::create([
                            'name_en' => $colorName,
                            'name_ar' => $colorsTranslations[$colorName] ?? $colorName,
                            'hex' => $hex
                        ]);
                    }
                    $colorIds[] = $color->id;
                }
            }

            // هـ. إدخال أو تحديث المنتج نفسه
            $titleEn = $p['title'];
            $price = $p['price'];
            $oldPrice = $p['oldPrice'] ?? null;
            $discount = $p['discount'] ?? null;
            $isBestseller = $p['isBestseller'] ?? false;
            $isNew = $p['isNew'] ?? true;
            $imageUrl = $p['image'] ?? '';
            $descEn = $p['description'] ?? '';

            $titleAr = $titleTranslations[$titleEn] ?? $titleEn;
            $descAr = $descTranslations[$titleEn] ?? $descEn;

            $product = Product::where('title_en', $titleEn)->first();
            $productData = [
                'category_id' => $targetCategoryId,
                'title_en' => $titleEn,
                'title_ar' => $titleAr,
                'slug' => Str::slug($titleEn),
                'description_en' => $descEn,
                'description_ar' => $descAr,
                'price' => $price,
                'old_price' => $oldPrice,
                'discount' => $discount,
                'image_url' => $imageUrl,
                'is_bestseller' => $isBestseller,
                'is_new' => $isNew,
                'stock_quantity' => 20, // كمية افتراضية للمخزون
            ];

            if ($product) {
                $product->update($productData);
                $this->command->info("تم تحديث المنتج: {$titleEn}");
            } else {
                $product = Product::create($productData);
                $this->command->info("تمت إضافة منتج جديد: {$titleEn}");
            }

            // و. ربط المقاسات والألوان بالمنتج عبر الـ pivot tables باستخدام sync لمنع التكرار
            $product->sizes()->sync($sizeIds);
            $product->colors()->sync($colorIds);
        }

        $this->command->info("تم استيراد جميع المنتجات والبيانات المرتبطة بها بنجاح! 🎉");
    }
}