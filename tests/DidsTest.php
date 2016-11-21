<?php
namespace Didbot\DidbotApi\Test;

use \Laravel\Passport\Token;
use \Didbot\DidbotApi\Models\Did;
use \Didbot\DidbotApi\Test\Models\User;

class GetDidsTest extends TestCase
{
    /**
     * @test
     */
    public function it_tests_all_endpont_require_auth()
    {
        $did = factory(Did::class)->create(['user_id' => 1]);

        $this->get('/dids', [
                'Authorization' => 'Bearer 123',
                'Accept'        => 'application/json',
                'content-type'  => 'application/json',
        ])
        ->see('Unauthorized')->seeStatusCode(401);

        $this->postJson('/dids', ['text'=>'test'], [
                'Authorization' => 'Bearer 123',
                'Accept'        => 'application/json',
                'content-type'  => 'application/json',
        ])
        ->see('Unauthorized')->seeStatusCode(401);

        $this->delete('/dids/' .  $did->id, [], [
                'Authorization' => 'Bearer 123',
                'Accept'        => 'application/json',
                'content-type'  => 'application/json',
        ])
        ->see('Unauthorized')->seeStatusCode(401);

    }


    /**
     * @test
     */
    public function it_tests_the_post_dids_endpoint()
    {

        $user  = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;
        $text  = str_random(10);

        $this->postJson('/dids', ['text' => $text], [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
                'content-type'  => 'application/json',
        ])->seeStatusCode(200);

        $this->get('/dids', [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'content-type' => 'application/json',
        ])->seeJson([
                'text' => $text
        ]);

    }

    /**
     * @test
     */
    public function it_tests_the_delete_dids_endpoint()
    {

        $user  = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;
        $did = factory(Did::class)->create(['user_id' => 1]);

        $this->delete('/dids/' . $did->id, [],
        [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'content-type' => 'application/json',
        ]);

        $this->dontSeeInDatabase('dids',['id' => $did->id]);

    }

    /**
     * @test
     */
    public function it_tests_user_can_only_see_their_dids()
    {

        $user         = factory(User::class)->create();
        $token        = $user->createToken('Test Token')->accessToken;
        $user_did     = factory(Did::class)->create(['user_id' => $user->id]);
        $not_user_did = factory(Did::class)->create(['user_id' => ($user->id + 1)]);

        $this->get('/dids', [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'content-type' => 'application/json',
        ])->seeJson([
                'text' => $user_did->text
        ])->dontSeeJson([
                'text' => $not_user_did->text
        ]);

    }
}