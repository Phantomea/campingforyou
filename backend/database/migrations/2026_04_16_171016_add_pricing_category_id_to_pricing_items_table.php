<?php

use App\Models\PricingCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pricing_items', 'pricing_category_id')) {
            Schema::table('pricing_items', function (Blueprint $table) {
                $table->foreignId('pricing_category_id')->nullable()->constrained()->nullOnDelete()->after('name');
            });
        }

        // Migrate existing category strings to FK references
        if (Schema::hasColumn('pricing_items', 'category')) {
            $existing = DB::table('pricing_items')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->select('category')
                ->distinct()
                ->pluck('category');

            foreach ($existing as $categoryName) {
                $category = PricingCategory::firstOrCreate(['name' => $categoryName]);
                DB::table('pricing_items')
                    ->where('category', $categoryName)
                    ->update(['pricing_category_id' => $category->id]);
            }

            Schema::table('pricing_items', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pricing_items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        // Restore category strings from FK
        $items = DB::table('pricing_items')
            ->whereNotNull('pricing_category_id')
            ->get(['id', 'pricing_category_id']);

        foreach ($items as $item) {
            $category = DB::table('pricing_categories')->find($item->pricing_category_id);
            if ($category) {
                DB::table('pricing_items')
                    ->where('id', $item->id)
                    ->update(['category' => $category->name]);
            }
        }

        Schema::table('pricing_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_category_id');
        });
    }
};
