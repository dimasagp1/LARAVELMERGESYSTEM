<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kategori Aset Tetap GA & Masa Manfaat Depresiasi
        Schema::create('ga_asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. 01, 02, 03, 04
            $table->string('name'); // Alat Kantor, Elektronik & IT, Peralatan Gedung/Utilitas, Kendaraan
            $table->unsignedInteger('useful_life_years')->default(4); // Standar 4 Tahun Garis Lurus
            $table->timestamps();
        });

        // 2. Master Aset Tetap GA Multi-Plant (HII-PLANT-DEPT-KAT-NO)
        Schema::create('ga_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique(); // e.g. HII-01-GA-01-MEJ-0001
            $table->string('name');
            $table->foreignId('plant_id')->constrained('plants');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('category_id')->constrained('ga_asset_categories');
            $table->enum('classification', ['UTAMA', 'TERPISAH', 'BERGERAK'])->default('UTAMA');
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->unsignedInteger('useful_life_years')->default(4);
            $table->enum('condition', ['Baik', 'Rusak Ringan', 'Rusak Berat/Perbaikan', 'DISPOSED'])->default('Baik');
            $table->string('pic_name')->nullable();
            $table->string('barcode_qr')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Mutasi Aset Lintas Lokasi & Plant
        Schema::create('ga_asset_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('ga_assets')->cascadeOnDelete();
            $table->date('mutation_date');
            $table->foreignId('from_plant_id')->constrained('plants');
            $table->foreignId('to_plant_id')->constrained('plants');
            $table->foreignId('from_department_id')->constrained('departments');
            $table->foreignId('to_department_id')->constrained('departments');
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('pic_receiver');
            $table->text('reason');
            $table->foreignId('transferred_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Master Kategori ATK & Rumah Tangga (RT)
        Schema::create('ga_atk_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['ATK', 'RT'])->default('ATK');
            $table->timestamps();
        });

        // 5. Katalog Barang Standar ATK & RT
        Schema::create('ga_atk_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('ga_atk_categories')->cascadeOnDelete();
            $table->string('item_code')->unique(); // ATK-KRT-01, RT-SBN-01
            $table->string('name');
            $table->foreignId('uom_id')->constrained('uoms');
            $table->unsignedInteger('min_stock')->default(5);
            $table->unsignedInteger('current_stock')->default(20);
            $table->decimal('standard_cost', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Compatibility Views for Direct ERD table names
        try {
            DB::statement('CREATE VIEW assets AS SELECT * FROM ga_assets');
            DB::statement('CREATE VIEW asset_mutations AS SELECT * FROM ga_asset_mutations');
            DB::statement('CREATE VIEW atk_items AS SELECT * FROM ga_atk_items');
        } catch (Throwable $e) {
            // Ignore in environments where views cannot be created
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS atk_items');
            DB::statement('DROP VIEW IF EXISTS asset_mutations');
            DB::statement('DROP VIEW IF EXISTS assets');
        } catch (Throwable $e) {
        }

        Schema::dropIfExists('ga_atk_items');
        Schema::dropIfExists('ga_atk_categories');
        Schema::dropIfExists('ga_asset_mutations');
        Schema::dropIfExists('ga_assets');
        Schema::dropIfExists('ga_asset_categories');
    }
};
