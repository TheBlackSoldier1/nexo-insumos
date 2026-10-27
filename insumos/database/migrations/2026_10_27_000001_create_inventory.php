<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('locations', function(Blueprint $t){$t->id();$t->string('name')->unique();$t->string('description')->nullable();$t->timestamps();});
  Schema::create('products', function(Blueprint $t){$t->id();$t->string('sku')->unique();$t->string('name');$t->string('category');$t->unsignedInteger('minimum')->default(5);$t->decimal('cost',12,2)->default(0);$t->text('description')->nullable();$t->timestamps();$t->softDeletes();});
  Schema::create('stocks', function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained();$t->foreignId('location_id')->constrained();$t->unsignedInteger('quantity')->default(0);$t->unique(['product_id','location_id']);});
  Schema::create('movements', function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained();$t->string('type');$t->foreignId('from_id')->nullable()->constrained('locations');$t->foreignId('to_id')->nullable()->constrained('locations');$t->unsignedInteger('quantity');$t->dateTime('occurred_at');$t->string('responsible');$t->text('note')->nullable();$t->timestamps();});
  Schema::create('audits', function(Blueprint $t){$t->id();$t->string('action');$t->text('detail');$t->timestamps();});
 }
 public function down(): void {foreach(['audits','movements','stocks','products','locations'] as $name) Schema::dropIfExists($name);}
};
