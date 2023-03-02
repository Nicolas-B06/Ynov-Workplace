<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;

class UserTest extends ApiTestCase
{
  use RecreateDatabaseTrait;
  public function testGetUser()
  {
    static::createClient()->request('GET', '/api/users');
    $this->assertResponseIsSuccessful();
  }
  
  public function testPostUser() {
    static::createClient()->request('POST', '/api/users', ['json' => [
      'nickname' => 'Nick Name',
      'email' => 'email@email.e',
      'plainPassword' => 'p455w0rD'
    ]]);
    
    $this->assertResponseStatusCodeSame(201);
    $this->assertJsonContains([
      'id' => 11,
      'nickname' => 'Nick Name',
      'email' => 'email@email.e',
    ]);
  }
}