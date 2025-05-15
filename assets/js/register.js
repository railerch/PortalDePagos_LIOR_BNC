import utils from "./main-utils.js";

window.onload = () => {
    // VALIDACION DE CONFIRMACION DE CLAVE EN TIEMPO REAL
    let claves = document.querySelectorAll(".clave");
    let btn = document.querySelector("#register-frm button[type=submit]");

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

    // REGISTRAR CLIENTE
    document.getElementById("register-frm").addEventListener("submit", (evt) => {
        evt.preventDefault();
        let procesar = true;

        let inputs = Array.from(evt.target.elements);
        let nombre = inputs[0].value.toLowerCase();
        let cedula = inputs[1].value.toLowerCase();
        let correo = inputs[2].value.toLowerCase();
        let telefono = inputs[3].value.toLowerCase();
        let clave = inputs[4].value;

        // Validar que los campos esten completos
        [correo, nombre, clave].forEach(el => {
            if (el == "") procesar = false;
        })

        // Procesar registro en caso de estar todo OK!
        if (procesar) {
            $("#preloader-modal").modal("show");

            const data = new URLSearchParams({ "nombre": nombre, "cedula": cedula, "correo": correo, "telefono": telefono, "clave": clave }).toString();

            fetch("controller.php?registrar-cliente=true", {
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
                        //evt.target.reset();
                        utils.aviso_modal("danger", res.message);
                    }
                })
                .catch($err => {
                    utils.aviso_modal("danger", $err);
                })

        } else {
            // Si algun campo esta vacio
            utils.aviso_modal("warning", "Debe llenar todos los campos.");
        }
    })
}