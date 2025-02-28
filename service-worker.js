const CACHE_STATICO = "cache-static-v1";
const CACHE_DINAMICO = "cache-dinamico-v1";

// ALMACENAR EL 'APP SHELL' EN CACHE AL INSTALAR
// Nota: El App Shell es todo lo necesario para que la app funcione
self.addEventListener("install", evt => {

    let cacheStatico = caches.open(CACHE_STATICO)
        .then(cache => {
            return cache.addAll([
                "/portalpagos_dev/index.php",
                "/portalpagos_dev/login.php",
                "/portalpagos_dev/main.php",
                "/portalpagos_dev/head.php",
                "/portalpagos_dev/reportes.php",
                
                "/portalpagos_dev/assets/css/main.css",
                "/portalpagos_dev/assets/css/main.css.map",
                "/portalpagos_dev/assets/css/mediaqueries.css",
                "/portalpagos_dev/assets/css/mediaqueries.css.map",
                
                "https://code.jquery.com/jquery-3.7.1.min.js",
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
                "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css",
                "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2?dd67030699838ea613ee6dbda90effa6",
                
                "/portalpagos_dev/assets/js/main.js",
                "/portalpagos_dev/assets/js/main-utils.js",
                "/portalpagos_dev/assets/js/install-app.js",
                "/portalpagos_dev/assets/js/codigo-bancos.json",
                "/portalpagos_dev/assets/js/sheet.js",

                "/portalpagos_dev/assets/img/liorLogoN.png",
                "/portalpagos_dev/assets/img/liorLogoB.png",
                "/portalpagos_dev/assets/img/liorLogoB2.png",
                "/portalpagos_dev/assets/img/favicon.ico",
                "/portalpagos_dev/assets/img/bg01.jpg",
                "/portalpagos_dev/assets/img/preloader.gif"
            ])
        })

    // Ejecutar la instalacion una vez el trabajo con la cache este listo
    evt.waitUntil(cacheStatico);
});

// MANEJAR PETICIONES FETCH
self.addEventListener("fetch", evt => {
    // console.log(evt);
    // console.log(evt.request);

    // cache dinamica (Network Fallback)
    let cacheDinamico = caches.match(evt.request)
        .then(res => {
            // Si existe el archivo en cache se retorna
            if (res) return res;

            // No existe el archivo y hay que recuperarlo desde la web
            console.log("Erro 404: " + evt.request.url);

            // Se solicita nuevamente y se reemplaza la solicitud en cache con la nueva peticion 
            return fetch(evt.request).then(newRes => {
                caches.open(CACHE_DINAMICO).then(cache => {
                    cache.put(evt.request, newRes);
                })

                return newRes.clone();
            })

        })

    evt.waitUntil(cacheDinamico);
})