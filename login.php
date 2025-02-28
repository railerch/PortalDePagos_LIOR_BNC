<?php
session_start();
if (@$_SESSION['session_id']) {
    header('location: main.php');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php require("head.php") ?>
    <title>Lior Pagos | Inicio</title>
</head>

<body>
    <main>
        <div id="bg-img"></div>
        <section>
            <article class="form-window">
                <figure>
                    <img id="forms-logo" src="assets/img/liorLogoN.png" alt="Logo Lior Cosmetics">
                </figure>

                <div class="mb-3">
                    <h3 class="text-muted">Plataforma de pago</h3>
                    <b class="text-muted">Inicio de sesión</b>
                </div>

                <form id="login-frm">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Correo ó numero celular" />
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="clave" id="clave" placeholder="Clave" />
                    </div>

                    <div class="btn-toolbarmb-3" role="toolbar" aria-label="Toolbar">
                        <div class="my-2">
                            <a class="text-decoration-none" href="pass-request-frm.php"><i class="bi bi-key"></i> Recuperar contraseña</a>
                        </div>
                        <div class="btn-group text-center" role="group" aria-label="Button Group">
                            <a class="btn btn-outline-secondary" href="register.php">
                                <i class="bi bi-pencil-square"></i> Registrarse
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </button>
                        </div>
                    </div>
                </form>

                <button
                    type="button"
                    id="install-app-btn"
                    class="btn btn-sm btn-outline-primary mt-3" 
                    style="display:none">
                    Instalar APP <i class="bi bi-download"></i>
                </button>

            </article>
        </section>
    </main>

    <!-- MODAL: PRELOADER Y AVISOS -->
    <?php include("modal-windows.php"); ?>
</body>

</html>