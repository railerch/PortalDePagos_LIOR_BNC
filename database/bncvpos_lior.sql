-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-08-2024 a las 22:22:52
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Base de datos: `bncvpos_lior`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
    `id` int(11) NOT NULL,
    `nombre` varchar(250) NOT NULL,
    `nro_cedula` int(10) NOT NULL,
    `correo` varchar(250) NOT NULL,
    `nro_telf` varchar(16) NOT NULL,
    `clave` varchar(250) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO
    `clientes` (
        `id`,
        `nombre`,
        `nro_cedula`,
        `correo`,
        `nro_telf`,
        `clave`
    )
VALUES (
        5,
        'railer ch',
        16971775,
        'railer.chalbaud@gmail.com',
        '04129880890',
        '827ccb0eea8a706c4c34a16891f84e7b'
    ),
    (
        10,
        'jose angel sojo dasco',
        17514641,
        'jsojo@bosto.group',
        '04121803035',
        'e10adc3949ba59abbe56e057f20f883e'
    ),
    (
        11,
        'ricardo cazorla',
        18276796,
        'ricardo@bosto.group',
        '041200000000',
        'a906449d5769fa7361d7ecc6aa3f6d28'
    );

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pagos`
--

CREATE TABLE `detalles_pagos` (
    `id` int(11) NOT NULL,
    `documento` int(10) NOT NULL,
    `responsable` varchar(50) NOT NULL,
    `monto_us` decimal(10, 2) NOT NULL,
    `monto_bs` decimal(10, 2) NOT NULL,
    `abono_bs` decimal(10, 2) NOT NULL,
    `ref_interna` varchar(50) NOT NULL,
    `ref_bnc` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `detalles_pagos`
--

INSERT INTO
    `detalles_pagos` (
        `id`,
        `documento`,
        `responsable`,
        `monto_us`,
        `monto_bs`,
        `abono_bs`,
        `ref_interna`,
        `ref_bnc`
    )
VALUES (
        1,
        68336,
        'ARIANA YANIRECTH RIERA GOMEZ                      ',
        2992.57,
        108121.55,
        100.00,
        'R1723492147',
        12345678
    ),
    (
        2,
        68336,
        'ARIANA YANIRECTH RIERA GOMEZ                      ',
        2992.57,
        108121.55,
        100.00,
        'R1723492842',
        155630
    ),
    (
        3,
        68336,
        'ARIANA YANIRECTH RIERA GOMEZ                      ',
        2992.57,
        108121.55,
        100.00,
        'R1723493088',
        18823
    );

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_pagos`
--

CREATE TABLE `historial_pagos` (
    `id` int(11) NOT NULL,
    `fec_emis` datetime NOT NULL,
    `cliente` varchar(50) NOT NULL,
    `nro_cedula` int(15) NOT NULL,
    `banco` varchar(50) NOT NULL,
    `monto` decimal(10, 2) NOT NULL,
    `ref_lior` varchar(50) NOT NULL,
    `ref_banco` varchar(50) NOT NULL,
    `tipo_pago` varchar(10) NOT NULL,
    `concepto` varchar(250) NOT NULL,
    `estatus` tinyint(1) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `historial_pagos`
--

INSERT INTO
    `historial_pagos` (
        `id`,
        `fec_emis`,
        `cliente`,
        `nro_cedula`,
        `banco`,
        `monto`,
        `ref_lior`,
        `ref_banco`,
        `tipo_pago`,
        `concepto`,
        `estatus`
    )
VALUES (
        1,
        '2024-08-12 15:49:07',
        'Railer Ch',
        16971775,
        '191',
        100.00,
        'R1723492147',
        '12345678',
        'P2P-OTR',
        'Abonos',
        0
    ),
    (
        2,
        '2024-08-12 16:00:42',
        'Railer Ch',
        16971775,
        'N/A',
        100.00,
        'R1723492842',
        '155630',
        'DEB',
        'Abonos',
        1
    ),
    (
        3,
        '2024-08-12 16:04:49',
        'Railer Ch',
        16971775,
        '191',
        100.00,
        'R1723493088',
        '18823',
        'P2P',
        'Abonos',
        1
    );

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `nro_cedula` (`nro_cedula`);

--
-- Indices de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos` ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial_pagos`
--
ALTER TABLE `historial_pagos` ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 13;

--
-- AUTO_INCREMENT de la tabla `detalles_pagos`
--
ALTER TABLE `detalles_pagos`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT de la tabla `historial_pagos`
--
ALTER TABLE `historial_pagos`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;