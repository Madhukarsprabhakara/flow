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
        Schema::create('connector_auth_fields', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('connector_auth_id')->unsigned();
            $table->foreign('connector_auth_id')->references('id')->on('connector_auths');
            $table->string('field_name');
            $table->text('field_help_text')->nullable();
            $table->string('field_type');
            $table->integer('sort_order');
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
        Schema::dropIfExists('connector_auth_fields');
    }
};
