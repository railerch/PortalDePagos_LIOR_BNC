# PORTAL DE PAGOS LIOR/BNC
Portal de pagos de tipo POS del BNC para Lior Cosmetics, C.A.

# CON RESPECTO A GIT
* El proyecto tiene dos ramas, una es la rama principal (_main_) y la otra de la de desarrollo (_dev_)
* No se puede hacer push directo a la rama principal
* Las actualizaciones hacia la rama principal se hacen via _Pull-request_ luego de actualizar la rama de desarrollo

# MECANICA DE TRABAJO ENTRE HOSTING Y REPO GIT
En el hosting hay tres carpetas:
* dev
* qa
* Produccion

La mecanica de actualizacion es la siguiente:
* Se trabaja en local en la carpeta del proyecto ubicada en el 10.80.35.25
* Esta esta conectada al repo que posee tres ramas: main, dev y qa
* Los cambios se suben al hosting desde dicha carpeta via FTP a la carpeta que corresponda segun la rama activa

# CHANGELOG 1.0.0
* Sistema de autenticacion que cosnta de dos fases:
    -   Autenticacion contra el banco
    -   Autenticacion de usuario en el portal
* Los usuarios pueden registrarse para hacer pagos
* Se puede recuperar la contraseña en caso de olvido
* El portal es una PWA con botones de instalacion presentes en el Login y panel lateral de la pantalla principal
* Pagos a traves de tarjeta de debito UNICAMENTE del BNC
* Pagos via P2P
* Reporte de pagos realizados desde otros bancos via transferencia o pago movil
* Envio de email al usuario al realizar un pago con los detalles del mismo
* Historial de pagos con opciones para exportar en CSV o EXCEL
* Visualizacion de tasa de cambio BCV (Bs. => USD) en pantalla principal
* Validacion de tiempo de sesion para la renovacion del token de seguridad (5 min)
* Carga de abonos individuales o mediante un archivo con multiples registros
* Visualizacion de registros de pago y logs de actividad mediante una sesion de administrador
