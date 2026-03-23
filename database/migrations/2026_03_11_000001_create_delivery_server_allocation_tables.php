<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_delivery_server')) {
            Schema::create('customer_delivery_server', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->foreignId('delivery_server_id')->constrained('delivery_servers')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['customer_id', 'delivery_server_id'], 'cust_del_srv_unique');
            });
        }

        if (!Schema::hasTable('customer_group_delivery_server')) {
            Schema::create('customer_group_delivery_server', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_group_id')->constrained('customer_groups')->onDelete('cascade');
                $table->foreignId('delivery_server_id')->constrained('delivery_servers')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['customer_group_id', 'delivery_server_id'], 'cust_grp_del_srv_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_group_delivery_server');
        Schema::dropIfExists('customer_delivery_server');
    }
};
