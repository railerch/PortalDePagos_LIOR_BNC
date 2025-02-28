window.onload = () => {
    // ACTIVAR EL SERVICE WORKER DE LA APP
    // Validar que no este un SW cargado previamente
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("/serviceWorker.js")
            .then(res => console.log("ServiceWorker registrado."))
            .catch(err => console.log("ServiceWorker NO registrado.", err))
    }
}

// BOTON DE INSTALACION PARA LA PWA

// Variable global que tendra la captura del evento 'beforeinstallprompt'
let deferredPrompt;

/* Boton de instalacion, con un 'display=none' para solo mostrarlo cuando
el evento 'beforeinstallpromt' sea activado, esto limitara la visualizacion
del boton solo en aquellos navegadores compatibles con la instalacion de PWA */
let installLnk = document.getElementById("install-txt");

// Captura del evento ya mencionado
window.addEventListener("beforeinstallprompt", function (evt) {
    evt.preventDefault();
    deferredPrompt = evt;
    installLnk.style.display = "inline";
})

// Configuracion del boton que activara el evento cuando sea requerido por el usuario
installLnk.addEventListener("click", async function () {
    // Validar que el evento haya sido capturado
    if (deferredPrompt !== null) {
        // Mostrar la ventana emergente de instalacion
        deferredPrompt.prompt();
        // Esperar por la eleccion del usuario
        const { outcome } = await deferredPrompt.userChoice;

        // Si escoge instalar outcome es 'accepted' de lo contrario sera 'dismiss'
        if (outcome === "accepted") {
            deferredPrompt = null;
        }
    }
})