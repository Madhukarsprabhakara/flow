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
        Schema::create('kobo_db_column_choices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kobo_db_column_id')->unsigned();
            $table->foreign('kobo_db_column_id')->references('id')->on('kobo_db_columns');
            $table->string('name')->nullable();
            $table->string('kuid')->nullable();
            $table->string('label')->nullable();
            $table->string('list_name')->nullable();
            $table->string('autovalue')->nullable();
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
        Schema::dropIfExists('kobo_db_column_choices');
    }
};
