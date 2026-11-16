<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('users', function(Blueprint $t) { $t->id(); $t->string('name'); $t->string('email')->unique(); $t->timestamp('email_verified_at')->nullable(); $t->string('password'); $t->rememberToken(); $t->timestamps(); });
        Schema::create('personal_access_tokens', function(Blueprint $t) { $t->id(); $t->morphs('tokenable'); $t->text('name'); $t->string('token', 64)->unique(); $t->text('abilities')->nullable(); $t->timestamp('last_used_at')->nullable(); $t->timestamp('expires_at')->nullable(); $t->timestamps(); });
        Schema::create('flight_sessions', function(Blueprint $t) { $t->string('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('hill',32)->index(); $t->unsignedSmallInteger('gate'); $t->string('physics_version',128)->index(); $t->json('wind'); $t->double('rho'); $t->double('mu'); $t->timestamps(); });
        Schema::create('strategies', function(Blueprint $t) { $t->string('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('hill',32)->index(); $t->string('physics_version',128)->index(); $t->json('controls'); $t->timestamps(); });
        Schema::create('simulations', function(Blueprint $t) { $t->string('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('session_id'); $t->foreign('session_id')->references('id')->on('flight_sessions')->cascadeOnDelete(); $t->string('strategy_id'); $t->foreign('strategy_id')->references('id')->on('strategies')->cascadeOnDelete(); $t->double('distance'); $t->boolean('telemark'); $t->boolean('fall'); $t->json('metrics')->nullable(); $t->timestamps(); });
        Schema::create('real_jumps', function(Blueprint $t) { $t->string('id')->primary(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('session_id'); $t->foreign('session_id')->references('id')->on('flight_sessions')->cascadeOnDelete(); $t->string('strategy_id'); $t->foreign('strategy_id')->references('id')->on('strategies')->cascadeOnDelete(); $t->string('simulation_id'); $t->foreign('simulation_id')->references('id')->on('simulations')->cascadeOnDelete(); $t->double('distance'); $t->boolean('telemark'); $t->boolean('fall'); $t->json('telemetry')->nullable(); $t->timestamps(); });
        Schema::create('cache', function(Blueprint $t) { $t->string('key')->primary(); $t->mediumText('value'); $t->integer('expiration'); });
        Schema::create('cache_locks', function(Blueprint $t) { $t->string('key')->primary(); $t->string('owner'); $t->integer('expiration'); });
        Schema::create('jobs', function(Blueprint $t) { $t->id(); $t->string('queue')->index(); $t->longText('payload'); $t->unsignedTinyInteger('attempts'); $t->unsignedInteger('reserved_at')->nullable(); $t->unsignedInteger('available_at'); $t->unsignedInteger('created_at'); });
        Schema::create('job_batches', function(Blueprint $t) { $t->string('id')->primary(); $t->string('name'); $t->integer('total_jobs'); $t->integer('pending_jobs'); $t->integer('failed_jobs'); $t->longText('failed_job_ids'); $t->mediumText('options')->nullable(); $t->integer('cancelled_at')->nullable(); $t->integer('created_at'); $t->integer('finished_at')->nullable(); });
        Schema::create('failed_jobs', function(Blueprint $t) { $t->id(); $t->string('uuid')->unique(); $t->text('connection'); $t->text('queue'); $t->longText('payload'); $t->longText('exception'); $t->timestamp('failed_at')->useCurrent(); });
        Schema::create('sessions', function(Blueprint $t) { $t->string('id')->primary(); $t->foreignId('user_id')->nullable()->index(); $t->string('ip_address',45)->nullable(); $t->text('user_agent')->nullable(); $t->longText('payload'); $t->integer('last_activity')->index(); });
    }
    public function down(): void {
        foreach(['sessions','failed_jobs','job_batches','jobs','cache_locks','cache','real_jumps','simulations','strategies','flight_sessions','personal_access_tokens','users'] as $table) Schema::dropIfExists($table);
    }
};
