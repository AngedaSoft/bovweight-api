-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-06-2026 a las 06:52:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bovweight_dev`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acceso_compartido`
--

CREATE TABLE `acceso_compartido` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `finca_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `tipo_acceso` enum('lectura','edicion') NOT NULL DEFAULT 'lectura',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `animales`
--

CREATE TABLE `animales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `finca_id` bigint(20) UNSIGNED NOT NULL,
  `rebano_id` bigint(20) UNSIGNED DEFAULT NULL,
  `raza_id` bigint(20) UNSIGNED DEFAULT NULL,
  `arete_senasa` varchar(50) NOT NULL COMMENT 'Identificador oficial unico por animal',
  `fecha_asignacion_arete` date DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `fecha_nacimiento_aprox` date DEFAULT NULL,
  `sexo` enum('macho','hembra') NOT NULL,
  `estado` enum('activo','inactivo_vendido','inactivo_muerto','inactivo_traslado','inactivo_transferido') NOT NULL DEFAULT 'activo',
  `motivo_inactivacion` varchar(255) DEFAULT NULL,
  `fecha_inactivacion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `animales`
--

INSERT INTO `animales` (`id`, `finca_id`, `rebano_id`, `raza_id`, `arete_senasa`, `fecha_asignacion_arete`, `nombre`, `fecha_nacimiento_aprox`, `sexo`, `estado`, `motivo_inactivacion`, `fecha_inactivacion`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 'CRI-0001', '2025-06-15', 'Garu', '2022-06-13', 'macho', 'activo', NULL, NULL, '2026-06-15 21:29:19', '2026-06-15 23:41:08'),
(2, 1, 3, 1, 'CRI-0002', '2025-10-15', 'Pucca', '2022-10-17', 'hembra', 'activo', NULL, NULL, '2026-06-15 21:29:19', '2026-06-15 23:21:08'),
(3, 2, 1, 5, 'CRI-1234', '2026-06-15', 'La Pinta', '2025-09-15', 'hembra', 'activo', NULL, NULL, '2026-06-15 21:35:16', '2026-06-15 21:35:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correcciones_peso`
--

CREATE TABLE `correcciones_peso` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pesaje_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `peso_original_kg` decimal(8,2) NOT NULL,
  `peso_corregido_kg` decimal(8,2) NOT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `correcciones_peso`
--

INSERT INTO `correcciones_peso` (`id`, `pesaje_id`, `usuario_id`, `peso_original_kg`, `peso_corregido_kg`, `motivo`, `fecha`, `created_at`, `updated_at`) VALUES
(1, 10, 1, 263.00, 262.00, 'Se nos olvido quitarle el mecate y pesaba un kilo mas', '2026-06-15 16:57:14', '2026-06-15 22:57:14', '2026-06-15 22:57:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fincas`
--

CREATE TABLE `fincas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `propietario_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `provincia` varchar(255) NOT NULL,
  `canton` varchar(255) NOT NULL,
  `distrito` varchar(255) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fincas`
--

INSERT INTO `fincas` (`id`, `propietario_id`, `nombre`, `provincia`, `canton`, `distrito`, `fecha_creacion`, `created_at`, `updated_at`) VALUES
(1, 2, 'Hacienda Los Llanos', 'Guanacaste', 'Liberia', 'Liberia', '2026-06-15', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(2, 1, 'Finca Flor', 'Guanacaste', 'Tilarán', 'Quebrada Grande', '2026-06-15', '2026-06-15 21:32:28', '2026-06-15 21:32:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotografias`
--

CREATE TABLE `fotografias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pesaje_id` bigint(20) UNSIGNED NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL COMMENT 'Ruta o URL en DigitalOcean Spaces',
  `fecha_captura` datetime NOT NULL,
  `resolucion` varchar(20) DEFAULT NULL COMMENT 'Ej: 1920x1080',
  `es_valida` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si paso la validacion de nitidez y deteccion de bovino',
  `motivo_invalidez` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_05_01_000001_create_users_table', 1),
(2, '2026_05_01_000002_create_password_reset_tokens_table', 1),
(3, '2026_05_01_000003_create_cache_table', 1),
(4, '2026_05_01_000004_create_jobs_table', 1),
(5, '2026_05_01_000010_create_fincas_table', 1),
(6, '2026_05_01_000011_create_rebanos_table', 1),
(7, '2026_05_01_000012_create_razas_table', 1),
(8, '2026_05_01_000020_create_animales_table', 1),
(9, '2026_05_01_000030_create_pesajes_table', 1),
(10, '2026_05_01_000031_create_fotografias_table', 1),
(11, '2026_05_01_000032_create_correcciones_peso_table', 1),
(12, '2026_05_01_000040_create_acceso_compartido_table', 1),
(13, '2026_05_01_000041_create_transferencias_animal_table', 1),
(14, '2026_05_01_000050_create_recordatorios_pesaje_table', 1),
(15, '2026_05_01_000060_create_reportes_table', 1),
(16, '2026_05_01_000070_create_notificaciones_table', 1),
(17, '2026_05_01_000080_create_personal_access_tokens_table', 1),
(18, '2026_06_01_000001_add_avatar_url_to_users_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `tipo` varchar(50) NOT NULL COMMENT 'Ej: recordatorio_pesaje, transferencia_solicitada, reporte_listo',
  `mensaje` varchar(255) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Datos contextuales de la notificacion' CHECK (json_valid(`payload`)),
  `fecha_programada` datetime DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(5, 'App\\Models\\User', 1, 'mobile-app', '493b227bc6481524f6e887c23eaa5888ccfc59402323e34964ecd854c93ae009', '[\"*\"]', '2026-06-18 04:40:31', '2026-08-17 04:38:07', '2026-06-18 04:38:07', '2026-06-18 04:40:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pesajes`
--

CREATE TABLE `pesajes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `animal_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL,
  `peso_estimado_kg` decimal(8,2) NOT NULL,
  `rango_confianza_kg` decimal(6,2) DEFAULT NULL COMMENT 'Margen +/- en kg que devuelve el servicio de IA',
  `fue_corregido` tinyint(1) NOT NULL DEFAULT 0,
  `peso_corregido_kg` decimal(8,2) DEFAULT NULL,
  `tipo` enum('ia','manual') NOT NULL,
  `modelo_ia_version` varchar(50) DEFAULT NULL COMMENT 'Version del modelo YOLOv8 que ejecuto la inferencia',
  `tiempo_procesamiento_seg` int(11) DEFAULT NULL,
  `estado_procesamiento` enum('pendiente','procesada','fallida') DEFAULT NULL,
  `formula_zootecnica` varchar(50) DEFAULT NULL COMMENT 'Formula usada en pesaje manual: schaeffer, agarwal, regresion_local',
  `es_offline` tinyint(1) NOT NULL DEFAULT 0,
  `perimetro_toracico_cm` decimal(6,2) DEFAULT NULL,
  `largo_cuerpo_cm` decimal(6,2) DEFAULT NULL,
  `fecha_medicion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pesajes`
--

INSERT INTO `pesajes` (`id`, `animal_id`, `usuario_id`, `fecha`, `peso_estimado_kg`, `rango_confianza_kg`, `fue_corregido`, `peso_corregido_kg`, `tipo`, `modelo_ia_version`, `tiempo_procesamiento_seg`, `estado_procesamiento`, `formula_zootecnica`, `es_offline`, `perimetro_toracico_cm`, `largo_cuerpo_cm`, `fecha_medicion`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-02-15 16:55:01', 380.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:01', '2026-06-15 22:55:01'),
(2, 1, 1, '2026-03-17 16:55:01', 395.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:01', '2026-06-15 22:55:01'),
(3, 1, 1, '2026-04-16 16:55:01', 412.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:01', '2026-06-15 22:55:01'),
(4, 1, 1, '2026-05-16 16:55:01', 428.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:01', '2026-06-15 22:55:01'),
(5, 1, 1, '2026-06-15 16:55:01', 441.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:01', '2026-06-15 22:55:01'),
(6, 2, 1, '2026-02-15 16:55:02', 210.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(7, 2, 1, '2026-03-17 16:55:02', 224.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(8, 2, 1, '2026-04-16 16:55:02', 238.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(9, 2, 1, '2026-05-16 16:55:02', 251.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(10, 2, 1, '2026-06-15 16:55:02', 263.00, NULL, 1, 262.00, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:57:14'),
(11, 3, 1, '2026-02-15 16:55:02', 520.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(12, 3, 1, '2026-03-17 16:55:02', 538.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(13, 3, 1, '2026-04-16 16:55:02', 555.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(14, 3, 1, '2026-05-16 16:55:02', 571.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02'),
(15, 3, 1, '2026-06-15 16:55:02', 584.00, NULL, 0, NULL, 'manual', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-15 22:55:02', '2026-06-15 22:55:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `razas`
--

CREATE TABLE `razas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `parametros_morfologicos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Coeficientes especificos por raza para ajustar la formula zootecnica' CHECK (json_valid(`parametros_morfologicos`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `razas`
--

INSERT INTO `razas` (`id`, `nombre`, `parametros_morfologicos`, `created_at`, `updated_at`) VALUES
(1, 'BRAHMAN', '{\"factor_k\":10800,\"peso_min\":180,\"peso_max\":950}', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(2, 'BRANGUS', '{\"factor_k\":10500,\"peso_min\":180,\"peso_max\":900}', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(3, 'GYR', '{\"factor_k\":11000,\"peso_min\":170,\"peso_max\":850}', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(4, 'HOLSTEIN', '{\"factor_k\":10200,\"peso_min\":200,\"peso_max\":900}', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(5, 'JERSEY', '{\"factor_k\":10800,\"peso_min\":150,\"peso_max\":600}', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(6, 'CHAROLAIS', '{\"factor_k\":10400,\"peso_min\":200,\"peso_max\":1100}', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(7, 'ANGUS', '{\"factor_k\":10600,\"peso_min\":200,\"peso_max\":1000}', '2026-06-15 21:29:19', '2026-06-15 21:29:19'),
(8, 'CRIOLLO', '{\"factor_k\":10700,\"peso_min\":150,\"peso_max\":700}', '2026-06-15 21:29:19', '2026-06-15 21:29:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rebanos`
--

CREATE TABLE `rebanos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `finca_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `proposito` enum('terneros','vacas_ordeno','engorde','cria','otro') NOT NULL DEFAULT 'otro',
  `fecha_creacion` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rebanos`
--

INSERT INTO `rebanos` (`id`, `finca_id`, `nombre`, `proposito`, `fecha_creacion`, `created_at`, `updated_at`) VALUES
(1, 2, 'Rebaño De La Montaña', 'vacas_ordeno', '2026-06-15', '2026-06-15 21:33:34', '2026-06-15 21:33:34'),
(2, 2, 'Rebaño Del Río', 'cria', '2026-06-15', '2026-06-15 21:59:11', '2026-06-15 21:59:11'),
(3, 1, 'Rebaño Sur', 'terneros', '2026-06-10', '2026-06-15 23:20:14', '2026-06-15 23:20:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recordatorios_pesaje`
--

CREATE TABLE `recordatorios_pesaje` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `animal_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `frecuencia_dias` int(11) NOT NULL,
  `proxima_fecha` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `finca_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` enum('pdf','excel') NOT NULL,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `ruta_archivo` varchar(255) NOT NULL COMMENT 'Ruta o URL en DigitalOcean Spaces',
  `parametros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Filtros aplicados al generar el reporte' CHECK (json_valid(`parametros`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('TNHsjOXshFus0ccTHqaqMznTcqywKv9mGucrgUue', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSlV1cm5OS2pmbVZwSkZxMjROZll5Z1VZN2lhYjR0NFVDZ3gzejdNcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1781558790);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transferencias_animal`
--

CREATE TABLE `transferencias_animal` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `animal_id` bigint(20) UNSIGNED NOT NULL,
  `vendedor_id` bigint(20) UNSIGNED NOT NULL,
  `comprador_id` bigint(20) UNSIGNED NOT NULL,
  `finca_destino_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_aceptacion` datetime DEFAULT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `rol` enum('propietario','veterinario','administrador') NOT NULL DEFAULT 'propietario',
  `estado` enum('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `avatar_url` varchar(500) DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `nombre_completo`, `correo`, `contrasena_hash`, `rol`, `estado`, `avatar_url`, `fecha_registro`, `ultimo_acceso`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrador BovWeight', 'admin@bovweight.local', '$2y$12$tQcLdAmRn5cKVmS3JX1e4O1qB003hUYdf56V6N55oRrS/FvnAVHNK', 'administrador', 'activo', 'avatars/1/rqwKxFmkDp5poCXvlp52uPFoSlDM02oK9yiYmf78.jpg', '2026-06-15', '2026-06-17 22:38:07', NULL, '2026-06-15 21:29:19', '2026-06-18 04:38:07'),
(2, 'Don Juan Ganadero', 'ganadero@bovweight.local', '$2y$12$vCBo5n6kIeFQts7HngCKyO1jZD6HLDe7DhcMx6YBpyPZC5MgwaYSO', 'propietario', 'activo', NULL, '2026-06-15', '2026-06-15 17:46:41', NULL, '2026-06-15 21:29:19', '2026-06-15 23:46:41');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acceso_compartido`
--
ALTER TABLE `acceso_compartido`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_acceso_finca_usuario` (`finca_id`,`usuario_id`),
  ADD KEY `acceso_compartido_usuario_id_index` (`usuario_id`);

--
-- Indices de la tabla `animales`
--
ALTER TABLE `animales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `animales_arete_senasa_unique` (`arete_senasa`),
  ADD KEY `animales_rebano_id_foreign` (`rebano_id`),
  ADD KEY `animales_raza_id_foreign` (`raza_id`),
  ADD KEY `animales_finca_id_estado_index` (`finca_id`,`estado`),
  ADD KEY `animales_arete_senasa_index` (`arete_senasa`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `correcciones_peso`
--
ALTER TABLE `correcciones_peso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `correcciones_peso_usuario_id_foreign` (`usuario_id`),
  ADD KEY `correcciones_peso_pesaje_id_index` (`pesaje_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `fincas`
--
ALTER TABLE `fincas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fincas_propietario_id_index` (`propietario_id`);

--
-- Indices de la tabla `fotografias`
--
ALTER TABLE `fotografias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fotografias_pesaje_id_index` (`pesaje_id`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notificaciones_usuario_id_leida_index` (`usuario_id`,`leida`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `pesajes`
--
ALTER TABLE `pesajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesajes_usuario_id_foreign` (`usuario_id`),
  ADD KEY `pesajes_animal_id_fecha_index` (`animal_id`,`fecha`),
  ADD KEY `pesajes_tipo_index` (`tipo`);

--
-- Indices de la tabla `razas`
--
ALTER TABLE `razas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `razas_nombre_unique` (`nombre`);

--
-- Indices de la tabla `rebanos`
--
ALTER TABLE `rebanos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rebanos_finca_id_proposito_index` (`finca_id`,`proposito`);

--
-- Indices de la tabla `recordatorios_pesaje`
--
ALTER TABLE `recordatorios_pesaje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recordatorios_pesaje_animal_id_foreign` (`animal_id`),
  ADD KEY `recordatorios_pesaje_usuario_id_activo_proxima_fecha_index` (`usuario_id`,`activo`,`proxima_fecha`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reportes_finca_id_foreign` (`finca_id`),
  ADD KEY `reportes_usuario_id_fecha_generacion_index` (`usuario_id`,`fecha_generacion`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `transferencias_animal`
--
ALTER TABLE `transferencias_animal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transferencias_animal_animal_id_foreign` (`animal_id`),
  ADD KEY `transferencias_animal_finca_destino_id_foreign` (`finca_destino_id`),
  ADD KEY `transferencias_animal_comprador_id_estado_index` (`comprador_id`,`estado`),
  ADD KEY `transferencias_animal_vendedor_id_estado_index` (`vendedor_id`,`estado`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_correo_unique` (`correo`),
  ADD KEY `users_correo_index` (`correo`),
  ADD KEY `users_rol_index` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acceso_compartido`
--
ALTER TABLE `acceso_compartido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `animales`
--
ALTER TABLE `animales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `correcciones_peso`
--
ALTER TABLE `correcciones_peso`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fincas`
--
ALTER TABLE `fincas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `fotografias`
--
ALTER TABLE `fotografias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pesajes`
--
ALTER TABLE `pesajes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `razas`
--
ALTER TABLE `razas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `rebanos`
--
ALTER TABLE `rebanos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `recordatorios_pesaje`
--
ALTER TABLE `recordatorios_pesaje`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `transferencias_animal`
--
ALTER TABLE `transferencias_animal`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `acceso_compartido`
--
ALTER TABLE `acceso_compartido`
  ADD CONSTRAINT `acceso_compartido_finca_id_foreign` FOREIGN KEY (`finca_id`) REFERENCES `fincas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acceso_compartido_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `animales`
--
ALTER TABLE `animales`
  ADD CONSTRAINT `animales_finca_id_foreign` FOREIGN KEY (`finca_id`) REFERENCES `fincas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `animales_raza_id_foreign` FOREIGN KEY (`raza_id`) REFERENCES `razas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `animales_rebano_id_foreign` FOREIGN KEY (`rebano_id`) REFERENCES `rebanos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `correcciones_peso`
--
ALTER TABLE `correcciones_peso`
  ADD CONSTRAINT `correcciones_peso_pesaje_id_foreign` FOREIGN KEY (`pesaje_id`) REFERENCES `pesajes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `correcciones_peso_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `fincas`
--
ALTER TABLE `fincas`
  ADD CONSTRAINT `fincas_propietario_id_foreign` FOREIGN KEY (`propietario_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `fotografias`
--
ALTER TABLE `fotografias`
  ADD CONSTRAINT `fotografias_pesaje_id_foreign` FOREIGN KEY (`pesaje_id`) REFERENCES `pesajes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pesajes`
--
ALTER TABLE `pesajes`
  ADD CONSTRAINT `pesajes_animal_id_foreign` FOREIGN KEY (`animal_id`) REFERENCES `animales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesajes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `rebanos`
--
ALTER TABLE `rebanos`
  ADD CONSTRAINT `rebanos_finca_id_foreign` FOREIGN KEY (`finca_id`) REFERENCES `fincas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recordatorios_pesaje`
--
ALTER TABLE `recordatorios_pesaje`
  ADD CONSTRAINT `recordatorios_pesaje_animal_id_foreign` FOREIGN KEY (`animal_id`) REFERENCES `animales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recordatorios_pesaje_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_finca_id_foreign` FOREIGN KEY (`finca_id`) REFERENCES `fincas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reportes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transferencias_animal`
--
ALTER TABLE `transferencias_animal`
  ADD CONSTRAINT `transferencias_animal_animal_id_foreign` FOREIGN KEY (`animal_id`) REFERENCES `animales` (`id`),
  ADD CONSTRAINT `transferencias_animal_comprador_id_foreign` FOREIGN KEY (`comprador_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transferencias_animal_finca_destino_id_foreign` FOREIGN KEY (`finca_destino_id`) REFERENCES `fincas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transferencias_animal_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
