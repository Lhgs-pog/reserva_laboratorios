<?php
/**
 * Favicon + Open Graph (WhatsApp, Telegram, redes sociais).
 * Logo oficial: uniceplac2.png
 */
require_once __DIR__ . '/../../Config/env.php';
app_load_env(dirname(__DIR__, 3));

$metaTitle = $pageTitle ?? 'UNICEPLAC — Central de Reservas Acadêmicas';
$metaDescription = $pageDescription ?? 'LabHub UNICEPLAC — sistema de reserva de laboratórios do Centro Universitário UNICEPLAC.';
$assetVersion = '20260619';
$baseUrl = app_base_url();
$logoUrl = $baseUrl . '/uniceplac2.png?v=' . $assetVersion;
$canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pageUrl = $baseUrl . $canonicalPath;
?>
<link rel="icon" type="image/png" href="uniceplac2.png?v=<?= htmlspecialchars($assetVersion, ENT_QUOTES, 'UTF-8') ?>">
<link rel="apple-touch-icon" href="uniceplac2.png?v=<?= htmlspecialchars($assetVersion, ENT_QUOTES, 'UTF-8') ?>">
<meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="LabHub UNICEPLAC">
<meta property="og:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image" content="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="UNICEPLAC Centro Universitário">
<meta property="og:url" content="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>">
