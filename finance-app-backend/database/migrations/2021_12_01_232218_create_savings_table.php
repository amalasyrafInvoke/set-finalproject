<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavingsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('savings', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('name');
      $table->string('icon', 50)->nullable();
      $table->float('current_amount', 8, 2)->default(0);
      $table->float('target_amount', 8, 2);
      $table->date('due_date')->nullable();
      $table->enum('status', ['ACTIVE', 'DELETED'])->default('ACTIVE');
      $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {

    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    Schema::dropIfExists('savings');
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
  }
}
