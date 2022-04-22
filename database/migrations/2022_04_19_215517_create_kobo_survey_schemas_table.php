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
        Schema::create('kobo_survey_schemas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kobo_survey_id')->unsigned();
            $table->foreign('kobo_survey_id')->references('id')->on('kobo_surveys');
            $table->string('asset_id');
            $table->string('survey_hash');
            $table->jsonb('survey_schema_json_original')->nullable();
            $table->jsonb('survey_schema_choices_json_original')->nullable();
            $table->jsonb('survey_schema_processed')->nullable();
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
        Schema::dropIfExists('kobo_survey_schemas');
    }
};
