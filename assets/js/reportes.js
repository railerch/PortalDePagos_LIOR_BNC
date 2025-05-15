import utils from "./main-utils.js";

window.onload = () => {
    $("#preloader-modal").modal("show");

    // MOSTRAR U OCULTAR HISTORIAL/LOGS
    document.getElementById("toggle-btn").addEventListener("click", (evt) => {
        const historial = document.getElementById("historial-registros");
        const logs = document.getElementById("logs-div");
        historial.classList.toggle('hide');
        logs.classList.toggle('hide');
    })

    // NOMBRE DEL CLIENTE EN EL HEADER
    document.getElementById("nombre-cliente").innerText = `Hola, ${sessionStorage.getItem("client")}`;

    // CERRAR SESION
    document.getElementById("cerrar-sesion-btn").addEventListener("click", (evt) => {
        sessionStorage.clear();

        fetch("controller.php?cerrar-sesion=true")
            .then(res => res.json())
            .then(res => {
                if (res.status == "success") {
                    $("#preloader-modal").modal("show");
                    setTimeout(() => {
                        window.location.replace("login.php");
                    }, 1500);
                } else if (res.status == "error") {
                    utils.aviso_modal("danger", res.message);
                }
            })
            .catch(err => {
                utils.aviso_modal("danger", err);
            });


    })

    // CONSULTAR REGISTRO DE PAGOS Y DOCUMENTOS ASOCIADOS

    // Autorizacion y formato
    const dat = atob(sessionStorage.getItem("sessionID")).split("-");

    // Enviar peticion
    fetch("controller.php?historial-pagos-documentos=true", {
        headers: { "Authorization": "Basic " + btoa(`${dat[0]}:${dat[1]}`) }
    }).then(res => res.json())
        .then(res => {
            $("#preloader-modal").modal("hide");

            if (res.status == "success") {
                if (res.data != "") {
                    utils.generar_tabla("regs-table-div", "historial-tbl", res.data, true, 'historial_pagos');
                } else {
                    document.getElementById("regs-table-div").innerHTML = "<h4 class=text-warning>Sin pagos registrados.</h4>"
                }
            } else {
                utils.aviso_modal("error", res.message);
            }
        }).catch(err => {
            $("#preloader-modal").modal("hide");
            utils.aviso_modal("danger", err);
        }).finally(() => console.log("Proceso finalizado."))

    // CONSULTAR LOGS
    fetch("controller.php?logs-sistema=true", {
        headers: { "Authorization": "Basic " + btoa(`${dat[0]}:${dat[1]}`) }
    }).then(res => res.json())
        .then(res => {
            $("#preloader-modal").modal("hide");

            if (res.status == "success") {
                utils.generar_tabla("logs-table-div", "logs-tbl", res.data, true, 'logs_del_sistema');
            } else {
                utils.aviso_modal("error", res.message);
            }
        }).catch(err => {
            $("#preloader-modal").modal("hide");
            utils.aviso_modal("danger", err);
        }).finally(() => console.log("Proceso finalizado."))
}