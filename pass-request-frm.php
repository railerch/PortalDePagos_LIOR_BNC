<!DOCTYPE html>
<html lang="es">

<head>
    <?php require("head.php") ?>
    <title>Lior Pagos | Registro</title>
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
                    <b class="text-muted">Recuperar clave de cuenta</b>
                </div>

                <form id="pass-request-frm">
                    <div class="mb-3 text-start">
                        <div class="mb-2">
                            <small id="helpId-mail" class="form-text text-muted">
                                Se enviaran instrucciones para restablecer su contraseña a la dirección de correo indicada, esta debe estar registrada en nuestro sistema. <br>
                                <span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Una vez hecha la solicitud no podra iniciar sesion hasta restablecer su contraseña.</span>
                            </small>
                        </div>
                        <input type="email" class="form-control" name="email" id="email" aria-describedby="helpId-mail" placeholder="Correo" />
                    </div>

                    <div class="btn-toolbarmb-3" role="toolbar" aria-label="Toolbar">
                        <div class="btn-group text-center" role="group" aria-label="Button Group">
                            <a href="login.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i>Atras
                            </a>

                            <button type="submit" class="btn btn-primary" disabled>
                                <i class="bi bi-send"></i> Enviar
                            </button>
                        </div>
                    </div>
                </form>
            </article>
        </section>
    </main>

    <!-- MODAL: PRELOADER Y AVISOS -->
    <?php include("modal-windows.php"); ?>
</body>

</html>