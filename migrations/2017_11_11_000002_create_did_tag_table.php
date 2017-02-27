<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDidTagTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('did_tag', function (Blueprint $table) {
            $table->uuid('did_id');
            $table->uuid('tag_id');
            $table->timestamps();

            $table->foreign('did_id')->references('id')->on('dids');
            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('did_tag');
    }
}