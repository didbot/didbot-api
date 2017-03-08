<?php
namespace Didbot\DidbotApi\Test;

use \Laravel\Passport\Client;
use \Didbot\DidbotApi\Test\Models\User;
use Didbot\DidbotApi\Models\Did;
use Didbot\DidbotApi\Models\Source;

class SourceTest extends TestCase
{

     public function testSourcesHaveDids()
     {

         $user = factory(User::class)->create();
         $client = factory(Client::class)->create(['user_id' => $user->id]);
         $source = factory(Source::class)->create(['user_id' => $user->id, 'sourceable_id'=> $client->id, 'sourceable_type' => 'client']);
         $did = factory(Did::class)->create(['user_id' => $user->id, 'source_id' => $source->id]);

         $dids = $source->dids;
         $this->assertEquals($did->id, $dids[0]->id);
     }

    public function testSearchSourceByName()
    {

        $user = factory(User::class)->create();
        $client = factory(Client::class)->create(['user_id' => $user->id]);
        $source = factory(Source::class)->create([
            'name' => 'This is the source',
            'user_id' => $user->id,
            'sourceable_id'=> $client->id,
            'sourceable_type' => 'client'
        ]);

        $result = Source::searchFilter('this is')->get();
        $this->assertEquals($source->id, $result[0]->id);
    }

    public function testSourceSourceable()
    {

        $user = factory(User::class)->create();
        $client = factory(Client::class)->create(['user_id' => $user->id]);
        $source = factory(Source::class)->create(['user_id' => $user->id, 'sourceable_id'=> $client->id, 'sourceable_type' => 'client']);

        $result = $source->sourceable;
        $this->assertEquals($result->id, $client->id);
    }

}