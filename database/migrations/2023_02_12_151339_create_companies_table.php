<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\CompanySearch\Types\CompanyType;
use Modules\CompanySearch\Types\ImportStage;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->enum('type', array_column(CompanyType::cases(), 'value'))->index();
            $table->enum('import_stage', array_column(ImportStage::cases(), 'value'))->index();
            $table->boolean('is_active')->default(false)->index();
            $table->string('name')->index();
            $table->string('alt_name')->nullable()->index();
            $table->string('slug')->nullable()->index();
            $table->string('number')->index()->nullable();
            $table->string('location_number')->index()->nullable();
            $table->string('rsin')->nullable();
            $table->string('btw')->nullable();
            $table->string('website')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->integer('amount_employees')->nullable();
            $table->string('industry')->nullable();
            $table->mediumText('description')->nullable();

            $table->string('address_country')->default('nl');
            $table->string('address_street')->nullable()->index();
            $table->string('address_zipcode')->nullable()->index();
            $table->string('address_residence')->nullable()->index();
            $table->string('address_house_number')->nullable()->index();
            $table->string('address_addition')->nullable()->index();
            $table->string('address_remark')->nullable()->index();
            $table->string('address_province')->nullable()->index();
            $table->string('address_lat')->nullable()->index();
            $table->string('address_lng')->nullable()->index();
            $table->string('full_address')->nullable()->index();

            $table->string('color_brand')->nullable();
            $table->string('color_accent')->nullable();

            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('crunchbase')->nullable();
            $table->string('github')->nullable();

            $table->date('date_of_creation')->nullable();
            $table->string('google_place_id')->nullable();

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
        Schema::dropIfExists('companies');
    }
};
