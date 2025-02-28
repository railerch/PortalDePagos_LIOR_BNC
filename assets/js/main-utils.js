// AVISOS

export default class utils {
    constructor() {
    }

    static aviso_alert(tipo, mensaje) {
        let titulo;
        switch (tipo) {
            case "info":
                titulo = "INFORMACION!"
                break;
            case "success":
                titulo = "EXITO!"
                break;
            case "warning":
                titulo = "AVISO!"
                break;
            case "danger":
                titulo = "ERROR!"
                break;
        }

        alert(`${titulo}\n${mensaje}`);
    }

    static aviso_modal(tipo, mensaje) {
        let titulo, clsBody, clsTitle;
        switch (tipo) {
            case "info":
                titulo = "INFORMACION!"
                clsBody = "alert-info";
                clsTitle = "text-info"
                break;
            case "success":
                titulo = "EXITO!"
                clsBody = "alert-success";
                clsTitle = "text-success"
                break;
            case "warning":
                titulo = "AVISO!"
                clsBody = "alert-warning";
                clsTitle = "text-warning"
                break;
            case "danger":
                titulo = "ERROR!"
                clsBody = "alert-danger";
                clsTitle = "text-danger"
                break;
        }

        // Titulo
        let encabezado = document.querySelector("#avisos-modal .modal-header .modal-title");
        encabezado.className = "";
        encabezado.className = `modal-title ${clsTitle}`;
        encabezado.innerText = titulo;

        // Mensaje
        let contenido = document.querySelector("#avisos-modal .modal-body p");
        contenido.innerHTML = mensaje;

        $("#avisos-modal").modal("show");
    }

    static aviso_conex_modal(tipo, mensaje) {
        let titulo, clsBody, clsTitle;
        switch (tipo) {
            case "success":
                titulo = "EXITO!"
                clsBody = "alert-success";
                clsTitle = "text-success"
                break;
            case "danger":
                titulo = "ERROR!"
                clsBody = "alert-danger";
                clsTitle = "text-danger"
                break;
        }

        // Titulo
        let encabezado = document.querySelector("#aviso-conex-modal .modal-header .modal-title");
        encabezado.className = "";
        encabezado.className = `modal-title ${clsTitle}`;
        encabezado.innerText = titulo;

        // Mensaje
        let contenido = document.querySelector("#aviso-conex-modal .modal-body p");
        contenido.innerHTML = mensaje;

        $("#aviso-conex-modal").modal("show");
    }

    static cambiar_vistas(id) {
        switch (id) {
            case "aside-vpos-btn":
                sessionStorage.setItem("pagoFrm", "vpos-frm");
                document.getElementById("ne-div").style.display = "block";
                document.getElementById("vpos-div").style.display = "block";
                //document.getElementById("p2p-div").style.display = "none";
                document.getElementById("c2p-div").style.display = "none";
                document.getElementById("reporte-pmov-div").style.display = "none";
                document.getElementById("historial-div").style.display = "none";
                break;
            case "aside-p2p-btn":
                sessionStorage.setItem("pagoFrm", "p2p-frm");
                document.getElementById("ne-div").style.display = "block";
                //document.getElementById("p2p-div").style.display = "block";
                document.getElementById("vpos-div").style.display = "none";
                document.getElementById("c2p-div").style.display = "none";
                document.getElementById("reporte-pmov-div").style.display = "none";
                document.getElementById("historial-div").style.display = "none";
                break;
            case "aside-c2p-btn":
                sessionStorage.setItem("pagoFrm", "c2p-frm");
                document.getElementById("ne-div").style.display = "block";
                document.getElementById("c2p-div").style.display = "block";
                document.getElementById("vpos-div").style.display = "none";
                //document.getElementById("p2p-div").style.display = "none";
                document.getElementById("reporte-pmov-div").style.display = "none";
                document.getElementById("historial-div").style.display = "none";
                break;
            case "aside-report-btn":
                // Deshabilitar campo de telefono al reingresar al formulario
                document.querySelector("#reporte-pagos-frm #telefono-div").style.display = "none";;
                document.querySelector("#reporte-pagos-frm #telefono-div input").setAttribute("disabled", true);

                sessionStorage.setItem("pagoFrm", "reporte-pagos-frm");
                document.getElementById("ne-div").style.display = "block";
                document.getElementById("reporte-pmov-div").style.display = "block";
                document.getElementById("c2p-div").style.display = "none";
                document.getElementById("vpos-div").style.display = "none";
                //document.getElementById("p2p-div").style.display = "none";
                document.getElementById("historial-div").style.display = "none";
                break;
            case "aside-historial-btn":
                sessionStorage.removeItem("pagoFrm");
                document.getElementById("historial-div").style.display = "block";
                document.getElementById("ne-div").style.display = "none";
                document.getElementById("vpos-div").style.display = "none";
                //document.getElementById("p2p-div").style.display = "none";
                document.getElementById("c2p-div").style.display = "none";
                document.getElementById("reporte-pmov-div").style.display = "none";
                break;
        }
    }

    static generar_tabla(contenedorID, tablaID, datos, exportar = false, nombreArchivo = null) {
        const contenedor = document.getElementById(contenedorID);
        contenedor.innerHTML = "";

        // Wrapper de tabla
        const wrapper = document.createElement("div");
        wrapper.className = "mt-3"
        wrapper.style.maxHeight = "60vh";
        wrapper.style.overflowX = "auto";

        // Crear tabla
        const table = document.createElement("table");
        table.id = tablaID;
        table.className = "table table-hover";
        table.style.width = "100%";

        // Cabecera
        const cols = Object.keys(datos[0]);
        const tHead = document.createElement("thead");
        tHead.className = "table-secondary sticky-top top-0";

        // Columnas cabecera
        const trHead = document.createElement("tr");
        cols.forEach(col => {
            const td = document.createElement("td");
            td.className = "text-capitalize text-muted fw-bold";
            if (col != "ref_interna") {
                td.innerText = col.replace("_", " ");
                trHead.appendChild(td);
            }
        })
        tHead.appendChild(trHead);

        // Crear cuerpo
        const tBody = document.createElement("tbody");
        datos.forEach(row => {
            const tr = document.createElement("tr");
            tr.style.cursor = "pointer";
            tr.style.userSelect = "none";
            for (let col in row) {
                if (col == "ref_interna") {
                    tr.id = row[col];
                } else {
                    const td = document.createElement("td");
                    // Asignar un color al renglon segun el estatus en caso de existir dicha columna
                    if (col.toLowerCase() == "estatus") {
                        if (row[col] == 0) {
                            tr.className = "table-danger";
                            td.innerText = "Sin validar";
                        } else {
                            td.innerText = "Procesado";
                        }
                    } else {
                        td.innerText = row[col];
                    }
                    tr.appendChild(td);
                }
            }
            tBody.appendChild(tr);
        })

        // Filtro de tabla
        const filtroDiv = document.createElement("div");
        filtroDiv.className = "d-flex flex-wrap justify-content-between";

        const div1 = document.createElement("div");
        div1.className = "col-12 col-sm-4";

        const div2 = document.createElement("div");
        div2.className = "col-12 col-sm-4 text-end";

        const filtro = document.createElement("div");
        filtro.className = "input-group my-2";

        const filtroLabel = document.createElement("label");
        filtroLabel.className = "input-group-text";
        filtroLabel.innerHTML = "<i class='bi bi-filter'></i> Filtro";

        const filtroInput = document.createElement("input");
        filtroInput.className = "form-control";
        filtroInput.id = `${tablaID}-filtro`;

        const trashBtn = document.createElement("button");
        trashBtn.id = `${tablaID}-filtro-btn`;
        trashBtn.className = "btn btn-outline-danger";
        trashBtn.innerHTML = "<i class='bi bi-trash'></i>";
        trashBtn.style.display = "none";

        // Botones para exportar a excel
        if (exportar) {
            const txt = document.createElement("span");
            txt.textContent = "Exportar registros: ";

            const btnXlsx = document.createElement("button");
            btnXlsx.className = "mainBtn me-2";
            btnXlsx.id = "exportar-xlsx-btn";
            btnXlsx.textContent = "XLSX";
            btnXlsx.addEventListener("click", () => {
                this.exportar_tabla(tablaID, nombreArchivo, 'xlsx');
            })

            const btnCsv = document.createElement("button");
            btnCsv.className = "mainBtn";
            btnCsv.id = "exportar-csv-btn"
            btnCsv.textContent = "CSV";
            btnCsv.addEventListener("click", () => {
                this.exportar_tabla(tablaID, nombreArchivo, 'csv');
            })

            div2.append(txt, btnXlsx, btnCsv);
        }

        // Integrar
        filtro.append(filtroLabel, filtroInput, trashBtn);
        div1.appendChild(filtro);

        // Incorporar botones para exportar
        exportar ? filtroDiv.append(div1, div2) : filtroDiv.append(div1);

        table.append(tHead, tBody);
        wrapper.appendChild(table);
        contenedor.prepend(filtroDiv, wrapper);

        // Listener del filtro
        this.filtro_tablas(tablaID, `${tablaID}-filtro-btn`);

        return true;
    }

    static exportar_tabla(tableID, fileName, fileType) {
        const archivo = `${fileName}.${fileType}`;
        const tabla = document.getElementById(tableID);
        const wb = XLSX.utils.table_to_book(tabla);
        XLSX.writeFile(wb, archivo);
    }

    static detalles_pago() {
        const filas = document.querySelectorAll("#historial-tbl tbody tr");
        filas.forEach(fila => {
            fila.addEventListener("click", function () {
                $("#preloader-modal").modal("show");

                // Obtener notas y referencia del pago
                const ref = this.id;

                // Autorizacion y formato
                const dat = atob(sessionStorage.getItem("sessionID")).split("-");
                const headers = new Headers({
                    "Authorization": "Basic " + btoa(`${dat[0]}:${dat[1]}`),
                    "conten-type": "application/json"
                })

                // Peticion
                fetch(`controller.php?detalles-transaccion=true&ref=${ref}`, { headers: headers })
                    .then(res => res.json())
                    .then(res => {
                        // Mostrar resultados
                        $("#preloader-modal").modal("hide");
                        $("#detalles-pago-modal").modal("show");
                        document.querySelector("#detalles-pago-modal #ref-pago").innerText = ref;
                        utils.generar_tabla("detalles-pago-div", "detalles-pago-tbl", res.data);
                    }).catch(err => {
                        $("#preloader-modal").modal("hide");
                        utils.aviso_modal("danger", err)
                    })
            })
        })
    }

    static filtro_tablas(tablaID, limpiarBtn) {
        const limpiarFiltro = document.getElementById(limpiarBtn);

        document.getElementById(`${tablaID}-filtro`).addEventListener("keyup", (evt) => {
            const search = evt.target.value.toLowerCase();

            document.querySelectorAll(`#${tablaID} tbody tr`).forEach(tr => {
                if (!tr.innerText.toLowerCase().includes(search)) {
                    tr.style.display = "none";
                } else {
                    tr.style.display = "table-row";
                }
            })

            // Ocultar el boton de limpiar el filtro si este se encuentra vacio
            if (evt.target.value == "") {
                limpiarFiltro.style.display = "none";
            } else {
                limpiarFiltro.style.display = "inline-block";
            }

        })

        limpiarFiltro.addEventListener("click", (evt) => {
            limpiarFiltro.style.display = "none";
            document.getElementById(`${tablaID}-filtro`).value = "";
            document.querySelectorAll(`#${tablaID} tbody tr`).forEach(tr => {
                tr.style.display = "table-row";
            })
        })
    }

    reiniciar_tabla(tablaID) {
        // Reinicializar tabla de datos
        $(`#${tablaID}`).DataTable().clear();
        $(`#${tablaID}`).DataTable().destroy();
        $(`#${tablaID} tbody`).empty();
        document.getElementById(`${tablaID}`).append(document.createElement("tbody"));
    }

    static leer_archivo(fn) {
        const file = document.getElementById("pagos-file-in").files[0];
        if (file) {
            const reader = new FileReader();
            const validos = [];
            let duplicados = new Set();
            reader.onload = (evt) => {
                let res = evt.target.result;
                // Dividir el archivo en lineas
                let docs = res.split("\n");

                // Evitar las lineas vacias
                docs = docs.filter(doc => {
                    if (doc != ("" || "\r")) return doc;
                });

                // Llimpiar datos y mostrar
                docs = docs.map(d => {
                    d = d.replace("\r", "").replaceAll(" ", "").trim().replace(",", ".");
                    return d.split(";");
                });

                // Validar que no existan documentos duplicados
                docs.forEach(doc => {
                    let i = 0;
                    docs.forEach(tmp => {
                        doc[0] == tmp[0] ? i++ : null;
                    })

                    i > 1 ? duplicados.add(doc[0]) : validos.push(doc);
                })

                duplicados = Array.from(duplicados);
                fn({ duplicados, validos });

            }
            reader.readAsText(file);
        } else {
            fn(null);
        }
    }

    static agregar_renglon_abonos(doc, totalPago, abonosEnTabla) {
        // Agregar monto de abono actual
        const totalAbonos = abonosEnTabla + parseFloat(doc.abono);

        // Validar que los abonos no excedan el monto total de pago y agregar a la tabla de abonos
        if (totalAbonos <= totalPago) {
            const tbody = document.querySelector("#notas-entrega-tbl tbody");
            const tr = document.createElement("tr");
            tr.id = doc.documento;

            tr.innerHTML = `
                                    <td>${doc.documento}</td>
                                    <td>${doc.responsable}</td>
                                    <td>${doc.final}</td>
                                    <td>${doc.abono}</td>
                                    <td>
                                        <i class="btn btn-outline-secondary btn-sm bi bi-trash" onclick="(this.parentElement.parentElement.remove())">
                                    </td>`;

            tbody.appendChild(tr);
            console.log(`Documento #${doc.documento} agregado.`);
        } else {
            console.warn(`Documento #${doc.documento} NO agregado, el abono excede el monto total de pago.`);
            // monto sugerido para el ultimo abono en caso de superar el monto total de pago
            const abonoSugerido = parseFloat(totalPago - abonosEnTabla).toFixed(2);
            utils.aviso_modal("danger", `El total de abonos supera el monto total del pago, monto restante disponible para abonar ${abonoSugerido} Bs.`);
        }
    }

    static cerrar_sesion() {
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
    }

    static renovar_token_sesion(intentos, tiempoSesion) {
        fetch("controller.php?refrescar-token-de-sesion=true")
            .then(res => res.json())
            .then(res => {
                $("#preloader-modal").modal("hide");

                if (res.status == "OK") {
                    utils.aviso_modal("success", "Sesión reactivada correctamente!");

                    // Reactivar contador
                    setTimeout(() => {
                        $("#mantener-sesion-activa-modal").modal("show");
                    }, tiempoSesion)

                } else if (res.status == "KO") {
                    if (intentos < 5) {
                        ++intentos;
                        utils.aviso_modal("danger", `<span class="text-danger">Error al renovar la sesión!</span><br><b>Reactivacion automatica (${intentos} Intentos)</b>`);
                        utils.renovar_token_sesion(intentos, tiempoSesion);
                    } else {
                        utils.aviso_modal("warning", 'Se ha alcanzado el limite de intentos, se procedera a cerrar la sesión.');
                        utils.cerrar_sesion();
                    }
                } else {
                    utils.aviso_modal("danger", 'Error inesperado, se procedera con el cierre de sesión forzado.');
                    utils.cerrar_sesion();
                }
            })
            .catch(err => {
                utils.aviso_modal("danger", err);
                utils.cerrar_sesion();
            });
    }

    static consultar_tasa_bcv() {
        // Autorizacion y formato
        const dat = atob(sessionStorage.getItem("sessionID")).split("-");

        fetch("controller.php?consultar-tasa-bcv=true", {
            headers: { "Authorization": "Basic " + btoa(`${dat[0]}:${dat[1]}`) }
        })
            .then(res => res.json())
            .then(res => {
                document.querySelector("#tasa-bcv-badge span").innerText = res.data;
            })
    }
};
