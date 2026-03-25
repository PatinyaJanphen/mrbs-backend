<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table companies
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->timestamps();
        });

        // 2. Table users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('google_id')->unique();
            $table->string('avatar')->nullable();
            $table->text('google_access_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->string('role')->default('user'); // admin, hr, manager, user
            $table->timestamps();
        });

        // 3. Table resources
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('capacity')->nullable();
            $table->jsonb('equipment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create GIN Index for searching data in JSONB
        DB::statement('CREATE INDEX resources_equipment_gin ON resources USING GIN (equipment)');

        // 4. Table resource_operating_hours
        Schema::create('resource_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->integer('day_of_week'); // 0 = Sunday, 1 = Monday, ...
            $table->time('open_time');
            $table->time('close_time');
            $table->unique(['resource_id', 'day_of_week']);
        });

        // 5. Table bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->string('title');
            $table->dateTimeTz('start_time'); // ระบุ Timezone ป้องกันเวลาเพี้ยน
            $table->dateTimeTz('end_time');
            $table->string('status')->default('pending'); // pending, confirmed, cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('google_event_id')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['resource_id', 'start_time', 'end_time']);
        });

        // Enforce Check Constraint at DB level: End time must be greater than start time
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT check_end_time_greater_than_start_time CHECK (end_time > start_time)');

        // 6. Table booking_attendees
        Schema::create('booking_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_email')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->timestamps();
        });

        // 7. Table logs
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->jsonb('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('booking_attendees');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('resource_operating_hours');
        
        DB::statement('DROP INDEX IF EXISTS resources_equipment_gin');
        Schema::dropIfExists('resources');
        
        Schema::dropIfExists('users');
        Schema::dropIfExists('companies');
    }
};
