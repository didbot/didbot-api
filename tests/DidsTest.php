<?php
namespace Didbot\DidbotApi\Test;

use Didbot\DidbotApi\Models\Did;
use Didbot\DidbotApi\Models\Tag;
use Didbot\DidbotApi\Test\Models\User;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;
use Webpatser\Uuid\Uuid;

class DidsTest extends TestCase
{

    /**
     * @test
     */
    public function it_tests_the_get_dids_endpoint()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;
        $client = factory(Client::class)->create(['user_id' => $user->id]);
        $did = factory(Did::class)->create(['user_id' => $user->id, 'client_id'=> $client->id]);
        $tag = factory(Tag::class)->create(['user_id' => $user->id]);
        $did->tags()->attach([$tag->id]);

        $this->get('/dids', ['Authorization' => 'Bearer ' . $token ])
        ->seeJsonEquals([
            'data' => [
                0 => [
                    'id' => $did->id,
                    'text' => $did->text,
                    'tags' => [
                        'data' => [
                            0 => [
                                'id' => $tag->id,
                                'text' => $tag->text,
                            ],
                        ],
                    ],
                    'client' => [
                        'data' => [
                            'id' => $client->id,
                            'name' => $client->name,
                        ],
                    ],
                    'created_at' => $did->created_at->toIso8601String()
                ],
            ],
            'meta' => [
                'cursor' => [
                    'current' => NULL,
                    'prev' => NULL,
                    'next' => NULL,
                    'count' => 1,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_tests_the_get_dids_endpoint_by_tag_id()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;
        $client = factory(Client::class)->create(['user_id' => $user->id]);

        $did1 = factory(Did::class)->create(['user_id' => $user->id, 'client_id'=> $client->id]);
        $did2 = factory(Did::class)->create(['user_id' => $user->id, 'client_id'=> $client->id]);
        $did3 = factory(Did::class)->create(['user_id' => $user2->id, 'client_id'=> $client->id]);

        $tag1 = factory(Tag::class)->create(['user_id' => $user->id]);
        $tag2 = factory(Tag::class)->create(['user_id' => $user->id]);

        $did1->tags()->attach([$tag1->id]);
        $did2->tags()->attach([$tag2->id]);
        $did3->tags()->attach([$tag1->id]);

        $this->get('/dids?tag_id=' . $tag1->id, ['Authorization' => 'Bearer ' . $token])
        ->seeJson([
                'text' => $did1->text
        ])->dontSeeJson([
                'text' => $did2->text
        ])->dontSeeJson([
                'text' => $did3->text
        ]);
    }

    /**
     * @test
     */
    public function it_tests_the_get_dids_endpoint_by_client_id()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $client1 = factory(Client::class)->create(['user_id' => $user->id]);
        $client2 = factory(Client::class)->create(['user_id' => $user->id]);

        $did1 = factory(Did::class)->create(['user_id' => $user->id, 'client_id' => $client1->id]);
        $did2 = factory(Did::class)->create(['user_id' => $user->id, 'client_id' => $client2->id]);

        $this->get('/dids?client_id=' . $client1->id)
            ->seeJson([
                'text' => $did1->text
            ])->dontSeeJson([
                'text' => $did2->text
            ]);
    }

    /**
     * @test
     */
    public function it_tests_all_endpont_require_auth()
    {
        $user = factory(User::class)->create();
        $client = factory(Client::class)->create(['user_id' => $user->id]);
        $did = factory(Did::class)->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $response = $this->call('GET','/dids', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . str_random(232)
        ]);
        $this->assertEquals(401, $response->getStatusCode());

        $response = $this->call('POST', '/dids', ['text'=>'test'], [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . str_random(232)
            ]);
        $this->assertEquals(401, $response->getStatusCode());

        $response = $this->call('DELETE', '/dids/' .  $did->id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . str_random(232)
            ]);
        $this->assertEquals(401, $response->getStatusCode());
    }


    /**
     * @test
     */
    public function it_tests_the_post_dids_endpoint()
    {

        $user  = factory(User::class)->create();
        $token = $user->createToken('Test Token')->accessToken;

        $tag1  = factory(Tag::class)->create(['user_id' => $user->id]);
        $tag2  = factory(Tag::class)->create(['user_id' => $user->id]);

        $text = str_random(10);

        $this->postJson('/dids', [
                'text' => $text,
                'tags' => [$tag1->id, $tag2->id]
        ], ['Authorization' => 'Bearer ' . $token])->seeStatusCode(200);

        $this->seeInDatabase('dids', ['user_id'=>$user->id, 'text'=>$text]);
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
        $client = factory(Client::class)->create(['user_id' => $user->id]);
        $token  = $user->createToken('Test Token')->accessToken;
        $did    = factory(Did::class)->create(['user_id' => $user->id, 'client_id'=> $client->id]);
        $tag    = factory(Tag::class)->create(['user_id' => $user->id]);

        $did->tags()->attach([$tag->id]);

        $this->delete('/dids/' . $did->id, [], ['Authorization' => 'Bearer ' . $token]);


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
        $user2        = factory(User::class)->create();
        $client       = factory(Client::class)->create(['user_id' => $user->id]);
        $token        = $user->createToken('Test Token')->accessToken;
        $user_did     = factory(Did::class)->create(['user_id' => $user->id, 'client_id'=> $client->id]);
        $not_user_did = factory(Did::class)->create(['user_id' => $user2->id, 'client_id'=> $client->id]);
        $tag          = factory(Tag::class)->create(['user_id' => $user->id]);
        $user_did->tags()->attach([$tag->id]);

        $this->get('/dids', ['Authorization' => 'Bearer ' . $token])->seeJson([
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

        $user   = factory(User::class)->create();
        $token  = $user->createToken('Test Token')->accessToken;
        $client = factory(Client::class)->create(['user_id' => $user->id]);
        $dids   = factory(Did::class, 50)->create(['user_id' => $user->id, 'client_id'=> $client->id]);

        $response = $this->get('/dids', ['Authorization' => 'Bearer ' . $token])
            ->seeJson(['text' => $dids[49]->text])
            ->seeJson(['text' => $dids[30]->text])
            ->dontSeeJson(['text' => $dids[29]->text])
            ->seeJsonContains([
                'cursor' => [
                   'count' => 20,
                   'current' => null,
                   'next' => $dids[30]->id,
                   'prev' => null
            ]])->decodeResponseJson();

        $response = $this->get('/dids?cursor=' . urlencode($response['meta']['cursor']['next']),
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
                             'current' => $dids[30]->id,
                             'next' => $dids[10]->id,
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
                             'current' => $dids[10]->id,
                             'next' => null,
                             'prev' => $dids[30]->id
                     ]
             ])
             ->decodeResponseJson();
    }
}