# asistente-conjuntos-web

Descripción del proyecto
===================================================================================================
Website es la parte front del servicio asistente de carga datos del Gobierno de Argón
Website realiza la persistencia de los datos a través de una apirest en un proyecto separado.
Wenbite realiza las tareas de:
 Dar una interfaz web al servicio
 Identificar a los usuarios por medio del LDAP
 Dar la funcionalidad de negocio tanto a los usuarios como a los administradores
 

Estructura del proyecto
====================================================================================================

APIRest
├── bin/
│   └── console
├── config/
│   ├── packages <------------ configuración de los paquetes
│   ├── routes <-------------- configuración de las rutas
│   ├── validator <----------- configuración de la validación de los DTO 
│   ├── autoload.php
│   ├── AppKernel.php
├── public <------------------ Carpeta raíz para el navegador web
├── src/ <-------------------- Carpeta del código realizado 
│   ├── Controllers <--------- Controladores rest
│   ├── DataFixtures
│   ├── Entity <-------------- Entidades de la aplicación enlazas por el ORM
│   ├── Enum  <--------------- Enumerados estáticos
│   ├── Form  <--------------- Definición de los formularios
│   │    └── Model <---------- Definición del modelo DTO para la entidad
│   │    └── Type <----------- Definición del formulario basado en el modelo DTO
│   ├── Repository <---------- Clases repositorio del ORM 
│   ├── EventSubscriber <----- Utilidad para capturar response y modificarlo
│   ├── Security <------------ Refactorizacion del componente LDAP, para adaptarlo a las necesidades del proyecto
│   ├── Service <------------  Encapsulación por servicio Manager/Procesor del negocio. También están aquí las utilidades de negocio
│   │   └── Manager <--------  Gestores repositorios de las entidades, su hace falta llaman al repositorio de ORM
│   │   └── Processor <------  Clases que ejecutan el proceso del controlador
├── Templates <--------------- Plantillas Twig (en está caso para los correos)       
├── test/ <------------------- Unitarias
├── var/
│   ├── cache <--------------- Cache symfony
│   └── logs  <--------------- Logs symfony
├── vendor/ <----------------- Carpeta con los distintos paquetes descargados  
│ 
└── .env  <----configuración


Exposición de la estructura
==========================================================================================================
El servicio rest tiene una arquitectura paralela a las funcionalidades del la aplicación
Las funcionalidades son Asistente para la carga de datos Paso 1,2,3 , Ficha conjunto datos (con workflow) y Listado Conjunto datos.
La arquitectura se define por:  
	Un formulario Form/Type (en este caso conjunto de campos web al que el usuario accede y/o informa y envía), es capturado por una función del controlador correspondiente.
	El controlador lanza un proceso (Processor), al que le envía el DTO, y Request y el Manager de la entidad
	Con el request generamos el formulario del Form/Type para validarlo
	Si es correcto se instancia la entidad (Entity)  que se persiste a través de la utilidad Apirest la cual realiza y recoge la solicitud json a la apirest.

El paso2 , carga de los orígenes de datos se distingue en tres soportes (base datos, fichero y url) y cuatro formatos para fichero y URL (xml, xls, csv y json)
Para cada uno de los casos de uso de realizan distintas clases al mismo nivel de la arquitectura adaptando los requisitos  

De esta forma se encontrará en el código la misma estructura de clases para cada una de las funcionalidades.

Las validaciones se realizan por configuración en la carpeta /Config/Validator  y en el propio formulario src/Form/Type por código PHP
Esta aplicación basa su seguridad de autenticación/identificación LDAP.
Para las solicitudes a la apirest con seguridad JWT, se ha desarrollado un sistema que genera el usuario y contraseña de solicitud del los token JWT, basado en los datos del LDAP y trasparente para el usuario, 


