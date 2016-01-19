
## Contribuir a SIMPLE
Damos las gracias y bienvenidas sean todas las correcciones y mejoras en el desarrollo del código de SIMPLE, pruebas y documentación. 
Esta guía detalla como contribuir en el proyecto de una manera que sea eficiente para toda la comunidad.

### Contribuir a la comunidad
<ol>
<li> Realizar Fork del repositorio https://github.com/e-gob/SIMPLE a tu cuenta GitHub</li>

<li> Clonar el proyecto forkeado desde tu cuenta GitHub. Ejemplo: </li>
   <code>
   https://github.com/nombreusuario/SIMPLE.git
   </code>

<li> Agregar el repositorio padre como origen remoto. Ejemplo:</li>
   <code>
   git remote add upstream https://github.com/e-gob/SIMPLE.git
   </code>

<li> Luego de clonado el proyecto, crear la rama beta para empezar a trabajar. Ejemplo:</li>
   <code>
   git checkout -b beta
   </code>

<li> Realizar los commits con una descripción sobre la o las nuevas  funcionalidades </li>
	<code>
	git commit -m “ Modifico funcionalidad manager”
	</code>

<li> Generar un push hacia la rama beta del proyecto forkeado. Ejemplo:</li>
   <code>
   git push origin beta
   </code>

<li> Generar un pull request hacia la rama beta del proyecto origen por la interfaz de Github</li>

<li> Si queremos actualizar nuestro repositorio con los cambios en el repositorio padre </li>
   <code>
   git fetch upstream
   </code><br>
   <code>
   git merge upstream/master
   </code><br>
   Donde master indica la rama del repositorio padre a actualizar

</ol>

### Seguimiento de incidencias (issues)
Antes de ingresar la incidencia, se recomienda buscar dentro del tracker de issues entradas similares 
antes de ingresar el propio para verificar que la incidencia ya fue reportada o resuelta.

Para ingresar un issue, favor usar la siguiente plantilla en el área de descripción:

<ol>
<li> Resumen</li>
(Resumir el issue en una sentencia. Cual es el error o que esperas que suceda)

<li> Pasos para reproducir</li>
(Como se reproduce el issue)

<li> Comportamiento esperado</li>
(Lo que debe ver en su lugar)

<li> En lo posible, capturas de pantalla relevantes</li>
(Capturas de pantalla que ayuden a reproducir el error)
</ol>

### Agregar nuevas características
Para agregar nuevas caracteristicas, es necesario primero cumplir con los siguientes requisitos
<ol>
<li> Leer el Manual de SIMPLE</li>

<li> Tener clara la normalización de código fuente</li>

<li> Documentar y comentarizar el nuevo desarrollo</li>

<li> Crear un fork del proyecto SIMPLE desde tu cuenta GitHub. Ejemplo:</li>
   <code>
   https://github.com/nombreusuario/SIMPLE.git
   </code>
<li> Agregar el repositorio padre como origen remoto. Ejemplo:</li>
   <code>
   git remote add upstream https://github.com/e-gob/SIMPLE.git
   </code>
<li> Luego de clonado el proyecto, crear la rama beta para empezar a trabajar. Ejemplo:</li>
   <code>
   git checkout -b beta
   </code>
</ol>
Una vez que se cumplan los pre requisitos,
<ol>
<li> Abrir un ISSUE, indicando cual es la caracteristica, funcionalidad o complemento que falta en SIMPLE</li>

<li> Comentar el Issue, agregar la solución que se entregara.</li>

<li> Realizar los commits con una descripción sobre la o las nuevas funcionalidades"</li>
   <code>
   git commit -m "Modifico funcionalidad manager"
   </code>
<li> Generar un push hacia la rama beta del proyecto forkeado. Ejemplo:</li>
   <code>
   git push origin beta
   </code>
</ol>

### ¿Dudas sobre el código?
Favor ante cualquier duda contactarse al correo simple@minsegpres.gob.cl



