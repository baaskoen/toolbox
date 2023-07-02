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
        Schema::create('api_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_query_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('api_name')->index();
            $table->string('search_query_type')->index();
            $table->longText('response');
            $table->string('data_type');
            $table->json('headers')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('was_successful')->default(false)->index();
            $table->boolean('processed')->default(false)->index();

            $table->foreign('search_query_id')
                ->references('id')
                ->on('search_queries')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

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
        Schema::dropIfExists('api_responses');
    }
};
