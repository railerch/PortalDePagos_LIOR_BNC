import utils from "./main-utils.js";
import codBancos from "./codigo-bancos.json" with {type: "json"};

window.onload = () => {
    // VALIDAR TIEMPO DE SESION
    {
        const tiempoSesion = 300000;
        sessionStorage.setItem("TiempoSesion", `${tiempoSesion / 60000}min`);

        // ==========> Activar contador
        setTimeout(() => {
            $("#mantener-sesion-activa-modal").modal("show");
        }, tiempoSesion)

        // ==========> Mantener la sesion activa
        document.getElementById("mantener-sesion-btn").addEventListener("click", (evt) => {
            $("#preloader-modal").modal("show");
            let intentos = 0;
            utils.renovar_token_sesion(intentos, tiempoSesion);
        });

        // ==========> Finalizar la sesion
        document.getElementById("finalizar-sesion-btn").addEventListener("click", (evt) => {
            utils.cerrar_sesion();
        });
    }

    // CONSULTAR TASA BCV
    const tiempo = 900000; // 15 min
    utils.consultar_tasa_bcv(); // Consulta inicial

    // Consultar cada 15 min
    setInterval(() => {
        utils.consultar_tasa_bcv();
    }, tiempo);

    // Actualizar tasa al pulsar el btn de actualizar
    document.getElementById("actualizar-tasa-btn").addEventListener("click", (evt) => {
        document.querySelector("#tasa-bcv-badge span").innerText = "0.00";
        utils.consultar_tasa_bcv();
    })

    // ESTABLECER VPOS COMO PAGO INICIAL
    /* Al entrar en la pantalla de pagos no hay ningun formulario activo, al ser un solo boton 
    el que procesa los pagos ya sean VPOS o P2P la seleccion del formulario es dinamica por lo cual se debe 
    haber seleccionado alguno de estos antes de procesar para que no emita un error*/
    sessionStorage.setItem("pagoFrm", "vpos-frm");

    // Eliminar pagos cargados en la sesion al refrescar la pagina
    sessionStorage.removeItem("docs");

    // MONTOS DE PAGO CON 2 DECIMALES AUTOMATICOS
    document.querySelectorAll(".montos").forEach(monto => {
        monto.addEventListener("change", (evt) => {
            evt.target.value = parseFloat(evt.target.value).toFixed(2);
        })
    })

    // CODIGOS DE BANCOS PARA PAGOS p2P
    const bancosSel = document.querySelectorAll(".codigo-bancos");
    bancosSel.forEach(selector => {
        const bancos = Object.keys(codBancos[0]);

        for (let bco in codBancos[0]) {
            const opt = document.createElement("option");
            opt.value = codBancos[0][bco];
            opt.innerText = bco;
            const frm = sessionStorage.getItem("pagoFrm");
            if (parseInt(codBancos[0][bco]) == 191) opt.selected = true;
            selector.appendChild(opt);
        }
    })

    // TIPO DE PAGO EN REPORTE DE PAGOS DESDE OTROS BANCOS
    document.querySelector("#reporte-pagos-frm #tipo-pago").addEventListener("change", (evt) => {
        const telfDiv = document.querySelector("#reporte-pagos-frm #telefono-div");
        const input = document.querySelector("#reporte-pagos-frm #telefono-div input");
        if (evt.target.value == "TRF") {
            telfDiv.style.display = "none";
            input.setAttribute("disabled", true);
            input.value = "";
        } else {
            telfDiv.style.display = "flex";
            input.removeAttribute("disabled");
        }
    })

    // NOMBRE DEL CLIENTE EN EL HEADER
    document.getElementById("nombre-cliente").innerText = `Hola, ${sessionStorage.getItem("client")}`;

    // CERRAR SESION
    document.getElementById("cerrar-sesion-btn").addEventListener("click", (evt) => {
        utils.cerrar_sesion();
    })

    // MOSTRAR FORMULARIO DE PAGOS
    document.querySelectorAll(".asideBtn").forEach(btn => {
        btn.addEventListener("click", (evt) => {
            utils.cambiar_vistas(evt.target.id);
            document.querySelectorAll("form").forEach(frm => frm.reset());
            document.querySelector("#notas-entrega-tbl tbody").innerHTML = "";
            document.getElementById("mobile-menu").removeAttribute("open");
            document.getElementById("numero-ne-in").value = "";
            document.getElementById("abono-ne-in").value = "";

            // Mostrar modal con datos de pago movil para pantalla de reporte de pagos
            if (evt.target.id == "aside-report-btn") {
                $("#datos-pmov-modal").modal("show");
            }
        })
    })

    // DOCUMENTOS (NE)

    // ======> Carga simple
    document.getElementById("cargar-abono-simple-btn").addEventListener("click", (evt) => {
        $("#preloader-modal").modal("show");

        const totalPago = parseFloat(document.querySelector(`#${sessionStorage.getItem("pagoFrm")} #monto`).value);
        let numNotaIn = document.getElementById("numero-ne-in");
        let abonoIn = document.getElementById("abono-ne-in");
        let perviamenteAgregado = false;
        let camposVacios = false;
        let abonosEnTabla = 0;

        // Validar que los campos no esten vacios
        if (numNotaIn.value != "" && abonoIn.value != "") {
            // Validar que la nota no haya sido agregada al pool de abonos
            const renglones = document.querySelectorAll("#notas-entrega-tbl tbody tr");

            if (renglones.length > 0) {
                document.querySelectorAll("#notas-entrega-tbl tbody tr").forEach(ne => {
                    // Validar que no se haya cargado previamente
                    if (ne.querySelector("td:nth-child(1)").innerText.includes(numNotaIn.value)) {
                        perviamenteAgregado = true;
                    }

                    // Sumar abonos agregados
                    abonosEnTabla += parseFloat(ne.querySelector("td:nth-child(4)").innerText);

                    return perviamenteAgregado;
                });
            }
        } else {
            camposVacios = true;
        }

        // Si el pago es simple se valida si la NE no ha sido agregada previamente (no aplica para abonos multiples)
        if (!camposVacios) {
            if (!perviamenteAgregado) {
                if (totalPago > 0 && abonoIn.value > 0) {
                    // Validar que el numero de nota este registrado en el sistema
                    fetch(`controller.php?validar-documentos=true`, {
                        method: "post",
                        body: JSON.stringify([[numNotaIn.value, abonoIn.value]]),
                        headers: { "Content-type": "application/json" }
                    }).then(res => res.json()).then(res => {
                        $("#preloader-modal").modal("hide");
                        const doc = res[0];
                        if (doc.status == "error") {
                            switch (doc.code) {
                                case 404:
                                    utils.aviso_modal("danger", doc.data);
                                    break;
                                case 502:
                                    utils.aviso_modal("warning", doc.data);
                                    break;
                            }
                        } else {
                            utils.agregar_renglon_abonos(doc, totalPago, abonosEnTabla);
                            numNotaIn.value = "";
                            abonoIn.value = "";
                            document.getElementById("numero-ne-in").focus();
                        }
                    }).catch(err => {
                        $("#preloader-modal").modal("hide");
                        utils.aviso_modal("danger", 'Error en consulta, si el mismo persiste consulte con el departamento de ventas.')
                        console.log(err)
                    });
                } else {
                    $("#preloader-modal").modal("hide");
                    utils.aviso_modal("danger", "El monto de pago o el abono no pueden estar en cero o vacios.");
                }
            } else {
                $("#preloader-modal").modal("hide");
                utils.aviso_modal("danger", "El numero de documento que indico ya esta agregado.");
            }
        } else {
            $("#preloader-modal").modal("hide");
            utils.aviso_modal("warning", "Datos de abono incompletos, verifique e intente nuevamente.");
        }
    })

    // ======> Carga desde archivo
    document.getElementById("cargar-archivo-abonos-btn").addEventListener("click", (evt) => {
        $("#preloader-modal").modal("show");

        let docsDuplicadosEnArchivo = null;
        let docsAgregados = [];
        let docsNoAgregados = [];
        let abonoTotalEnArchivo = 0;
        const frmID = sessionStorage.getItem("pagoFrm");

        // Monto total en frm cabecera de pago
        const totalPago = parseFloat(document.querySelector(`#${frmID} #monto`).value);

        // Eliminar pagos cargados previamente
        sessionStorage.removeItem("docs");

        // Extraer datos del archivo
        utils.leer_archivo(res => {
            if (res) {
                sessionStorage.setItem("docs", JSON.stringify(res.validos));
                docsDuplicadosEnArchivo = res.duplicados;
            } else {
                $("#preloader-modal").modal("hide");
                utils.aviso_modal("danger", "Debe seleccionar un archivo.");
            }
        });

        // Retrasar la carga hasta que este disponible en el storage
        setTimeout(() => {
            $("#preloader-modal").modal("hide");

            const docs = JSON.parse(sessionStorage.getItem("docs"));

            if (docs) {
                console.log("Documentos para validar y cotejar: ", docs);

                // Filtrar documentos agregados y NO agregados
                const renglones = document.querySelectorAll("#notas-entrega-tbl tbody tr");
                if (renglones.length > 0) {
                    docs.forEach(docFl => {
                        let agregado = false;
                        console.log("Cotejando contra tabla doc: #", docFl[0]);

                        renglones.forEach(ne => {
                            if (ne.querySelector("td:nth-child(1)").innerText.includes(docFl[0])) {
                                agregado = true;
                            }
                        });

                        if (agregado) {
                            console.log(`Documento #${docFl[0]} ya agregado previamente.`)
                            docsAgregados.push(docFl[0]);
                        } else {
                            docsNoAgregados.push(docFl);
                            abonoTotalEnArchivo += parseFloat(docFl[1]);
                        }
                    });
                } else {
                    docsNoAgregados = docs;
                    console.log("Sin registros en tabla de abonos.");

                    // Monto total del abono en archivo
                    docsNoAgregados.forEach(doc => abonoTotalEnArchivo += parseFloat(doc[1]));
                }

                console.log("Monto total de abonos validos en archivo: Bs.", abonoTotalEnArchivo);
                console.log("Documentos duplicados en archivo: ", docsDuplicadosEnArchivo);
                console.log("Documentos duplicados en tabla de abonos: ", docsAgregados);
                console.log("Documentos para validar y agregar en tabla de abonos: ", docsNoAgregados);

                // Validar que existan registros para agregar
                if (docsNoAgregados.length > 0) {
                    // Validar que tanto el pago como los abonos en el archivo sean mayor que cero
                    if (totalPago > 0 && abonoTotalEnArchivo > 0) {
                        // Validar que el numero de nota este registrado en el sistema
                        fetch(`controller.php?validar-documentos=true`, {
                            method: "post",
                            body: JSON.stringify(docsNoAgregados),
                            headers: { "Content-type": "application/json" }
                        }).then(res => res.json()).then(res => {
                            const err404 = [];
                            const err502 = [];
                            res.forEach(doc => {
                                if (doc.status == "error") {
                                    switch (doc.code) {
                                        case 404:
                                            console.log("Err404: ", doc.data ?? "Empty");
                                            err404.push(doc.data);
                                            break;
                                        case 502:
                                            console.log("Err502: ", doc.data ?? "Empty");
                                            err502.push(doc.data);
                                            break;
                                    }
                                } else {
                                    console.log("DOC #: ", doc.documento);

                                    let abonosEnTabla = 0;

                                    document.querySelectorAll("#notas-entrega-tbl tbody tr").forEach(ne => {
                                        // Sumar abonos en tabla para evitar pasar el monto total de pago
                                        abonosEnTabla += parseFloat(ne.querySelector("td:nth-child(4)").innerText);
                                    })

                                    console.log("Total Abonos en Tabla: ", abonosEnTabla)

                                    utils.agregar_renglon_abonos(doc, totalPago, abonosEnTabla);
                                    document.getElementById("pagos-file-in").value = "";
                                    document.getElementById("numero-ne-in").focus();
                                }
                            });

                            if (docsDuplicadosEnArchivo.length > 0) {
                                utils.aviso_modal("warning", `
                                    No se agregaron los siguientes documentos: <br>
                                    <b>${docsDuplicadosEnArchivo.join(", ")}</b>
                                    <br> Estos se encuentran duplicados dentro del archivo de abonos y solo se permite un monto por documento.
                                    `);
                            }
                        }).catch(err => utils.aviso_modal("danger", err));
                    } else {
                        utils.aviso_modal("danger", "El monto de pago o el abono no pueden estar en cero o vacios.")
                    }
                } else {
                    utils.aviso_modal("warning", "Los documentos indicados ya han sido agregados previamente.");
                }
            }

        }, 2500);
    });

    // ======> Limpiar campos
    document.getElementById("reiniciar-campos-btn").addEventListener("click", (evt) => {
        evt.preventDefault();
        document.getElementById("numero-ne-in").value = "";
        document.getElementById("abono-ne-in").value = "";
    })

    // ENVIAR DATOS DEL FORMULARIO DE PAGOS
    document.getElementById("confirmar-enviar-pago-btn").addEventListener("click", (evt) => {
        $("#preloader-modal").modal("show");

        // Obtener ID del formulario activo y el endpoint de consulta para fetch al backend
        const frmId = sessionStorage.getItem("pagoFrm");
        let endpoint = null;
        switch (frmId) {
            case "vpos-frm":
                endpoint = "procesar-pago-vpos";
                break;
            case "p2p-frm":
                endpoint = "procesar-pago-p2p";
                break;
            case "c2p-frm":
                endpoint = "procesar-pago-c2p";
                break;
            case "reporte-pagos-frm":
                endpoint = "registrar-pagos-otros-bancos";
                break;
        }

        // Datos del formulario
        const inputs = Array.from(document.getElementById(frmId).elements);
        let data = {};
        data.notas = [];
        let procesar = true;
        let dif = false;

        // Generar Obj de datos
        inputs.forEach(inp => {
            // Autocompletar campos opcionales
            if (inp.id == "concepto") {
                inp.value = inp.value != "" ? inp.value : "Abonos";
            }

            // Validar que el campo no este deshabilitado
            if (inp.getAttribute("disabled") != "true") {
                // Validar que no existan campos vacios
                if (inp.value != "") {
                    if (inp.id == "cedula") {
                        // Agregar el tipo de documento al numero de cedula
                        const tipo = document.querySelector(`#${frmId} #tipo-doc`).value;
                        data[`${inp.id}`] = `${tipo}${inp.value}`;
                    } else if (inp.id != "tipo-doc") {
                        // Ignorar el tipo de documento
                        data[`${inp.id}`] = inp.value;
                    }

                    inp.classList.remove('invalid');
                } else {
                    inp.classList.add('invalid');
                    $("#preloader-modal").modal("hide");
                    procesar = false;
                }
            }

        })

        // Documentos agregados
        const filas = Array.from(document.querySelectorAll("#notas-entrega-tbl tbody tr"));
        if (filas.length > 0) {
            let abonos = 0;
            filas.forEach(row => {
                const numNE = row.querySelector("td:nth-child(1)").innerText;
                const monto = row.querySelector("td:nth-child(4)").innerText;
                data.notas.push({ num: numNE, monto: monto });
                abonos += parseFloat(monto);
            })

            // Evaluar si hay diferencia entre el monto de pago y el total de abonos
            const totalPago = parseFloat(document.querySelector(`#${frmId} #monto`).value);
            dif = totalPago != abonos ? true : false;
        } else {
            procesar = false;
        }

        // Cancelar el proceso de pago si el abono es menor al monto de pago
        if (!dif) {
            if (procesar) {
                data = JSON.stringify(data);

                // Autorizacion y formato
                const dat = atob(sessionStorage.getItem("sessionID")).split("-");
                const headers = new Headers({
                    "Authorization": "Basic " + btoa(`${dat[0]}:${dat[1]}`),
                    "Content-type": "application/json"
                });

                // Enviar datos de pago al endpoint adecuado en funcion al formulario o tipo de pago seleccionado
                fetch(`controller.php?${endpoint}=true`, {
                    method: "post",
                    body: data,
                    headers: headers
                }).then(res => res.json())
                    .then(res => {
                        $("#preloader-modal").modal("hide");

                        if (res.status == "success") {
                            if (res.code == 403) {
                                // Respuesta si el correo de transaccion exitosa no se envia al usuario
                                // pero el proceso se realizo sin problemas
                                utils.aviso_modal("warning", res.message);

                            } else {
                                // Respuesta si todo esta OK
                                utils.aviso_modal("success", res.message);
                            }

                            document.getElementById(frmId).reset();
                            document.querySelector("#notas-entrega-tbl tbody").innerHTML = "";
                        } else {
                            // Errores adicionales
                            utils.aviso_modal("danger", res.message);
                        }

                    }).catch(err => {
                        $("#preloader-modal").modal("hide");
                        utils.aviso_modal("danger", err);
                    }).finally(() => console.log("Proceso finalizado."))
            } else {
                $("#preloader-modal").modal("hide");
                utils.aviso_modal("warning", "Debe rellenar todos los campos y agregar al menos un numero de documento para poder continuar.")
            }
        } else {
            $("#preloader-modal").modal("hide");
            utils.aviso_modal("warning", "La suma total de abonos debe ser igual al monto total de pago.");
        }

    })

    // MOSTRAR EL HISTORIAL DE TRANSACCIONES
    document.querySelectorAll(".historial-op").forEach(btn => {
        btn.addEventListener("click", (evt) => {

            $("#preloader-modal").modal("show");

            // Autorizacion y formato
            const dat = atob(sessionStorage.getItem("sessionID")).split("-");

            // Registros para mostrar
            fetch("controller.php?historial-transacciones=true", {
                headers: { "Authorization": "Basic " + btoa(`${dat[0]}:${dat[1]}`) }
            }).then(res => res.json())
                .then(res => {
                    $("#preloader-modal").modal("hide");

                    if (res.status == "success") {
                        if (res.data != "") {
                            // Generar tabla de datos
                            const historial = new Promise((done, fail) => {
                                const tmp = utils.generar_tabla("resultado-div", "historial-tbl", res.data, true, "Mi_Historial_pagos_lior");
                                done(tmp);
                            })

                            // Activar codigo de detalles luego de cargar la tabla
                            historial.then(res => res ? utils.detalles_pago() : null);
                        } else {
                            document.getElementById("resultado-div").innerHTML = "<h4 class=text-warning>Ud. aun no posee pagos registrados.</h4>"
                        }
                    } else {
                        utils.aviso_modal("error", res.message);
                    }
                }).catch(err => {
                    $("#preloader-modal").modal("hide");
                    utils.aviso_modal("danger", err);
                }).finally(() => console.log("Proceso finalizado."))
        });
    })

} 
