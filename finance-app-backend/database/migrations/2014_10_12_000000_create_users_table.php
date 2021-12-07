<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name', 255);
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->date('dob')->nullable();
      $table->string('contact_number', 20)->nullable();
      $table->tinyInteger('role')->default(0);
      $table->enum('status', ['ACTIVE', 'INACTIVE', 'DELETED', 'PENDING'])->default('ACTIVE');
      $table->rememberToken();
      $table->timestamps();
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
    Schema::dropIfExists('users');
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
  }
}
