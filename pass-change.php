<!DOCTYPE html>
<html lang="es">

<head>
    <?php require("head.php") ?>
    <title>Lior Pagos | Cambiar clave</title>
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
                    <h3 class="text-muted">Cambiar clave</h3>
                    <p>
                        <small>
                            A continuación indique su nueva clave, se recomienda usar numeros, letras y simbolos para mayor seguridad.
                        </small>
                    </p>
                </div>

                <form id="pass-change-frm">
                    <div class="mb-3">
                        <input type="text" class="form-control clave" name="clave" id="clave" placeholder="Nueva clave" />
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control clave" name="confirmacion" id="confirmacion-clave" placeholder="Repetir clave" />
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-success" disabled>
                            <i class="bi bi-check"></i> Aceptar
                        </button>
                    </div>

                </form>
            </article>
        </section>
    </main>

    <!-- MODAL: PRELOADER Y AVISOS -->
    <?php include("modal-windows.php"); ?>
</body>

</html>