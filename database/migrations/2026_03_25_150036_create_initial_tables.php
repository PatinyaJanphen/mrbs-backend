<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('google_id')->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->text('google_access_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->tinyInteger('role')->default(3); // 0: super_admin, 1: admin, 2: staff, 3: user
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Table resources
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('capacity')->nullable();
            $table->jsonb('equipment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // Create GIN Index for searching data in JSONB
        DB::statement('CREATE INDEX resources_equipment_gin ON resources USING GIN (equipment)');

        // 3. Table resource_operating_hours
        Schema::create('resource_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->integer('day_of_week'); // 0 = Sunday, 1 = Monday, ...
            $table->time('open_time');
            $table->time('close_time');
            $table->unique(['resource_id', 'day_of_week']);
        });

        // 4. Table bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->string('title');
            $table->dateTimeTz('start_time'); // ระบุ Timezone ป้องกันเวลาเพี้ยน
            $table->dateTimeTz('end_time');
            $table->tinyInteger('status')->default(0); // 0: pending, 1: confirmed, 2: cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('google_event_id')->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['resource_id', 'start_time', 'end_time']);
        });

        // Enforce Check Constraint at DB level: End time must be greater than start time
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT check_end_time_greater_than_start_time CHECK (end_time > start_time)');

        // 5. Table booking_attendees
        Schema::create('booking_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_email')->nullable();
            $table->tinyInteger('status')->default(0); // 0: pending, 1: accepted, 2: declined
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 6. Table logs
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
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
        Schema::dropIfExists('booking_attendees');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('resource_operating_hours');

        DB::statement('DROP INDEX IF EXISTS resources_equipment_gin');
        Schema::dropIfExists('resources');

        Schema::dropIfExists('logs');
        Schema::dropIfExists('users');
    }
};
