<?php
require __DIR__ . '/config.php';
iniciar_sesion();

if (empty($_SESSION['registrado'])) {
    http_response_code(403);
    exit('Acceso restringido. Regístrate desde el menú "Material descargable".');
}

$id = $_GET['id'] ?? '';
$material = null;
foreach (MATERIALES as $m) {
    if ($m['id'] === $id) { $material = $m; break; }
}
if (!$material) {
    http_response_code(404);
    exit('Material no encontrado.');
}

$ruta = __DIR__ . '/materiales-privados/' . basename($material['archivo']);
if (!is_file($ruta)) {
    http_response_code(404);
    exit('Archivo no disponible.');
}

$tipos = [
    'pdf'  => 'application/pdf',
    'zip'  => 'application/zip',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];
$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

header('Content-Type: ' . ($tipos[$ext] ?? 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
header('Content-Length: ' . filesize($ruta));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

session_write_close();
readfile($ruta);
exit;
