<?php
require_once __DIR__ . '/../../Config/csrf_helpers.php';
$csrfToken = labhub_csrf_token();
?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
<script src="js/labhub-csrf.js?v=20260618"></script>
