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
            $table->uuid('id');
            $table->integer('user_id');
            $table->string('text');
            $table->string('geo')->nullable();
            $table->integer('client_id');
            $table->timestamps();

            $table->primary('id');
            $table->index('user_id');
            $table->index('client_id');
            $table->index('created_at');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('oauth_clients');
        });

        // if using pgsql create a full text search index and the uuid timestamp function
        if(DB::connection()->getDriverName() == 'pgsql'){
            DB::statement('ALTER TABLE dids ADD searchable tsvector NULL');
            DB::statement('CREATE INDEX dids_searchable_index ON dids USING GIST (searchable)');
            DB::statement('CREATE TRIGGER ts_searchable 
                BEFORE INSERT OR UPDATE ON dids 
                FOR EACH ROW EXECUTE PROCEDURE 
                    tsvector_update_trigger(\'searchable\', \'pg_catalog.english\', \'text\')'
            );

            DB::statement('
                CREATE OR REPLACE FUNCTION uuid_v1_timestamp (_uuid uuid)
                RETURNS TIMESTAMP WITH TIME zone AS $$
                
                    SELECT
                        to_timestamp(
                            (
                                (\'x\' || lpad(h, 16, \'0\'))::bit(64)::bigint::DOUBLE PRECISION -
                                122192928000000000
                            ) / 10000000
                        )
                    FROM (
                        SELECT
                            SUBSTRING (u FROM 16 FOR 3) ||
                            SUBSTRING (u FROM 10 FOR 4) ||
                            SUBSTRING (u FROM 1 FOR 8) AS h
                        FROM (VALUES (_uuid::text)) s (u)
                    ) s
                    ;
                
                $$ LANGUAGE SQL immutable;
            ');
        }

        DB::statement('CREATE index uuid_timestamp_ndx ON dids (uuid_v1_timestamp(id));');
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