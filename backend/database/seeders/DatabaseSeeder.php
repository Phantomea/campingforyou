<?php

namespace Database\Seeders;

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
            'email' => 'admin@campingforyou.sk',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // Create owner
        User::create([
            'name' => 'CampingForYou (Majiteľ)',
            'email' => 'info@campingforyou.sk',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        // Create sample caravans
        $caravans = [
            [
                'title' => 'Hobby De Luxe 540 UL',
                'slug' => 'hobby-de-luxe-540-ul',
                'description' => 'Priestranný karavan pre 4 osoby s plne vybavenou kuchyňou a komfortnou spálňou.',
                'manufacturer' => 'Hobby',
                'capacity' => 4,
                'year' => 2022,
                'license_plate' => 'BA123AB',
                'price' => 65.00,
                'price_per_day' => 65.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Fendt Bianco Activ 515 SG',
                'slug' => 'fendt-bianco-activ-515-sg',
                'description' => 'Elegantný 5-miestny karavan s panoramatickým oknom a luxusným vybavením.',
                'manufacturer' => 'Fendt',
                'capacity' => 5,
                'year' => 2023,
                'license_plate' => 'BA456CD',
                'price' => 80.00,
                'price_per_day' => 80.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Adria Altea 472 PX',
                'slug' => 'adria-altea-472-px',
                'description' => 'Kompaktný karavan ideálny pre páry alebo malé rodiny. Ľahký na ťahanie.',
                'manufacturer' => 'Adria',
                'capacity' => 2,
                'year' => 2021,
                'license_plate' => 'BA789EF',
                'price' => 50.00,
                'price_per_day' => 50.00,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($caravans as $caravan) {
            Service::create($caravan);
        }

        // Create pricing categories
        $zakladny  = PricingCategory::create(['name' => 'Základné vybavenie', 'sort_order' => 1]);
        $doplnky   = PricingCategory::create(['name' => 'Doplnkové služby', 'sort_order' => 2]);
        $poistenie = PricingCategory::create(['name' => 'Poistenie', 'sort_order' => 3]);

        // Create sample pricing items
        $pricingItems = [
            ['name' => 'Prenájom karavanu (za deň)', 'price_from' => 50, 'price_to' => 80, 'pricing_category_id' => $zakladny->id, 'sort_order' => 1],
            ['name' => 'Záloha pri rezervácii', 'price_from' => 200, 'price_to' => 300, 'pricing_category_id' => $zakladny->id, 'sort_order' => 2],
            ['name' => 'Čistenie po návrate', 'price_from' => 50, 'price_to' => 80, 'pricing_category_id' => $doplnky->id, 'sort_order' => 1],
            ['name' => 'Bedding set (posteľná bielizeň)', 'price_from' => 15, 'price_to' => 25, 'pricing_category_id' => $doplnky->id, 'sort_order' => 2],
            ['name' => 'Vonkajší gril', 'price_from' => 10, 'price_to' => 15, 'pricing_category_id' => $doplnky->id, 'sort_order' => 3],
            ['name' => 'Základné poistenie (v cene)', 'price_from' => 0, 'price_to' => 0, 'pricing_category_id' => $poistenie->id, 'sort_order' => 1],
            ['name' => 'Rozšírené havarijné poistenie', 'price_from' => 8, 'price_to' => 12, 'pricing_category_id' => $poistenie->id, 'sort_order' => 2],
        ];

        foreach ($pricingItems as $item) {
            PricingItem::create($item);
        }

        // Create settings
        $settings = [
            ['key' => 'site_name', 'value' => 'CampingForYou', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'Prenájom karavanov pre nezabudnuteľné prázdniny', 'type' => 'string'],
            ['key' => 'phone', 'value' => '+421 900 123 456', 'type' => 'string'],
            ['key' => 'email', 'value' => 'info@campingforyou.sk', 'type' => 'string'],
            ['key' => 'address', 'value' => 'Hlavná 123, 851 01 Bratislava', 'type' => 'string'],
            ['key' => 'opening_hours', 'value' => json_encode([
                'Po - Pi' => '9:00 - 17:00',
                'So' => '9:00 - 13:00',
                'Ne' => 'Zatvorené',
            ]), 'type' => 'json'],
            ['key' => 'facebook', 'value' => 'https://facebook.com/campingforyou', 'type' => 'string'],
            ['key' => 'instagram', 'value' => 'https://instagram.com/campingforyou', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // Create pages
        $pages = [
            [
                'slug' => 'about',
                'title' => 'O nás',
                'content' => '<h2>Vitajte v CampingForYou</h2><p>Sme rodinná firma špecializujúca sa na prenájom karavanov. Ponúkame moderné a dobre vybavené karavany pre vaše dovolenky a výlety.</p><p>Náš tím sa postará o to, aby ste na ceste mali pohodlie domova. Každý karavan je pred odovzdaním dôkladne skontrolovaný a vyčistený.</p>',
                'meta_title' => 'O nás | CampingForYou',
                'meta_description' => 'Spoznajte CampingForYou - prenájom karavanov pre nezabudnuteľné prázdniny.',
            ],
            [
                'slug' => 'contact',
                'title' => 'Kontakt',
                'content' => '<h2>Kontaktujte nás</h2><p>Máte otázky alebo chcete rezervovať karavan? Neváhajte nás kontaktovať.</p>',
                'meta_title' => 'Kontakt | CampingForYou',
                'meta_description' => 'Kontaktujte CampingForYou - telefón, email, adresa a otváracie hodiny.',
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
