<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            ['name' => 'Marketing & Promotional', 'slug' => 'marketing', 'icon' => 'megaphone', 'sort_order' => 10],
            ['name' => 'Transactional', 'slug' => 'transactional', 'icon' => 'receipt', 'sort_order' => 20],
            ['name' => 'Automation & Drip', 'slug' => 'automation', 'icon' => 'zap', 'sort_order' => 30],
            ['name' => 'Cold & Outreach', 'slug' => 'cold', 'icon' => 'send', 'sort_order' => 40],
            ['name' => 'Relationship & Engagement', 'slug' => 'relationship', 'icon' => 'heart', 'sort_order' => 50],
            ['name' => 'Support & System', 'slug' => 'support', 'icon' => 'headphones', 'sort_order' => 60],
            ['name' => 'E-commerce', 'slug' => 'ecommerce', 'icon' => 'shopping', 'sort_order' => 70],
            ['name' => 'Other', 'slug' => 'other', 'icon' => 'folder', 'sort_order' => 999],
        ];

        foreach ($rows as $row) {
            DB::table('public_template_categories')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'icon' => $row['icon'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('public_template_categories')->whereIn('slug', [
            'marketing',
            'transactional',
            'automation',
            'cold',
            'relationship',
            'support',
            'ecommerce',
            'other',
        ])->delete();
    }
};
