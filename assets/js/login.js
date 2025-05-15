import utils from "./main-utils.js";

window.onload = () => {
    // INICIO DE SESION
    document.getElementById("login-frm").addEventListener("submit", (evt) => {
        evt.preventDefault();

        // Recolectar datos
        let inputs = Array.from(evt.target.elements);
        let usuario = inputs[0].value.toLowerCase();
        let clave = inputs[1].value;

        // cabecera del request
        const headers = new Headers();
        headers.append("content-type", "application/x-www-form-urlencoded");

        if (usuario != "" && clave != "") {
            $("#preloader-modal").modal("show");

            // Enviar datos para autenticar
            const data = new URLSearchParams({ "usuario": usuario, "clave": clave }).toString();

            fetch("controller.php?iniciar-sesion=true", {
                method: "post",
                body: data,
                headers: headers
            })
                .then(res => res.json())
                .then(res => {
                    $("#preloader-modal").modal("hide");

                    if (res.status == "success") {
                        if (res.auth) {
                            if (res.auth.status == "KO") {
                                // Error en la autenticacion u otro detalle con la API del BNC
                                utils.aviso_modal("danger", "API Error: contácte con el administrador.");
                                console.log(`API Error: ${res.auth.message}`);
                            } else {
                                // Autenticacion API y de usuario OK
                                sessionStorage.setItem("sessionID", res.message);
                                sessionStorage.setItem("client", res.name);
                                sessionStorage.setItem("host", res.host);
                                sessionStorage.setItem("neAPI", res.neAPI);
                                sessionStorage.setItem("Conn", res.conn_instance);
                                sessionStorage.setItem("Process", res.process_instance);

                                setTimeout(function () {
                                    if (res.name.toLowerCase() != "admin") {
                                        window.location.replace("main.php");
                                    } else {
                                        window.location.replace("reportes.php");
                                    }
                                }, 1000)
                            };
                        } else {
                            // Sin respuesta de la API del BNC
                            utils.aviso_modal("danger", "API Error: contácte con el administrador.");
                            console.log(`API Error: ${res.auth.message}`);
                        }
                    } else if (res.status == "error") {
                        // Error de inicio de sesion de usuario
                        utils.aviso_modal("danger", res.message);
                    }
                })
                .catch($err => {
                    $("#preloader-modal").modal("hide");
                    utils.aviso_modal("danger", $err);
                })
        } else {
            utils.aviso_modal("warning", "Debe completar todos los campos.")
        }

    })
}