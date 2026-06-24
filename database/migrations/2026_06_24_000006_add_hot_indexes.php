<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-17: Add indexes on hot filter/sort/group columns.
 *
 * Additive and MySQL-safe:
 *  - Each index is guarded by Schema::hasColumn() so it is skipped if the
 *    column was never created (this project has had partial-migration issues).
 *  - Every index has an explicit name so down() can drop it reliably.
 *
 * Columns deliberately NOT indexed here:
 *  - businesses.city already has an index (added in 2026_06_11_000013), so a
 *    second one would be redundant.
 */
return new class extends Migration
{
    /**
     * column => index name. Only columns present at run time are indexed.
     *
     * @var array<string, array<string, string>>
     */
    private array $indexes = [
        'businesses' => [
            'status'    => 'businesses_status_index',
            'onboarded' => 'businesses_onboarded_index',
            'featured'  => 'businesses_featured_index',
            'priority'  => 'businesses_priority_index',
            'plan'      => 'businesses_plan_index',
        ],
        'offers' => [
            'status' => 'offers_status_index',
        ],
        'redemptions' => [
            'customer_email' => 'redemptions_customer_email_index',
            'customer_phone' => 'redemptions_customer_phone_index',
            'status'         => 'redemptions_status_index',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $column => $indexName) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->index($column, $indexName);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $column => $indexName) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropIndex($indexName);
                    }
                }
            });
        }
    }
};
