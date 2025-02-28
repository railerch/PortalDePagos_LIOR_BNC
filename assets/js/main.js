import utils from "./main-utils.js";
import codBancos from "./codigo-bancos.json" with {type: "json"};

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
    }else{
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

if (window.location.pathname.includes("login")) {
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

} else if (window.location.pathname.includes("register")) {
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

} else if (window.location.pathname.includes("pass-request-frm")) {
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
} else if (window.location.pathname.includes("pass-change")) {
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
} else if (window.location.pathname.includes("main")) {
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

} else if (window.location.pathname.includes("reportes")) {
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
