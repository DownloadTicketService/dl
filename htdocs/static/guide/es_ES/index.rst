Download ticket service
=======================

.. contents::


Breve guía paso a paso para subir ficheros
------------------------------------------

Primero, diríjase a https://dl.example.com/ y autentíquese usando su usuario y
contraseña.

Esta página debería ser mostrada:

.. image:: t-step-1.png

1) Cliquee en el botón bajo "Subir fichero" (llamado "Examinar" or "Escoja
   fichero") como se ve en la imagen y seleccione el fichero que necesita subir.

2) Click en "Subir" y espere mientras el fichero es subido como aquí se muestra:

.. image:: t-step-2.png

3) Click en "Enviar vía e-mail" para enviar un e-mail a alguien con el enlace
   al fichero que acaba de subir.

Por defecto, el receptor tiene una semana para descargar el fichero antes de
este sea eliminado automáticamente. Puede cambiar este comportamiento
configurando algunos parámetros antes de la subida.

Puede ver una lista de ficheros que ha subido y administrarlos cliqueando en el
botón "Listar tickets activos" a pie de página.


Breve guía paso a paso para recibir ficheros
--------------------------------------------

Primero, diríjase a https://dl.example.com/ y autentíquese usando su usuario y
contraseña.

Esta página debería ser mostrada:

.. image:: g-step-1.png

1) Click en el enlace "Nueva concesión" al pie de página para empezar una nueva
   concesión:

.. image:: g-step-2.png

2) Introduzca *su* dirección de e-mail.

3) Click en "Crear" para generar una concesión de subida:

.. image:: g-step-3.png

4) Click en "Enviar vía E-Mail" para enviar un e-mail a alguien con el enlace
   a quién le permitirá subir un fichero para usted.

El receptor simplemente necesitará seguir las instrucciones contenidas en el
propio enlace. Una vez este suba un fichero al servidor, usted recibirá un
e-mail que contendrá un enlace a el fichero que ha sido subido.


Parámetros Avanzados de subida
------------------------------

Antes de subir un fichero, puede personalizar cómo se realizará su descarga y
su limpieza configurando algunos parámetros "avanzados":

.. image:: t-advanced.png

* *Si quiere que su fichero nunca sea eliminado*, por favor, marque la casilla
  "Ticket permanente". Su fichero estará disponible hasta que usted lo elimine
  manualmente.

* *Si quiere ser notificado cada vez que alguien descargue el fichero* puede
  escribir una dirección de e-mail en el campo "Notificar vía e-mail". Recibirá
  notificaciones cada vez que el fichero sea correctamente descargado o
  eliminado del servidor. Esto es genial si desea una confirmación extra de que
  su e-mail actúa en consecuencia.

Expirar en un total de # horas:

  Introduzca el número máximo de horas que a un fichero subido le está
  permitido permanecer en el servidor. Pasado este período el fichero será
  eliminado del servidor independientemente de si fue descargado o no.

Expirar en # horas tras la última descarga:

  Introduzca el número de horas que a un fichero subido se le permite
  permanecer en el servidor tras haber sido descargado. Una nueva descarga
  alargará el tiempo de vida del ticket durante el número de horas
  especificado. Pasado este período sin actividad el fichero será eliminado del
  servidor.

  Esta funcionalidad, cuando es usada con un largo período (días o semanas),
  permite a los "hot" tickets permanecer vivos mientras están en uso y ser
  elimindos automáticamente cuando ya no son demandados.

  Cuando es usado en un período corto (24 horas o menos), permite eliminar el
  ticket tan pronto como sea descargado, permitiendo al receptor algo de margen
  para descargar el fichero más de una vez.

Expirar tras # descargas:

  Introduce el número total de veces que es permitido descargar un fichero
  subido. Alcanzado este número el fichero será eliminado del servidor. Útil si
  quiere asegurarse de que el fichero es descargado sólo una vez por una sola
  persona.

Si al menos uno de estos parámetros expira el fichero será eliminado. Puede
configurar cualquier parámetro a "0" para deshabilitar la condición.
