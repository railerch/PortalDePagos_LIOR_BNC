import utils from "./main-utils.js";

window.onload = () => {
    // ACTIVAR EL BOTON DE ENVIAR SI LA DIRECCION DE CORREO CUMPLE CON EL FORMATO
    let sendBtn = document.querySelector("#pass-request-frm button[type=submit]");
    document.getElementById("email").addEventListener("keyup", (evt) => {
        if (evt.target.value.search(/(.+@.+\.[a-z]{2,6})/ig) != -1) {
            sendBtn.removeAttribute("disabled");
        } else {
            sendBtn.setAttribute("disabled", true);
        }
    })

    // ENVIAR SOLICITUD PARA RECUPERAR CONTRASEÑA
    document.getElementById("pass-request-frm").addEventListener("submit", (evt) => {
        evt.preventDefault();

        $("#preloader-modal").modal("show");

        // Enviar datos para validar
        let correo = evt.target.elements[0].value.toLowerCase();
        const data = new URLSearchParams({ "email": correo }).toString();

        fetch("controller.php?solicitar-cambio-clave=true", {
            method: "post",
            body: data,
            headers: { "content-type": "application/x-www-form-urlencoded" }
        })
            .then(res => res.json())
            .then(res => {
                $("#preloader-modal").modal("hide");

                if (res.status == "success") {
                    evt.target.reset();
                    utils.aviso_modal("success", res.message);
                    $("#preloader-modal").modal("show");
                    setTimeout(function () {
                        window.location.replace("login.php");
                    }, 2500)
                } else if (res.status == "error") {
                    utils.aviso_modal("danger", res.message);
                    sendBtn.setAttribute("disabled", true);
                }

                evt.target.reset();
            }).catch(err => {
                $("#preloader-modal").modal("hide");
                utils.aviso_modal("danger", err);
            })
    })
}