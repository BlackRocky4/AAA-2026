<?php
require __DIR__ . '/config.php';
iniciar_sesion();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder($datos, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function lista_materiales() {
    $salida = [];
    foreach (MATERIALES as $m) {
        $salida[] = ['id' => $m['id'], 'titulo' => $m['titulo'], 'miniatura' => $m['miniatura']];
    }
    return $salida;
}

// Evita que Google Sheets interprete un texto como fórmula
function celda($texto) {
    return preg_match('/^[=+\-@\t\r]/', $texto) ? "'" . $texto : $texto;
}

// ---------- GET: ¿ya se registró esta sesión? ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_SESSION['registrado'])) {
        responder(['registrado' => true, 'materiales' => lista_materiales()]);
    }
    if (empty($_SESSION['t0'])) {
        $_SESSION['t0'] = time(); // para medir cuánto tardó en llenar el formulario
    }
    responder(['registrado' => false]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

// ---------- Antispam ----------
if (!empty($_POST['web'])) { // honeypot
    responder(['ok' => false, 'error' => 'No pudimos procesar tu solicitud.'], 400);
}
$_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
if ($_SESSION['intentos'] > 6) {
    responder(['ok' => false, 'error' => 'Demasiados intentos. Intenta más tarde.'], 429);
}
if (empty($_SESSION['t0']) || (time() - $_SESSION['t0']) < 3) {
    responder(['ok' => false, 'error' => 'Envío demasiado rápido. Intenta de nuevo.'], 400);
}

// ---------- Validación ----------
$nombre      = trim($_POST['nombre'] ?? '');
$edad        = filter_var($_POST['edad'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 120]]);
$sexo        = $_POST['sexo'] ?? '';
$procedencia = trim($_POST['procedencia'] ?? '');
$perfil      = $_POST['perfil'] ?? '';
$consiente   = ($_POST['consentimiento'] ?? '') === '1';

$errores = [];
if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100 || !preg_match("/^[\p{L}\p{M}\s.'-]+$/u", $nombre)) {
    $errores[] = 'Escribe tu nombre completo.';
}
if ($edad === false) {
    $errores[] = 'Escribe una edad válida.';
}
if (!in_array($sexo, SEXOS, true)) {
    $errores[] = 'Selecciona una opción de sexo.';
}
if (mb_strlen($procedencia) < 2 || mb_strlen($procedencia) > 100 || !preg_match("/^[\p{L}\p{M}\p{N}\s.,'-]+$/u", $procedencia)) {
    $errores[] = 'Indica desde dónde nos visitas.';
}
if (!in_array($perfil, PERFILES, true)) {
    $errores[] = 'Selecciona tu perfil.';
}
if (!$consiente) {
    $errores[] = 'Debes aceptar el aviso de privacidad.';
}
if ($errores) {
    responder(['ok' => false, 'error' => $errores[0]], 422);
}

// ---------- Envío a Google Sheets (vía Apps Script) ----------
$payload = [
    'secret'      => APPS_SCRIPT_SECRET,
    'nombre'      => celda($nombre),
    'edad'        => $edad,
    'sexo'        => $sexo,
    'procedencia' => celda($procedencia),
    'perfil'      => $perfil,
];

$ch = curl_init(APPS_SCRIPT_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true, // Apps Script responde con una redirección
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_TIMEOUT        => 20,
]);
$respuesta = curl_exec($ch);
$errorCurl = curl_error($ch);
curl_close($ch);

$json = ($respuesta !== false) ? json_decode($respuesta, true) : null;
if (!is_array($json) || empty($json['ok'])) {
    error_log('[registro.php] Fallo al guardar en Sheets: ' . $errorCurl . ' | ' . substr((string)$respuesta, 0, 200));
    responder(['ok' => false, 'error' => 'No pudimos guardar tus datos. Intenta de nuevo en unos minutos.'], 502);
}

// ---------- Éxito: se habilita la sesión para descargar ----------
session_regenerate_id(true);
$_SESSION['registrado'] = true;
responder(['ok' => true, 'materiales' => lista_materiales()]);
