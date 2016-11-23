<?php
namespace Didbot\DidbotApi\Test;

use \Laravel\Passport\Token;
use \Didbot\DidbotApi\Models\Did;
use \Didbot\DidbotApi\Models\Tag;
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

        $tag1  = factory(Tag::class)->create();
        $tag2  = factory(Tag::class)->create();

        $text = str_random(10);

        $this->postJson('/dids', [
                'text' => $text,
                'tags' => [$tag1->id, $tag2->id]
        ], [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
                'content-type'  => 'application/json',
        ])->seeStatusCode(200);

        $this->seeInDatabase('dids', ['user_id'=>1, 'text'=>$text]);
        $did = Did::where('text', $text)->firstOrFail();
        $this->seeInDatabase('did_tag', ['tag_id'=>$tag1->id, 'did_id'=>$did->id]);
        $this->seeInDatabase('did_tag', ['tag_id'=>$tag2->id, 'did_id'=>$did->id]);

    }

    /**
     * @test
     */
    public function it_tests_the_delete_dids_endpoint()
    {

        $user   = factory(User::class)->create();
        $token  = $user->createToken('Test Token')->accessToken;
        $did    = factory(Did::class)->create(['user_id' => 1]);
        $tag    = factory(Tag::class)->create(['user_id' => 1]);

        $did->tags()->attach([$tag->id]);

        $this->delete('/dids/' . $did->id, [],
        [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'content-type' => 'application/json',
        ]);


        $this->dontSeeInDatabase('dids',['id' => $did->id]);

        // Verify and did_tag relations were also deleted
        $this->dontSeeInDatabase('did_tag',['did_id' => $did->id]);

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
        $tag          = factory(Tag::class)->create();
        $user_did->tags()->attach([$tag->id]);

        $this->get('/dids', [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'content-type' => 'application/json',
        ])->seeJson([
                'text' => $user_did->text,
        ])->seeJson([
                'text' => $tag->text
        ])->dontSeeJson([
                'text' => $not_user_did->text
        ]);

    }

    /**
     * @test
     */
    public function it_tests_dids_cursor()
    {

        $user = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;
        $dids = factory(Did::class, 50)->create(['user_id' => $user->id]);

        $response = $this->get('/dids',
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'content-type' => 'application/json',
            ])
            ->seeJson(['text' => $dids[49]->text])
            ->seeJson(['text' => $dids[30]->text])
            ->dontSeeJson(['text' => $dids[29]->text])
            ->seeJsonContains([
                'cursor' => [
                   'count' => 20,
                   'current' => null,
                   'next' => 31,
                   'prev' => null
            ]])->decodeResponseJson();

        $response = $this->get('/dids?cursor=' . $response['meta']['cursor']['next'],
                [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                        'content-type' => 'application/json',
                ])
             ->seeJson(['text' => $dids[29]->text])
             ->seeJson(['text' => $dids[10]->text])
             ->dontSeeJson(['text' => $dids[9]->text])
             ->seeJsonContains([
                     'cursor' => [
                             'count' => 20,
                             'current' => 31,
                             'next' => 11,
                             'prev' => null
                     ]
             ])
             ->decodeResponseJson();

        $this->get('/dids?'
                .'cursor=' . $response['meta']['cursor']['next']
                .'&prev='   . $response['meta']['cursor']['current'],
                [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                        'content-type' => 'application/json',
                ])
             ->seeJson(['text' => $dids[9]->text])
             ->seeJson(['text' => $dids[0]->text])
             ->dontSeeJson(['text' => $dids[10]->text])
             ->seeJsonContains([
                     'cursor' => [
                             'count' => 10,
                             'current' => 11,
                             'next' => 1,
                             'prev' => 31
                     ]
             ])
             ->decodeResponseJson();
    }
}