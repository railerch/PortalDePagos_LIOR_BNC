<?php

session_start();

// Validar ID generado en el inicio de sesión

if (!$_SESSION["session_id"]) header("location: login.php");

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <?php require("head.php") ?>

    <script type="text/javascript">
        // Validar ID de sesión

        if (!sessionStorage.getItem("sessionID")) {

            window.location.replace("login.php");

            fetch("controller.php?cerrar-sesion=true");

        };
    </script>

    <title>Portal de pagos | Main</title>

</head>



<body>

    <header id="pagos-header" class="py-2">

        <div class="col-12 col-md-8">

            <img id="main-logo" src="assets/img/liorLogoB.png" alt="Lior Cosmetics logo">

            <h3 class="h1-G">Portal de pagos</h3>
        </div>

        <div class="col-12 col-md-4">

            <h4 class="m-0" id="nombre-cliente"></h4>

            <button id="cerrar-sesion-btn" class="btn btn-outline-light" title="Cerrar sesión"><i class="bi bi-power"></i></button>

        </div>

    </header>

    <!-- Tasa BCV -->
    <div id="tasa-bcv-badge" class="col-12 d-flex justify-content-between justify-content-md-center shadow align-items-center bg-info text-light p-2">
        <div>
            <b>Tasa BCV:</b> Bs. <span>0.00</span>
        </div>
        <button id="actualizar-tasa-btn" class="btn btn-sm btn-outline-light ms-5" title="Actualizar tasa"><i class="bi bi-arrow-counterclockwise"></i></button>
    </div>

    <div id="contenido">
        <aside id="menu-usuario" class="col-12 col-lg-2 gap-2">

            <div id="desktop-menu">

                <button class="asideBtn" id="aside-vpos-btn" data-btn="pagar"><i class="bi bi-credit-card-2-back"></i> Punto de venta Online</button>

                <button class="asideBtn" id="aside-c2p-btn" data-btn="pagar"><i class="bi bi-cash"></i> Pago móvil C2P</button>

                <!--button class="asideBtn" id="aside-p2p-btn" data-btn="pagar"><i class="bi bi-people-fill"></i> Pago móvil BNC</button-->

                <button class="asideBtn" id="aside-report-btn" data-btn="pagar"><i class="bi bi-bank"></i> Reporte de pagos otros bancos</button>

                <button class="asideBtn historial-op" id="aside-historial-btn" data-btn="historial"><i class="bi bi-list"></i> Historial

                    de pagos</button>

                <button class="asideBtn" id="install-app-btn" style="display:none">Instalar APP <i class="bi bi-download"></i></button>

            </div>

            <details id="mobile-menu" style="display:none">

                <summary>Menu principal</summary>

                <div class="text-center mt-2">

                    <button class="asideBtn" id="aside-vpos-btn" data-btn="pagar"><i class="bi bi-credit-card-2-back"></i> Punto de venta Online</button>

                    <button class="asideBtn" id="aside-c2p-btn" data-btn="pagar"><i class="bi bi-cash"></i> Pago móvil C2P</button>

                    <!--button class="asideBtn" id="aside-p2p-btn" data-btn="pagar"><i class="bi bi-people-fill"></i> Pago móvil BNC</button-->

                    <button class="asideBtn" id="aside-report-btn" data-btn="pagar"><i class="bi bi-bank"></i> Reporte de pagos otros bancos</button>

                    <button class="asideBtn historial-op" id="aside-historial-btn" data-btn="historial"><i class="bi bi-list"></i> Historial

                        de pagos</button>

                </div>

            </details>

        </aside>

        <main class="col-12 col-lg-10">

            <!-- PAGOS -->



            <!-- VPOS -->

            <div id="vpos-div">

                <h2 class="text-muted">Punto de venta virtual</h2>

                <hr>

                <h5 class="text-muted">Datos de pago</h5>

                <form id="vpos-frm">

                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <div class="input-group">

                            <label for="nomTarjeta" class="input-group-text col-4 col-sm-2 col-lg-3">Titular</label>

                            <input type="text" class="form-control" id="nomTarjeta" placeholder="Nombre completo">

                        </div>



                        <div class="input-group">

                            <label for="identificacion" class="input-group-text col-4 col-sm-2 col-lg-3">Nro.

                                Cédula</label>

                            <input type="text" class="form-control" id="identificacion" placeholder="Ej: 15123999">

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <div class="input-group">

                            <label for="tipCuenta" class="input-group-text col-4 col-sm-2 col-lg-3">Tipo Cuenta</label>

                            <select name="choice" class="form-control" id="tipCuenta" type="number">

                                <option value="00">Principal</option>

                                <option value="10" selected>Ahorro</option>

                                <option value="20">Corriente</option>

                            </select>

                        </div>



                        <div class="input-group">

                            <label for="tipTarjeta" class="input-group-text col-4 col-sm-2 col-lg-3">Tipo

                                Tarjeta</label>

                            <select name="choice" class="form-control" id="tipTarjeta" type="number">

                                <option value="1">VISA</option>

                                <option value="2">MasterCard</option>

                                <option value="3" selected>Debito Maestro</option>

                            </select>

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <div class="input-group">

                            <label for="tarjeta" class="input-group-text col-4 col-sm-2 col-lg-3">Nro. Tarjeta</label>

                            <input type="number" min="0" class="form-control" id="tarjeta">

                        </div>



                        <div class="input-group">

                            <label for="fechExp" class="input-group-text col-4 col-sm-2 col-lg-3">Fecha Venc.</label>

                            <input type="number" min="0" class="form-control" id="fechExp" placeholder="Ej: mmaaaa (122024)">

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <div class="input-group">

                            <label for="cvv" class="input-group-text col-4 col-sm-2 col-lg-3">CVV</label>

                            <input type="number" min="0" class="form-control" id="cvv">

                        </div>



                        <div class="input-group">

                            <label for="pin" class="input-group-text col-4 col-sm-2 col-lg-3" title="Clave o PIN de cajero">Clave</label>

                            <input type="number" min="0" class="form-control" id="pin" placeholder="Clave del cajero">

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <div class="col-12 col-lg-6">

                            <div class="input-group">

                                <label for="monto" class="input-group-text col-4 col-sm-2 col-lg-3">Monto Bs.</label>

                                <input type="number" min="0" step="0.1" class="form-control montos" id="monto" placeholder="0.00">

                            </div>

                        </div>

                    </div>

                </form>

            </div>



            <!-- P2P

            <div id="p2p-div">

                <h2 class="text-muted">Pago móvil <sup>p2p</sup></h2>

                <hr>

                <h5 class="text-muted">Datos del beneficiario</h5>

                <form id="p2p-frm">

                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <div class="input-group">

                            <label for="cedula" class="input-group-text col-4 col-sm-2 col-lg-3">Cédula</label>

                            <select class="form-select form-select tipo-doc" id="tipo-doc">

                                <option value="V" selected>V</option>

                                <option value="E">E</option>

                                <option value="J">J</option>

                            </select>

                            <input type="number" step="1" min="0" class="form-control" id="cedula" placeholder="Ej: 15123999">

                        </div>

                        <div class="input-group">

                            <label for="telefono" class="input-group-text col-4 col-sm-2 col-lg-3">Teléfono</label>

                            <input type="number" step="1" min="0" class="form-control" id="telefono" placeholder="Ej: 0414999888">

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <div class="input-group">

                            <label for="monto" class="input-group-text col-4 col-sm-2 col-lg-3">Monto Bs.</label>

                            <input type="number" min="0" step="0.1" class="form-control montos" id="monto" placeholder="0.00">

                        </div>

                        <div class="input-group">

                            <label for="descripcion" class="input-group-text col-4 col-sm-2 col-lg-3">Concepto</label>

                            <input type="text" class="form-control" id="concepto" placeholder="Opcional">

                        </div>

                    </div>

                </form>

            </div-->



            <!-- C2P -->

            <div id="c2p-div">

                <h2 class="text-muted">Pago móvil <sup>c2p</sup></h2>

                <small>A continuación introduzca sus datos de pago movil en el siguiente formulario junto a su clave de pagos C2P (token) porporcionada por su banco para emitir su pago.</small>

                <hr>

                <h5 class="text-muted">Datos de pago</h5>

                <form id="c2p-frm">

                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <!-- Teléfono Deudor -->

                        <div class="input-group">

                            <label for="telefono" class="input-group-text col-4 col-sm-2 col-lg-3">Teléfono</label>

                            <input type='number' step="1" min="0" class='form-control' id='telefono' placeholder="Ej: 0414888999">

                        </div>

                        <!-- Cedula Deudor -->

                        <div class="input-group">

                            <label for="cedula" class="input-group-text col-4 col-sm-2 col-lg-3">Cédula</label>

                            <select class="form-select form-select tipo-doc" id="tipo-doc">

                                <option value="V" selected>V</option>

                                <option value="E">E</option>

                                <option value="J">J</option>

                            </select>

                            <input type='number' step="1" min="0" class='form-control' id='cedula' placeholder="Ej: 15666999">

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <!-- Codigo de banco -->

                        <div class="input-group">

                            <label for="banco" class="input-group-text col-4 col-sm-2 col-lg-3">Banco</label>

                            <select class="form-select form-select codigo-bancos" id="co-banco">

                                <option value="" selected>Seleccionar</option>

                            </select>

                        </div>

                        <!-- Token -->

                        <div class="input-group">

                            <label for="token" class="input-group-text col-4 col-sm-2 col-lg-3">Token</label>

                            <input type='text' class='form-control' id='token' placeholder="Codigo de autorización emitido por su banco">

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <!-- Monto -->

                        <div class="input-group">

                            <label for="monto" class="input-group-text col-4 col-sm-2 col-lg-3">Monto Bs.</label>

                            <input type="number" min="0" step="0.1" class="form-control montos" id="monto" placeholder="0.00">

                        </div>



                        <div class="input-group">

                            <label for="descripcion" class="input-group-text col-4 col-sm-2 col-lg-3">Concepto</label>

                            <input type="text" class="form-control" id="concepto" placeholder="Opcional">

                        </div>

                    </div>

                </form>

            </div>



            <!-- REPORTE DE PAGO MOVIL DE BANCOS SIN C2P -->

            <div id="reporte-pmov-div">

                <h2 class="text-muted">Reportar pagos desde otros bancos</h2>

                <small>

                    Aqui podra reportar Pago movil/transferencias realizadas desde otros bancos. <br>

                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#datos-pmov-modal">

                        Datos de pago<i class="bi bi-phone"></i>

                    </button>

                </small>

                <hr>

                <h5 class="text-muted">Datos de pago</h5>

                <form id="reporte-pagos-frm">

                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <!-- Tipo de pago -->

                        <div class="input-group">

                            <label for="tipo-pago" class="input-group-text col-4 col-sm-2 col-lg-3">Tipo pago</label>

                            <select class="form-select form-select tipo-pago" id="tipo-pago">

                                <option value="TRF" selected>Transferencia</option>

                                <option value="PMOV">Pago movil</option>

                            </select>

                        </div>

                        <!-- Codigo de banco -->

                        <div class="input-group">

                            <label for="banco" class="input-group-text col-4 col-sm-2 col-lg-3">Banco</label>

                            <select class="form-select form-select codigo-bancos" id="banco">

                                <option value="" selected>Seleccionar</option>

                            </select>

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <!-- Monto -->

                        <div class="input-group">

                            <label for="monto" class="input-group-text col-4 col-sm-2 col-lg-3">Monto Bs.</label>

                            <input type="number" min="0" step="0.1" class="form-control montos" id="monto" placeholder="0.00">

                        </div>

                        <!-- Referencia -->

                        <div class="input-group">

                            <label for="descripcion" class="input-group-text col-4 col-sm-2 col-lg-3">Referencia</label>

                            <input type="text" class="form-control" id="referencia" maxlength="6" placeholder="Ultimos 6 dígitos">

                        </div>

                    </div>



                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between gap-2 mb-2">

                        <!-- Concepto del pago -->

                        <div class="input-group">

                            <label for="descripcion" class="input-group-text col-4 col-sm-2 col-lg-3">Concepto</label>

                            <input type="text" class="form-control" id="concepto" placeholder="Opcional">

                        </div>



                        <!-- Telefono -->

                        <div style="width:100%">

                            <div class="input-group" id="telefono-div" style="display:none">

                                <label for="telefono" class="input-group-text col-4 col-sm-2 col-lg-3">Teléfono</label>

                                <input type='number' step="1" min="0" class='form-control' id='telefono' placeholder="Ej: 0414888999" disabled>

                            </div>

                        </div>

                    </div>

                </form>

            </div>



            <!-- DOCUMENTOS -->

            <div id="ne-div">

                <!-- ENCABEZADO NE -->

                <div class="my-3">

                    <h3 class="text-muted">Documentos</h3>

                    <details>

                        <summary class="text-success">Ver instrucciones de uso <i class="bi bi-list"></i></summary>

                        <ul>

                            <li>A continuación agrege los documentos entre las cuales se debe distribuir su pago

                            </li>

                            <li>Indique el numero de documento y el abono que desea asignar al mismo</li>

                            <li>El total de la suma de abonos asignados entre documentos no debe superar el monto de pago

                                establecido en el formulario de pagos</li>

                            <li>Una vez completados los campos haga clic en <span class="text-success">"Agregar"</span></li>

                            <li>Para limpiar los campos de datos haga clic en limpiar campos <span class="text-danger"><i class="bi bi-trash"></i></span></li>
                            <li>Para agregar multiples abonos desde un archivo haga clic en el boton "Asignar multiples abonos desde archivo"</li>

                        </ul>

                    </details>

                </div>

                <div class="my-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#abonos-desde-archivo-modal">
                        Asignar multiples abonos desde archivo <i class="bi bi-table"></i>
                    </button>
                </div>



                <!-- CAMPOS DE DATOS -->

                <div class="col-12 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="input-group">
                        <label for="documento-in" class="input-group-text" title="Numero de documento">Asignar 1 Abono.</label>

                        <input type="number" step="1" min="0" class="form-control" id="numero-ne-in" placeholder="Numero documento">

                        <input type="number" step="1" min="0" class="form-control montos me-1" id="abono-ne-in" placeholder="Monto">
                    </div>
                    <div class="btn-group" role="group" aria-label="Button Group">

                        <button id="cargar-abono-simple-btn" class="btn btn-outline-success cargar-docs-btn">Agregar</button>

                        <button id="reiniciar-campos-btn" class="btn btn-outline-danger" title="Limpiar campos"><i class="bi bi-trash"></i></button>

                    </div>
                </div>





                <!-- TABLA NE -->

                <div id="notas-entrega-div" class="mb-3">

                    <div class="table-responsive">

                        <table id="notas-entrega-tbl" class="table table-striped">

                            <thead class="table-secondary">

                                <tr>

                                    <th scope="col">Nro. de documento</th>
                                    <th scope="col">Cli. responsable</th>
                                    <th scope="col">Cli. final</th>
                                    <th scope="col">Monto de pago Bs.</th>
                                    <th scope="col">Acc</th>

                                </tr>

                            </thead>

                            <tbody>

                                <!-- ABONOS -->

                            </tbody>

                        </table>

                    </div>

                </div>



                <!-- ENVIAR PAGO -->

                <div class="col-12 text-center mb-3">

                    <button class="mainBtn" data-bs-toggle="modal" data-bs-target="#confirmar-enviar-pagos-modal"><i class="bi bi-send"></i> Enviar pago</button>

                </div>

            </div>



            <!-- HISTORIAL -->

            <div id="historial-div">

                <h2 class="text-muted">Historial de pagos</h2>

                <small>Haga clic en algun registro para ver los documentos asociados.</small>

                <hr>

                <div id="resultado-div">

                    <!-- RESULTADOS DE LA CONSULTA -->

                </div>

            </div>



            <!-- MODAL: PRELOADER Y AVISOS -->

            <?php include("modal-windows.php"); ?>

        </main>

    </div>

</body>



</html>