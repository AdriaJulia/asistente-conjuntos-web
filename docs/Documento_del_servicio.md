# Documento de servicio de la WEB del asistente de creación de conjuntos de datos

1.  [Descripción general del servicio](#1-descripción-general-del-proyecto)

2.  [Tecnologías](#2-tecnologías)

3.  [Componentes del servicio](#3-componentes-del-servicio)

4.  [Guía de despliegue y pruebas](#4-guía-de-despliegue-y-pruebas)

5.  [Guía de mantenimiento](#5-guía-de-mantenimiento)


## 1. Descripción general del servicio

La WEB del asistente de creación de conjuntos de datos es la parte FRONT del servicio y principalmente ofrece acceso a un asistente que permite a cualquier usuario del Gobierno de Aragón, la incorporación de un nuevo conjuntos de datos, la adhesión de una nueva distribución en un conjunto de datos ya existente o la actualización de algún conjunto incorporado anteriormente en Aragón Open Data. 

Por otra parte, también da servicio a los administradores mediante un flujo de gestión, que permite la validación del traspaso de cada distribución de un conjunto de datos al Banco de datos y/o a Virtuoso.

Los usuarios acceden al servicio mediante su identificación a través del LDAP del Gobierno de Aragón y la persistencia de los datos, además del traspaso al Banco de datos, se realiza mediante un API ubicado también en este proyecto.

## 2. Tecnologías

Las tecnologías usadas en el proyecto y sus versiones son las sisguientes:

- PHP -> 7.46
- PostgreSQL -> 9.6.12

## 3. Componentes del servicio

![](docs/images/asistente_diagrama_arquitectura.png)

La ejecución principal del asistente se realiza a través de la aplicación **WEB** donde el usuario debe realizar login contra el **LDAP** del Gobierno de Aragón. El usuario genera un conjunto de datos siguiendo el asistente y guarda toda la información en la base de datos interna **PostgreSQL** a través del **API**.

Los datos del conjunto de datos generado pueden tener su origen en un fichero ubicado en un **Repositorio externo**, una **Base de datos Externa** o un Fichero subido directamente a la aplicación para finalmente ubicarse en un **Repositorio interno**. Si los datos provienen de una base de datos, se utiliza el API de **GA_OD_CORE** para recuperar los datos.

Una vez generado el conjunto de datos y validado por parte de los administradores, se traslada al Banco de datos mediante el API de **CKAN** y si el conjunto de datos se encuentra alineado con el modelo ontológico EI2A V2, se traslada a Virtuoso mediante el API de **AOD POOL V2**.

## 4. Guía de despliegue y pruebas

El procedimiento de despliegue automatizado mediante ANSIBLE se encuentra en desarrollo. Provisionalmente, la información de instalación y configuración se encuentra en el repositorio central de infraestructura semántica [https://github.com/aragonopendata/infraestructura-semantica#454-procedimiento-de-despliegue](https://github.com/aragonopendata/infraestructura-semantica#454-procedimiento-de-despliegue)

En relación al proceso de pruebas, los tipos de pruebas que se realizan son los siguientes:
- Pruebas unitarias
- Pruebas de aceptación
- Pruebas de integración
- Pruebas de rendimiento

Para que funcione correctamente la ejecución automatizada de las pruebas unitarias de la WEB, hay que quitar la autenticación en localhost (una vez terminadas, hay que restaurarla). Para ello, en el archivo config/packages/security.yaml hay que añadir (cambiando las IPs localhost correctas en vez de 192.168.0.1/24):
```
{ path: ^/asistentecamposdatos, roles: IS_AUTHENTICATED_ANONYMOUSLY, ips: [127.0.0.1, ::1, 192.168.0.1/24] }
```

Quedaría así:
```
access_control:
	- { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
	- { path: ^/logout, roles: IS_AUTHENTICATED_ANONYMOUSLY }
	- { path: ^/asistentecamposdatos, roles: ROLE_USER }
	- { path: ^/asistentecamposdatos, roles: IS_AUTHENTICATED_ANONYMOUSLY, ips: [127.0.0.1, ::1, 192.168.0.1/24] } 
```

Para instalar o actualizar la herramienta que lanza los test PHPUnit, hay que ejecutar solo una vez, en la carpeta raíz de cada proyecto, el siguiente comando: 
> composer require --dev symfony/phpunit-bridge

El comando para ejecutar todas las pruebas es: 
> php bin/phpunit

Si se desea ejecutar las pruebas de un Controller:
> php bin/phpunit tests/Util

Y para ejecutar la prueba de un archivo hay que ejecutar:
> php bin/phpunit tests/Util/CalculatorTest.php

Es posible seguir su trazabilidad en:
```/var/log/log.test```


En relación a las pruebas de aceptación, integración y rendimiento, su ejecución es manual y se realiza cumplimentando el siguiente fichero Excel, donde se pueden encontrar todas las pruebas a realizar divididas en 3 hojas.

El fichero con la plantilla del plan de pruebas se encuentra aquí: [plantilla_plan_de_pruebas.xlsx](plantilla_plan_de_pruebas.xlsx)

## 5. Guía de mantenimiento

El procedimiento de despliegue automatizado mediante ANSIBLE se encuentra en desarrollo. Una vez se haya finalizado, se actualizará esta información.