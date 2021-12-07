<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('transactions', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('details')->nullable();
      $table->float('amount', 8, 2);
      $table->enum('status', ['COMPLETED', 'FAILED', 'PENDING'])->default('PENDING');
      $table->enum('process_type', ['INCOME', 'EXPENSES']);
      $table->timestamps();

      //Foreign keys
      $table->unsignedBigInteger('account_id');
      $table->foreign('account_id')->references('id')->on('accounts');
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
    Schema::dropIfExists('transactions');
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
  }
}
