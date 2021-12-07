<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavingTransactionsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('saving_transactions', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('name');
      $table->float('amount', 8, 2);
      $table->enum('process_type', ['FUND', 'WITHDRAW']);
      $table->foreignId('savings_id')->constrained('savings')->onUpdate('cascade')
        ->onDelete('cascade');
      // $table->foreignId('transaction_id')->constrained('transactions')->onUpdate('cascade')
      //   ->onDelete('cascade');
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
    Schema::dropIfExists('saving_transactions');
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
  }
}
