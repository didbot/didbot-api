<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->uuid('user_id')->index();
            $table->string('name')->index();
            $table->uuid('sourceable_id')->index(); // either oauth.client_id or token_id
            $table->string('sourceable_type')->index();
            $table->timestamps();

            $table->unique(['user_id', 'sourceable_id', 'sourceable_type']);
            $table->foreign('user_id')->references('id')->on('users');
        });

    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sources');
    }
}