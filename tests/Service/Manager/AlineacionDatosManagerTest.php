<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use App\Form\Model\DescripcionDatosDto;
use App\Form\Model\OrigenDatosDto;
use App\Entity\DescripcionDatos;
use App\Entity\OrigenDatos;
use App\Entity\OrigenDatosDatos;
use App\Enum\ModoFormularioOrigenEnum;
use App\Enum\TipoBaseDatosEnum;
use App\Enum\TipoOrigenDatosEnum;
use App\Form\Model\AlineacionDatosDto;

use Ramsey\Uuid\Uuid;

class AlineacionDatosManagerTest extends WebTestCase
{

    private $client = null;
    private $descripcionDatosManager = null;

    public function setUp(): void
    { 
        $this->client = static::createClient();
        $this->descripcionDatosManager = self::$container->get('App\Service\Manager\DescripcionDatosManager');
        $this->origenDatosManager = self::$container->get('App\Service\Manager\OrigenDatosManager');
        $this->alineacionDatosManager = self::$container->get('App\Service\Manager\AlineacionDatosManager');
        parent::setUp();
    }

    public function testPaso3Action()
    {      
        $this->logIn();
        $session = self::$container->get('session');
        $descripcionDatos = new DescripcionDatos();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);

         //paso1,1
        $descripcionDatosDto->denominacion = "Denominación conjunto datos";
        $descripcionDatosDto->descripcion = "Descripcion conjunto datos";
        $descripcionDatosDto->territorio = "CM:La Ribagorza";
        $descripcionDatosDto->frecuenciaActulizacion = "Semestral";
        $descripcionDatosDto->fechaInicio = "2021-01-01";
        $descripcionDatosDto->fechaFin = "2021-01-31";
        $descripcionDatosDto->instancias = "Intacia1,intancia2,Intancia3";

        $uuidGenerator = Uuid::uuid4();
   
        $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
        $descripcionDatos->setIdentificacion($uuidGenerator->toString());
        $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
        $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
        
        $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);    
        $descripcionDatos->setFechaInicio(new \DateTime($descripcionDatosDto->fechaInicio));
        $descripcionDatos->setFechaFin(new \DateTime($descripcionDatosDto->fechaFin));
        $descripcionDatos->setInstancias($descripcionDatosDto->instancias);
    
        $username = $session->getName();
        $descripcionDatos->setUsuario($username);
        $descripcionDatos->setSesion($session->getId());
        $descripcionDatos->setEstado(EstadoDescripcionDatosEnum::BORRADOR);
        $descripcionDatos->setEstadoAlta(EstadoAltaDatosEnum::paso2);
        $descripcionDatos = $this->descripcionDatosManager->create($descripcionDatos, $session);  
        $descripcionDatos->updatedTimestamps();

        $id= $descripcionDatos->getId();
        $idexiste = !empty($id);

        $this->assertTrue($idexiste);
        $this->assertEquals($uuidGenerator, $descripcionDatos->getIdentificacion());

        $contains = "html:contains('Nombre del conjunto de Datos')";
        $crawler = $this->client->request('GET', "/asistentecamposdatos/$id");
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter($contains)->count());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode()); 
        

        $origenDatos = new OrigenDatos();
        $descripcionDatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);

        $descripcionDatosDto->idDescripcion = $id;
        $descripcionDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $descripcionDatosDto->url = "http://localhost:8080/storage/default/Libro1.json";
        $descripcionDatosDto->data = "";
        $descripcionDatosDto->tipoBaseDatos = "";
        $descripcionDatosDto->host = "";
        $descripcionDatosDto->puerto = "";
        $descripcionDatosDto->servicio = "";
        $descripcionDatosDto->esquema = "";
        $descripcionDatosDto->tabla = "";
        $descripcionDatosDto->usuarioDB = "";
        $descripcionDatosDto->contrasenaDB = "";
        $descripcionDatosDto->campos = "";
        
        $origenDatos->setIdDescripcion($descripcionDatosDto->idDescripcion);
        $origenDatos->setTipoOrigen($descripcionDatosDto->tipoOrigen);
        $origenDatos->setData($descripcionDatosDto->url);
        $origenDatos->setUsuario($username);
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        $origenDatos->setCampos("");
        [$origenDatos,$errorProceso] = $this->origenDatosManager->createData($origenDatos,$session); 
        $this->assertTrue(empty($errorProceso));
        $this->assertTrue(!empty($origenDatos->getCampos()));


        $alineacionDatosDto = AlineacionDatosDto::createFromAlineacionDatos($origenDatos);
        $alineacionDatosDto->lineacionEntidad = "http://opendata.aragon.es/def/ei2a/categorization#1";
        $alineacionDatosDto->alineacionRelaciones ='{"id":"http://opendata.aragon.es/def/ei2a/categorization#1","lastname":"http://opendata.aragon.es/def/ei2a/categorization#12"}';

        $origenDatos->setAlineacionEntidad($alineacionDatosDto->alineacionEntidad);
        $origenDatos->setAlineacionRelaciones(base64_encode($alineacionDatosDto->alineacionRelaciones));
        $origenDatos->setSesion($session->getId());
        $origenDatos->updatedTimestamps();
        [$origenDatos,$errorProceso] = $this->alineacionDatosManager->saveAlineacionDatosEntidad($origenDatos,$session);
        $this->assertTrue(empty($errorProceso));

        $this->descripcionDatosManager->delete($id, $session); 
    }


    
    private function logIn()
    {

        $session = self::$container->get('session');

        // somehow fetch the user (e.g. using the user repository)
        $user = "MOCKSESSID";

        $firewallName = 'secure_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken($user, null, $firewallName, ['ROLE_USER']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie); 
        
    }
}