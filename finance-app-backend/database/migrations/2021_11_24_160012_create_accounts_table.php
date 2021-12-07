<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('accounts', function (Blueprint $table) {
      $table->id();
      $table->float('balance')->default(0);
      $table->integer('number')->unique()->default(random_int(1000001, 9999999));
      $table->timestamps();

      // Foreign Key for This Table
      $table->unsignedBigInteger('user_id');
      $table->foreign('user_id')->references('id')->on('users');
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
    Schema::dropIfExists('accounts');
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
  }
}
