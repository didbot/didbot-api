<?php
namespace Didbot\DidbotApi\Test;

use \Laravel\Passport\Client;
use \Didbot\DidbotApi\Models\Did;
use \Didbot\DidbotApi\Models\Tag;
use \Didbot\DidbotApi\Models\Source;
use \Didbot\DidbotApi\Test\Models\User;

class TagsTest extends TestCase
{

    /**
     * @test
     */
     public function it_tests_the_get_tags_endpoint()
     {
         $user  = factory(User::class)->create();
         $tag   = factory(Tag::class)->create(['user_id' => $user->id]);
         $token = $user->createToken('Test Token')->accessToken;

         $this->get('/tags', [
             'Authorization' => 'Bearer ' . $token,
             'Accept' => 'application/json',
             'content-type' => 'application/json',
         ])->seeJson([
            'text' => $tag->text
         ]);

     }

    /**
     * @test
     */
    public function it_tests_the_post_tags_endpoint()
    {

        $user  = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;
        $text  = str_random(10);

        $this->postJson('/tags', ['text' => $text], [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
                'content-type'  => 'application/json',
        ])->seeStatusCode(200);

        $this->seeInDatabase('tags', ['user_id' => $user->id, 'text' => $text]);
    }

    /**
     * @test
     */
    public function it_tests_the_delete_tags_endpoint()
    {

        $user  = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;
        $client = factory(Client::class)->create(['user_id' => $user->id]);
        $source = factory(Source::class)->create(['user_id' => $user->id, 'sourceable_id'=> $client->id, 'sourceable_type' => 'client']);
        $did = factory(Did::class)->create(['user_id' => $user->id, 'source_id' => $source->id]);
        $tag = factory(Tag::class)->create(['user_id' => $user->id]);

        $did->tags()->attach([$tag->id]);

        $this->delete('/tags/' . $tag->id, [],
        [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'content-type' => 'application/json',
        ]);

        $this->dontSeeInDatabase('tags',['id' => $tag->id]);

        $this->dontSeeInDatabase('did_tag', ['tag_id' => $tag->id]);

    }

}