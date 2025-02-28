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

                <<div class="mb-3">
                    <h3 class="text-muted">Plataforma de pago</h3>
                    <b class="text-muted">Registrar cliente</b>
                </div>

                <form id="register-frm">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Nombre completo" />
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="cedula" id="cedula" placeholder="Cedula" />
                    </div>
                    <div class="mb-3 text-start">
                        <input type="email" class="form-control" name="correo" id="correo" placeholder="Correo" />
                    </div>
                    <div class="mb-3 text-start">
                        <input type="number" class="form-control" name="telefono" id="telefono" placeholder="Nro. telefónico" />
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control clave" name="clave" id="clave" placeholder="Clave" />
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control clave" name="confirmacion" id="confirmacion-clave" placeholder="Repetir clave" />
                    </div>

                    <div class="btn-toolbarmb-3" role="toolbar" aria-label="Toolbar">
                        <div class="btn-group text-center" role="group" aria-label="Button Group">
                            <a href="login.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i>Atras
                            </a>

                            <button type="submit" class="btn btn-success" disabled>
                                <i class="bi bi-check"></i> Aceptar
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