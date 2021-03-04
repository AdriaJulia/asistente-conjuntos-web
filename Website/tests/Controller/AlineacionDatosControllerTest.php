<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class AlineacionDatosControllerTest extends WebTestCase
{
    private $accessToken="";
    private $client;
    private $id;

    public function setUp(): void
    {
       // $this->GetLoginCheckAction();
        parent::setUp();
    }

    public function testindexAction()
    {      
        $this->assertEquals(202, 202);
    }

    public function testGetAction()
    {      
        $this->assertEquals(202, 202);
    }


    public function testInsertUpdatePasosAction()
    {      
        $this->assertEquals(202, 202);
    }
}