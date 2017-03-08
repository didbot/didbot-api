<?php
namespace Didbot\DidbotApi\Test;

use \Laravel\Passport\Client;
use \Didbot\DidbotApi\Test\Models\User;

class AuthTest extends TestCase
{

    /**
     * @test
     */
     public function it_tests_a_422_error_is_thrown_without_xmlhttprequest()
     {

         $this->serverVariables = [
             'HTTP_X_REQUESTED_WITH'=> 'NotXMLHttpRequest'
         ];

         $user  = factory(User::class)->create();
         $token = $user->createToken('Test Token')->accessToken;

         $this->get('/tags', [
             'Authorization' => 'Bearer ' . $token,
             'Accept' => 'application/json',
             'content-type' => 'application/json',
         ])->seeStatusCode(422);

     }

}