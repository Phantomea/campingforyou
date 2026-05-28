<?php

namespace Database\Seeders;

use App\Models\AddonService;
use App\Models\Page;
use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create super admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@campingforyou.cz',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // Create owner
        User::create([
            'name' => 'CampingForYou (Majitel)',
            'email' => 'info@campingforyou.cz',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        // Create sample caravans
        $caravans = [
            [
                'title' => 'Hobby De Luxe 540 UL',
                'slug' => 'hobby-de-luxe-540-ul',
                'description' => 'Prostorný karavan pro 4 osoby s plně vybavenou kuchyní a komfortní ložnicí.',
                'manufacturer' => 'Hobby',
                'capacity' => 4,
                'year' => 2022,
                'license_plate' => '1AB1234',
                'price_low_season' => 1200.00,
                'price_high_season' => 1600.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Fendt Bianco Activ 515 SG',
                'slug' => 'fendt-bianco-activ-515-sg',
                'description' => 'Elegantní 5-místný karavan s panoramatickým oknem a luxusním vybavením.',
                'manufacturer' => 'Fendt',
                'capacity' => 5,
                'year' => 2023,
                'license_plate' => '2CD5678',
                'price_low_season' => 1500.00,
                'price_high_season' => 2000.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($caravans as $caravan) {
            Service::create($caravan);
        }

        // Create pricing categories
        $zakladni  = PricingCategory::create(['name' => 'Základní vybavení', 'sort_order' => 1]);
        $doplnky   = PricingCategory::create(['name' => 'Doplňkové služby', 'sort_order' => 2]);
        $pojisteni = PricingCategory::create(['name' => 'Pojištění', 'sort_order' => 3]);

        // Create sample pricing items
        $pricingItems = [
            ['name' => 'Pronájem karavanu (za den)', 'price_from' => 1250, 'price_to' => 2000, 'pricing_category_id' => $zakladni->id, 'sort_order' => 1],
            ['name' => 'Záloha při rezervaci', 'price_from' => 5000, 'price_to' => 7500, 'pricing_category_id' => $zakladni->id, 'sort_order' => 2],
            ['name' => 'Čištění po návratu', 'price_from' => 1250, 'price_to' => 2000, 'pricing_category_id' => $doplnky->id, 'sort_order' => 1],
            ['name' => 'Sada ložního prádla', 'price_from' => 375, 'price_to' => 625, 'pricing_category_id' => $doplnky->id, 'sort_order' => 2],
            ['name' => 'Venkovní gril', 'price_from' => 250, 'price_to' => 375, 'pricing_category_id' => $doplnky->id, 'sort_order' => 3],
            ['name' => 'Základní pojištění (v ceně)', 'price_from' => 0, 'price_to' => 0, 'pricing_category_id' => $pojisteni->id, 'sort_order' => 1],
            ['name' => 'Rozšířené havarijní pojištění', 'price_from' => 200, 'price_to' => 300, 'pricing_category_id' => $pojisteni->id, 'sort_order' => 2],
        ];

        foreach ($pricingItems as $item) {
            PricingItem::create($item);
        }

        // Create settings
        $settings = [
            ['key' => 'site_name',              'value' => 'CampingForYou', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'Pronájem karavanů pro nezapomenutelné prázdniny', 'type' => 'string'],
            ['key' => 'phone', 'value' => '+420 900 123 456', 'type' => 'string'],
            ['key' => 'email', 'value' => 'info@campingforyou.cz', 'type' => 'string'],
            ['key' => 'address', 'value' => 'Hlavní 1, 110 00 Praha', 'type' => 'string'],
            ['key' => 'opening_hours', 'value' => json_encode([
                'Po - Pá' => '9:00 - 17:00',
                'So' => '9:00 - 13:00',
                'Ne' => 'Zavřeno',
            ]), 'type' => 'json'],
            ['key' => 'facebook',              'value' => 'https://facebook.com/campingforyou', 'type' => 'string'],
            ['key' => 'instagram',             'value' => 'https://instagram.com/campingforyou', 'type' => 'string'],
            ['key' => 'iban',                  'value' => 'CZ65 0800 0000 0000 0000 0001', 'type' => 'string'],
            ['key' => 'bank_name',             'value' => 'Česká spořitelna', 'type' => 'string'],
            ['key' => 'bank_bic',              'value' => 'GIBACZPX', 'type' => 'string'],
            ['key' => 'bank_code',             'value' => '0800', 'type' => 'string'],
            ['key' => 'account_holder_name',   'value' => 'CampingForYou s.r.o.', 'type' => 'string'],
            ['key' => 'payment_deadline_days',       'value' => '5',  'type' => 'string'],
            ['key' => 'full_payment_deadline_days',  'value' => '14', 'type' => 'string'],
            ['key' => 'booking_cutoff_time',   'value' => '17:00', 'type' => 'string'],
            ['key' => 'pickup_time',             'value' => '10:00', 'type' => 'string'],
            ['key' => 'return_time',             'value' => '18:00', 'type' => 'string'],
            ['key' => 'deposit_required',        'value' => '0',   'type' => 'boolean'],
            ['key' => 'upfront_payment_percent', 'value' => '100', 'type' => 'string'],
            ['key' => 'season_start_month', 'value' => '5', 'type' => 'string'],
            ['key' => 'season_start_day',   'value' => '1', 'type' => 'string'],
            ['key' => 'season_end_month',   'value' => '9', 'type' => 'string'],
            ['key' => 'season_end_day',     'value' => '1', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // Create addon services
        $addonServices = [
            [
                'name'        => 'Vrátenie bez plnej nádrže',
                'description' => 'Karavan môžete vrátiť bez plnej nádrže, ušetríte čas.',
                'price'       => 80.00,
                'is_premium'  => false,
                'is_active'   => true,
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Vyprázdnenie odpadného tanku pri odovzdaní',
                'description' => 'Nechcete alebo neviete ako? Odpad vyčistíme za vás.',
                'price'       => 50.00,
                'is_premium'  => false,
                'is_active'   => true,
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Umytie po vrátení',
                'description' => 'Karavan za vás umyjeme my.',
                'price'       => 120.00,
                'is_premium'  => false,
                'is_active'   => true,
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Prémiové vrátenie',
                'description' => 'O nič sa nestaráte, karavan umyjeme, vyčistíme, natankujeme my. Môžete tak ušetriť čas, ktorý je lepšie stráviť s blízkymi.',
                'price'       => 220.00,
                'is_premium'  => true,
                'is_active'   => true,
                'sort_order'  => 4,
            ],
        ];

        foreach ($addonServices as $addon) {
            AddonService::create($addon);
        }

        // Create pages
        $pages = [
            [
                'slug' => 'about',
                'title' => 'O nás',
                'content' => '<h2>Vítejte v CampingForYou</h2><p>Jsme rodinná firma specializující se na pronájem karavanů. Nabízíme moderní a dobře vybavené karavany pro vaše dovolené a výlety.</p><p>Náš tým se postará o to, abyste na cestě měli pohodlí domova. Každý karavan je před předáním důkladně zkontrolován a vyčištěn.</p>',
                'meta_title' => 'O nás | CampingForYou',
                'meta_description' => 'Poznejte CampingForYou - pronájem karavanů pro nezapomenutelné prázdniny.',
            ],
            [
                'slug' => 'contact',
                'title' => 'Kontakt',
                'content' => '<h2>Kontaktujte nás</h2><p>Máte otázky nebo chcete rezervovat karavan? Neváhejte nás kontaktovat.</p>',
                'meta_title' => 'Kontakt | CampingForYou',
                'meta_description' => 'Kontaktujte CampingForYou - telefon, e-mail, adresa a otevírací hodiny.',
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
