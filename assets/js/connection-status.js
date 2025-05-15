import utils from "./main-utils.js";

// ESTATUS DE LA CONEXION A INTERNET
const OfflineTxt = `
        Sin conexión a internet <i class="bi bi-wifi-off text-danger"></i>
        <br>
        Podra continuar cuando se restablezca la conexión.
        `;
const onlineTxt = 'Conectado a Internet <i class="bi bi-wifi text-success"></i>';

function offline_test() {
    if (!navigator.onLine) {
        utils.aviso_conex_modal("danger", OfflineTxt);
    } else {
        console.log("Conexión a internet OK!");
    }
}

window.addEventListener('load', offline_test);
window.addEventListener('offline', offline_test);
window.addEventListener('online', function () {
    if (navigator.onLine) {
        utils.aviso_conex_modal("success", onlineTxt);

        setTimeout(() => {
            $("#aviso-conex-modal").modal("hide");
        }, 1000)
    }
});