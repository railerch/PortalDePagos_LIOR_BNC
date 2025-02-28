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
    <title>Reportes</title>
</head>

<body>
    <header id="pagos-header">
        <div class="col-12 col-md-8">
            <img id="main-logo" src="assets/img/liorLogoB.png" alt="Lior Cosmetics logo">
            <h3 class="h1-G">Portal de pagos</h3>
        </div>
        <div class="col-12 col-md-4">
            <h4 class="m-0" id="nombre-cliente"></h4>
            <button id="cerrar-sesion-btn" class="btn btn-outline-light" title="Cerrar sesión"><i class="bi bi-power"></i></button>
        </div>
    </header>

    <div id="contenido">


        <main class="col-12 col-lg-11 mx-auto">
            <!-- MENU BTN -->
            <div class="mb-3">
                <button id="toggle-btn" class="btn btn-outline-primary" data-ocultar="logs-div" data-div="historial-registros" type="button">
                    Historial/Logs
                </button>
            </div>

            <!-- HISTORIAL DE PAGOS -->
            <div id="historial-registros" class="">
                <h2 class="text-muted">Historial de registros</h2>
                <hr>
                <div id="regs-table-div">
                    <!-- CONTENEDOR PARA TABLA -->
                </div>
            </div>

            <!-- LOGS -->
            <div id="logs-div" class="hide">
                <h2 class="text-muted">Logs del sistema</h2>
                <hr>
                <div id="logs-table-div">
                    <!-- CONTENEDOR PARA TABLA -->
                </div>
            </div>
        </main>
    </div>

    <!-- PRELOADER MODAL -->
    <div class="modal modal-sm" id="preloader-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="assets/img/preloader.gif" alt="Preloader Gif">
                </div>
            </div>
        </div>
    </div>
</body>

</html>