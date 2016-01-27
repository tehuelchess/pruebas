## Contribuir a SIMPLE
Damos las gracias y bienvenidas sean todas las correcciones y mejoras en el desarrollo del código de SIMPLE, pruebas y documentación. 
Esta guía detalla como contribuir en el proyecto de una manera que sea eficiente para toda la comunidad.


1. [Prerequisitos](#prerequisitos)
2. [Preparación de ambiente local](#preparación-de-ambiente-local)
3. [Detección y solución de issues](#detección-y-solución-de-issues)
4. [Detección de issues](#detección-de-issues)
  - [Solución de issues](#solución-de-issues)
  - [Agregar nuevas características](#agregar-nuevas-características)
5. [Actualizar mi repositorio local](#actualizar-mi-repositorio-local)
6. [¿Dudas, consultas, información?](#dudas-consultas-información)


### Prerequisitos 

1. [Leer el Manual de SIMPLE](/docs/SIMPLE.docx)

2. Tener clara la normalización de código fuente

3. Documentar y comentarizar correcciones y/o nuevos desarrollos.

[Volver](#contribuir-a-simple)


### Preparación de ambiente local

1. Realizar Fork del repositorio https://github.com/e-gob/SIMPLE a tu cuenta GitHub

2. Clonar el proyecto forkeado desde tu cuenta GitHub. Ejemplo:
   ```console
   https://github.com/nombreusuario/SIMPLE.git
   ```
3. Agregar el repositorio padre como origen remoto. Ejemplo:
   ```console
   git remote add upstream https://github.com/e-gob/SIMPLE.git
   ```

4. Luego de clonado el proyecto, crear la rama beta para empezar a trabajar. Ejemplo:
   ```console
   git checkout -b beta
   ```

5. Realizar los commits con una descripción sobre la o las nuevas  funcionalidades
   ```console
   git commit -m “ Modifico funcionalidad manager”
   ```

6. Generar un push hacia la rama beta del proyecto forkeado. Ejemplo:
   ```console
   git push origin beta
   ```

7. Generar un pull request hacia la rama beta del proyecto origen por la interfaz de Github

[Volver](#contribuir-a-simple)

### Detección y solución de issues

#### Detección de issues 
Antes de ingresar la incidencia, se recomienda buscar dentro del tracker de issues entradas similares 
para verificar que la incidencia ya haya sido reportada o resuelta.

Para ingresar un issue, favor usar la siguiente plantilla en el área de descripción:

1. Resumen
(Resumir el issue en una sentencia. Cual es el error o que esperas que suceda)

2. Pasos para reproducir
(Como se reproduce el issue)

3. Comportamiento esperado
(Lo que debe ver en su lugar)

4. En lo posible, capturas de pantalla relevantes
(Capturas de pantalla que ayuden a reproducir el error)

[Volver](#contribuir-a-simple)

#### Solución de issues
Lo principal es primero cumplir con los prerequisitos, luego :

1. Comentar el Issue y detallar la solución que se entregará.

2. Realizar los commits con una descripción sobre la o las correcciones realizadas"
 ```console
   git commit -m "Modifico funcionalidad manager"
   ```
3. Generar un push hacia la rama beta del proyecto forkeado. Ejemplo:
 ```console
   git push origin beta
   ```
4. Generar un pull request hacia la rama beta del proyecto origen por la interfaz de Github  

[Volver](#contribuir-a-simple)

### Agregar nuevas características
Para agregar nuevas caracteristicas, es necesario primero cumplir con los prerequisitos

1. Abrir un ISSUE, indicando cual es la caracteristica, funcionalidad o complemento que falta en SIMPLE

2. Comentar el Issue, agregar la solución que se entregara.

3. Realizar los commits con una descripción sobre la o las nuevas funcionalidades:
 ```console
   git commit -m "Modifico funcionalidad manager"
   ```
4. Generar un push hacia la rama beta del proyecto forkeado. Ejemplo:
 ```console
   git push origin beta
   ```
5. Generar un pull request hacia la rama beta del proyecto origen por la interfaz de Github 

[Volver](#contribuir-a-simple)

### Actualizar mi repositorio local
 Si queremos actualizar nuestro repositorio con los cambios del repositorio padre SIMPLE debemos seguir los siguientes pasos
 ```console
   git fetch upstream
   
   git merge upstream/master
   ```
   Donde master indica la rama del repositorio padre a actualizar

[Volver](#contribuir-a-simple)

### ¿Dudas, consultas, información?
contáctese con nosotros al correo simple@minsegpres.gob.cl

[Volver](#contribuir-a-simple)
