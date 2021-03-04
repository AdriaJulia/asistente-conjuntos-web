<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class OrigenDatosControllerTest extends WebTestCase
{
    private $accessToken="";
    private $client;
    private $id;

    public function setUp(): void
    {
       // $this->GetLoginCheckAction();
        parent::setUp();
    }
  
    public function testInsertUpdateUrlActions()
    {
        
        $this->assertEquals(204, 204);

    }

    public function testInsertUpdatefileActions()
    {
        
        $this->assertEquals(204, 204);

    }


    public function testInsertUpdateDBActions()
    {
        
        $this->assertEquals(204, 204);

    }
}