<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use App\Service\Manager\DescripcionDatosManager;

use App\Form\Type\DescripcionDatosPaso1FormType;
use App\Form\Type\DescripcionDatosPaso2FormType;
use App\Form\Type\DescripcionDatosPaso3FormType;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use App\Form\Model\DescripcionDatosDto;
use App\Entity\DescripcionDatos;
use DateTimeInterface;


use Ramsey\Uuid\Uuid;

class DescripcionDatosControllerTest extends WebTestCase
{


    private $client = null;
    private $descripcionDatosManager = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->descripcionDatosManager = self::$container->get('App\Services\Manager\DescripcionDatosManager');
        parent::setUp();
    }

    public function testindexAction()
    {   

        $this->logIn();
        $contains = "html:contains('Listado banco de datos')";

        $crawler = $this->client->request('GET', '/asistentecamposdatos',['pagina'=>0,'tamano'=>0]);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testGetAction()
    {      
        $this->logIn();

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $url = "/asistentecamposdatos/131";
        $crawler = $this->client->request('GET', '/asistentecamposdatos/131');
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());         
    }


    public function testInsertUpdatePasosAction()
    {      
        $this->logIn();
        $session = $this->client->getContainer()->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1, intancia2, Intancia3";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new DateTimeInterface($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new DateTimeInterface($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);
    
        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso2);

        
        $expected = new DescripcionDatos();

        $form = $this->factory->create(DescripcionDatosPaso1FormType::class, $descripcionDatosDto);

        $form->submit($descripcionDatos);
        
        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $formData was modified as expected when the form was submitted
        $this->assertEquals($expected, $descripcionDatosDto);

    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        // the firewall context (defaults to the firewall name)
        $firewall = 'secured_area';

        $token = new UsernamePasswordToken('servidotnet@gmail.com', null, $firewall, array('ROLE_USER'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}