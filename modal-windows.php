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

<!-- AVISOS MODAL -->
<div class="modal modal-sm fade" id="avisos-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="modalTitleId">
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <p></p>
            </div>
        </div>
    </div>
</div>

<!-- AVISO CONEXION INTERNET MODAL -->
<div class="modal modal-sm fade" id="aviso-conex-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="modalTitleId">
                </h5>
            </div>
            <div class="modal-body p-2">
                <p></p>
            </div>
        </div>
    </div>
</div>

<!-- DETALLES DE PAGOS  MODAL -->
<div class="modal modal-sm fade" id="detalles-pago-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title text-muted" id="modalTitleId">
                    Detalles del pago <span class="text-primary" id="ref-pago"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="detalles-pago-div">
                    <!-- CONTENIDO -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DATOS PAGO MOVIL -->
<div class="modal modal-sm fade" id="datos-pmov-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document" style="max-width:350px">
        <div class="modal-content">
            <div class="modal-body p-2">
                <h3><i class="bi bi-phone"></i> Datos de pago</h3>
                <p>Paga desde tu banco con los siguientes datos</p>
                <div>
                    <ul>
                        <li><b>Razón social:</b> Lior Cosmetics, C.A.</li>
                        <li><b>Banco:</b> Banco Nacional de Crédito (0191)</li>
                        <li><b>Nro. Cuenta:</b> 0191-0220-31-2100008999</li>
                        <li><b>RIF:</b> J-31084990-0</li>
                        <li><b>Telf. pago móvil:</b> 0424-2134724</li>
                    </ul>
                </div>

                <small>Registra el nombre como favorito para tu próximo pago.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ASIGNACION DE ABONOS DESDE ARCHIVO -->
<div class="modal modal-sm" id="abonos-desde-archivo-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title text-muted" id="modalTitleId">
                    <i class="bi bi-table"></i> Abonos desde archivo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <ul>
                    <li>El formato del archivo debe ser CSV</li>
                    <li>El archivo solo debe tener dos datos por linea en el siguiente orden: <br><b><span class="text-primary">numero de documento</span></b> seguido del <b><span class="text-primary">el monto del abono</span></b> separados por (;) y sin espacios.</b>
                    </li>
                    <li>Una vez seleccionado el archivo haga clic en <b>Procesar archivo</b></li>
                    <li>Al finalizar la carga pulse <b>Enviar pago</b></li>
                </ul>
                <div class="input-group">
                    <input type="file" class="form-control" name="pagos-file" id="pagos-file-in"
                        placeholder="Solo archivos csv" accept="text/csv" />
                    <button id="cargar-archivo-abonos-btn" type="button" class="btn btn-success" data-bs-dismiss="modal">
                        Procesar archivo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CONFIRMAR ENVIO DEL PAGO -->
<div class="modal modal-sm" id="confirmar-enviar-pagos-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-muted" id="modalTitleId">
                    <i class="bi bi-send"></i> Procesar abonos
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta a punto de procesar el pago y los abonos asociados, esta operacion no tiene reverso, desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="confirmar-enviar-pago-btn" data-bs-dismiss="modal">Si</button>
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    No
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MANTANER LA SESION ACTIVA -->
<div class="modal modal-sm" id="mantener-sesion-activa-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-muted" id="modalTitleId">
                    <i class="bi bi-person-workspace"></i> Mantener sesion activa
                </h5>
            </div>
            <div class="modal-body">
                <p>Han transcurrido 5 minutos desde que ingreso al sistema, desea continuar con la sesión activa?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="mantener-sesion-btn" data-bs-dismiss="modal">Si</button>
                <button
                    type="button"
                    class="btn btn-danger"
                    id="finalizar-sesion-btn"
                    data-bs-dismiss="modal">
                    No
                </button>
            </div>
        </div>
    </div>
</div>