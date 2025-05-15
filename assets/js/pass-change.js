import utils from "./main-utils.js";

window.onload = () => {
    // VALIDACION DE CONFIRMACION DE CLAVE EN TIEMPO REAL
    let claves = document.querySelectorAll(".clave");
    let btn = document.querySelector("#pass-change-frm button[type=submit]");

    claves.forEach(inp => {
        inp.addEventListener("keyup", (evt) => {
            if (claves[0].value != claves[1].value) {
                claves[1].classList.add("invalid");
                btn.setAttribute("disabled", true);
            } else {
                claves[1].classList.remove("invalid");
                btn.removeAttribute("disabled");
            }
        })
    })

    // ENVIAR SOLICITUD PARA RESTABLECER CLAVE
    document.getElementById("pass-change-frm").addEventListener("submit", (evt) => {
        evt.preventDefault();

        // Enviar datos para validar
        let data = JSON.stringify({ clave: evt.target.elements[0].value });
        fetch("controller.php?cambiar-clave=true", { method: "post", headers: { "content-rype": "application/json" }, body: data })
            .then(res => res.json())
            .then(res => {
                if (res.status == "success") {
                    utils.aviso_modal("success", res.message);
                    $("#preloader-modal").modal("show");
                    setTimeout(function () {
                        window.location.replace("login.php");
                    }, 2500)
                } else {
                    utils.aviso_modal("danger", res.message);
                }
            }).catch(err => utils.aviso_modal("danger", err))
    })
}