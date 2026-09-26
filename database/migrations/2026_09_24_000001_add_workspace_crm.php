<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $t) {
            $t->string('plan')->default('starter');
            $t->string('subscription_status')->default('trial');
            $t->timestamp('trial_ends_at')->nullable();
            $t->timestamp('paid_until')->nullable();
            $t->boolean('cancel_at_period_end')->default(false);
            $t->boolean('ai_enabled')->default(false);
        });
        Schema::create('contacts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('organization')->nullable();
            $t->string('status')->default('lead');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'email']);
            $t->index(['company_id', 'status']);
        });
        Schema::create('deals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->string('title');
            $t->decimal('value', 14, 2)->default(0);
            $t->string('stage')->default('new');
            $t->date('expected_close')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['company_id', 'stage']);
        });
        Schema::create('crm_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->text('notes')->nullable();
            $t->date('due_date');
            $t->string('status')->default('open');
            $t->timestamp('reminded_at')->nullable();
            $t->timestamps();
            $t->index(['company_id', 'status', 'due_date']);
        });
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->string('number')->unique();
            $t->string('plan');
            $t->unsignedBigInteger('amount_cents');
            $t->string('currency', 3);
            $t->string('status')->default('open');
            $t->date('due_date');
            $t->timestamp('paid_at')->nullable();
            $t->date('period_end');
            $t->string('payment_reference')->nullable()->unique();
            $t->timestamps();
        });
        Schema::create('ai_generations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $t->string('kind');
            $t->string('model');
            $t->string('status')->default('pending');
            $t->text('output')->nullable();
            $t->unsignedInteger('tokens')->default(0);
            $t->timestamps();
            $t->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['ai_generations', 'invoices', 'crm_tasks', 'deals', 'contacts'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('companies', fn (Blueprint $t) => $t->dropColumn(['plan', 'subscription_status', 'trial_ends_at', 'paid_until', 'cancel_at_period_end', 'ai_enabled']));
    }
};
