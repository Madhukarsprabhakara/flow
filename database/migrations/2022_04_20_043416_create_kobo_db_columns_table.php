<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kobo_db_columns', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->nullable();
            $table->string('type')->nullable();
            $table->string('kuid')->nullable();
            $table->string('label')->nullable();
            $table->string('autoname')->nullable();
            $table->string('raw_db_column_name')->nullable();
            $table->string('db_column_name')->nullable();
            $table->string('select_from_list_name')->nullable();
            $table->string('kobo_score_choices')->nullable();
            $table->string('kobo_matrix_list')->nullable();
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
        Schema::dropIfExists('kobo_db_columns');
    }
};
