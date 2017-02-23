<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDidsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('dids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('text');
            $table->string('geo')->nullable();
            $table->integer('client_id');
            $table->timestamps();

            $table->index('user_id');
            $table->index('client_id');
            $table->index('created_at');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('oauth_clients');
        });

        // if using pgsql create a full text search index
        if(DB::connection()->getDriverName() == 'pgsql'){
            DB::statement('ALTER TABLE dids ADD searchable tsvector NULL');
            DB::statement('CREATE INDEX dids_searchable_index ON dids USING GIST (searchable)');
            DB::statement('CREATE TRIGGER ts_searchable 
                BEFORE INSERT OR UPDATE ON dids 
                FOR EACH ROW EXECUTE PROCEDURE 
                    tsvector_update_trigger(\'searchable\', \'pg_catalog.english\', \'text\')'
            );
        }
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dids');
    }
}