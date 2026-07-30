<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();

            // Link back to the source quote in cayan-l
            $table->unsignedBigInteger('cayan_quote_id');
            $table->string('quote_number')->nullable();

            $table->string('client_name');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('status')->default('unpaid'); // unpaid | paid | void
            $table->json('quote_snapshot')->nullable();  // cayan-l quote payload at verification time
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index('cayan_quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
