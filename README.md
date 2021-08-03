# WEB del asistente de creación de conjuntos de datos

1.  [Descripción del proyecto](#1-descripción-del-proyecto)

2.  [Estructura del proyecto](#2-estructura-del-proyecto)


## 1. Descripción del proyecto

La WEB del asistente de creación de conjuntos de datos es la parte FRONT del servicio y principalmente ofrece acceso a un asistente que permite a cualquier usuario del Gobierno de Aragón, la incorporación de un nuevo conjuntos de datos, la adhesión de una nueva distribución en un conjunto de datos ya existente o la actualización de algún conjunto incorporado anteriormente en Aragón Open Data. 

Por otra parte, también da servicio a los administradores mediante un flujo de gestión, que permite la validación del traspaso de cada distribución de un conjunto de datos al Banco de datos y/o a Virtuoso.

Los usuarios acceden al servicio mediante su identificación a través del LDAP del Gobierno de Aragón y la persistencia de los datos, además del traspaso al Banco de datos, se realiza mediante un API ubicado también en este proyecto.
 

## 2. Estructura del proyecto


```
WEB
├─bin/
│  └─ console
├─config/
│  ├─ packages <------------ Configuración de los paquetes
│  ├─ routes <-------------- Configuración de las rutas
│  ├─ validator <----------- Configuración de la validación de los DTO (Data Transfer Objects)
├─public <------------------ Carpeta raíz para el navegador web
├─src/ <-------------------- Carpeta raíz del código realizado 
│  ├─ Controller <---------- Controladores REST
│  ├─ Entity <-------------- Entidades de la aplicación enlazas por el ORM (Object Relational Mapping)
│  ├─ Enum  <--------------- Enumerados estáticos
│  ├─ EventSubscriber <----- Utilidad para capturar response y modificarlo
│  ├─ Form  <--------------- Definición de los formularios
│  │  ├─ Model <------------ Definición del modelo DTO para la entidad
│  │  └─ Type <------------- Definición del formulario basado en el modelo DTO
│  ├─ Security <------------ Refactorizacion del componente LDAP, para adaptarlo a la integración
│  ├─ Service <------------- Encapsulación por servicio Manager/Processor del negocio y utilidades de negocio
│  │  ├─ Manager <---------- Gestores repositorios de las entidades, si hace falta llaman al repositorio de ORM
│  │  └─ Processor <-------- Clases que ejecutan el proceso del controlador
├─templates <--------------- Plantillas Twig
├─test/ <------------------- Pruebas Unitarias
├─var/
│  ├─ cache <--------------- Caché Symfony
│  └─ logs  <--------------- Logs Symfony
├─vendor/ <----------------- Carpeta con los distintos paquetes descargados  
│ 
└─.env  <------------------- Configuración general
```


La aplicación tiene una arquitectura paralela a las funcionalidades de la aplicación que son las siguientes:
- Asistente para la creación en 4 pasos.
- Listado de conjunto de datos.
- Ficha de conjunto de datos y su workflow de gestión.
- Ayuda.

La arquitectura se define por:  
- Un formulario Form/Type con un conjunto de campos web, al que el usuario accede y/o informa y envía, que es capturado por una función del controlador correspondiente.
- El controlador lanza un proceso (Processor), al que le envía el DTO, Request y el Manager de la entidad.
- Con el Request se genera el formulario del Form/Type para validarlo.
- Si es correcto, se instancia la entidad (Entity) que se persiste a través de la API, la cual realiza y recoge la solicitud JSON con los datos necesarios.

En el paso 3, se escoge el origen de los datos que puede ser FICHERO, URL o BASE DE DATOS. Para las opciones de FICHERO y URL, es posible escoger o enlazar archivos de tipo CSV, XML, JSON, XLS o XLSX. Para cada una de estas posibilidades, se realizan distintas clases al mismo nivel de la arquitectura adaptando los requisitos.

De la misma forma, se encuentra en el código la misma estructura de clases para cada una de las funcionalidades. Las validaciones se realizan por configuración en la carpeta /Config/Validator y en el propio formulario src/Form/Type por código PHP.

Esta aplicación basa su seguridad en la autenticación/identificación LDAP. Para las solicitudes a la API con seguridad JWT, se ha desarrollado un sistema que genera el usuario y contraseña de solicitud de los token JWT, basado en los datos del LDAP y transparente para el usuario.

Las plantillas TWIG (templates) sirven para realizar la customización de las pantallas y visualizaciones disponibles en la WEB.

Finalmente, cabe destacar que los parámetros fundamentales del servicio se encuentran en el fichero .env situado en la raíz del proyecto y que las pruebas unitarias, se encuentran en la carpeta test.