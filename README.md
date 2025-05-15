# PORTAL DE PAGOS LIOR/BNC

Portal de pagos de tipo POS del BNC para Lior Cosmetics, C.A.

## CON RESPECTO A GIT

* El proyecto tiene dos ramas, una es la rama principal (_main_) y la otra de la de desarrollo (_dev_)
* No se puede hacer push directo a la rama principal
* Las actualizaciones hacia la rama principal se hacen via _Pull-request_ luego de actualizar la rama de desarrollo

## AL IMPLEMENTAR

Se debe remover la referencia '_dev' en los archivos:
* install-app.js
* service-worker.js
* config.json

## MECANICA DE TRABAJO ENTRE HOSTING Y REPO GIT

### En el hosting hay tres carpetas

* dev
* qa
* Produccion

### Mecanica de actualizacion

* Se trabaja en local en la carpeta del proyecto ubicada en el 10.80.35.25
* Esta esta conectada al repo que posee tres ramas: main, dev y qa
* Los cambios se suben al hosting desde dicha carpeta via FTP a la carpeta que corresponda segun la rama activa

## OBSERVACIONES IMPORTANTES

Sistema de autenticacion que cosnta de dos fases:

1. Autenticacion contra el banco
2. Autenticacion de usuario en el portal

* El portal permite crear cuentas de usuario para registrar pagos
* El formato de la clave para registrar una cuenta debe ser de 8 a 15 caracteres entre mayusculas, minusculas y numeros
* En el registro se validan correo y numero de cedula para la creacion de nuevas cuentas, lo que indica que estos datos no pueden estar en uso por cuentas ya registradas, por lo tanto un correo o numero de cedula son validos para una sola cuenta
* El usuario puede iniciar sesion con el correo o numero telefonico registrado para su cuenta
* El portal permite recuperar la contraseña en caso de olvido
* Al solicitar la recuperacion de contraseña el sistema elimina la anterior lo que impide al usuario iniciar sesion hasta que indique la nueva clave
* Los pagos con debito en la opcion VPOS solo son validos con tarjetas del BNC
* El sistema permite reportar pagos realizados desde otros bancos via transferencia o pago movil
* El portal envia un email al usuario despues realizar un pago con los detalles del mismo
* El historial de pagos tiene opciones para exportar en CSV o EXCEL
* El detalle de un pago se visualiza al hacer clic en un registro desde el historial de pagos
* Se muestra la tasa de cambio BCV (Bs. => USD) del dia en la pantalla principal
* El tiempo de la sesion es de 5 min, luego de este se le solicita al usuario que confirme si desea mantener la sesion activa
* Aparte de la carga de abonos individuales el sistema permite cargar multiples pagos a traves de un archivo previamente configurado con los pagos a realizar
* Las instrucciones para reportar pagos multiples se encuentra en la misma pantalla de reporte de pagos pagos
* Los registros de pagos de los usuarios y logs de actividad del sistema se pueden visualizar mediante una sesion especial de administrador
* La instalacion es compatible solo con SO Android
* Para evitar problemas de compatibilidad se recomienda el uso de una version actualizada de Google Chrome
* Si tiene fallas de conectividad el portal puede presentar problemas al mostrar las pantallas, registros de pago o incluso realizar pagos
