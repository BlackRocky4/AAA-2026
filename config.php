<?php
// ============================================================
// CONFIGURACIÓN — solo constantes, no imprime nada
// ============================================================

// URL de la aplicación web de Google Apps Script (termina en /exec)
const APPS_SCRIPT_URL = 'https://script.google.com/macros/s/AKfycbzNgoHY9YqfVNQNC--Frr_In9WCjqUlrcZKLdE-vrG1FOgO_xCy60gpvf8H46vy8ZIe/exec';

// Clave compartida con el Apps Script. Debe ser IDÉNTICA en ambos lados.
const APPS_SCRIPT_SECRET = 'AlzheimerAprendeyActua2026-3clue';

// Materiales. 'archivo' vive en /materiales-privados/ (bloqueada al público).
// 'miniatura' sí es pública (una imagen que no hay problema en exponer).
const MATERIALES = [
    ['id' => 'cuidados-paliativos','titulo' => 'Guía de cuidados paliativos',  'archivo' => 'cuidados-paliativos.pdf','miniatura' => 'img/materiales/cuidados-paliativos.webp'],
    ['id' => 'guia-decuidados','titulo' => 'Guía de cuidados personas con demencia',  'archivo' => 'guia-decuidados.pdf','miniatura' => 'img/materiales/guia-decuidados.webp'],
    ['id' => 'guia-informativa', 'titulo' => 'Guia informativa para familiares y cuidadores',   'archivo' => 'guia-informativa.pdf', 'miniatura' => 'img/materiales/guia-informativa.webp'],
    ['id' => '10senales-alerta', 'titulo' => '10 Señales de alerta',   'archivo' => '10senales-alerta.pdf', 'miniatura' => 'img/materiales/10senales-alerta.webp'],
    ['id' => 'cuida-tucrerebro',  'titulo' => 'Cuida tu cerebro',      'archivo' => 'cuida-tucrerebro.pdf',  'miniatura' => 'img/materiales/cuida-tucrerebro.webp'],
    ['id' => 'notas-senales',  'titulo' => '¿Notaste una señal?',      'archivo' => 'notas-senales.pdf',  'miniatura' => 'img/materiales/notas-senales.webp'],
    ['id' => 'delirium', 'titulo' => 'Dilirium',   'archivo' => 'delirium.pdf', 'miniatura' => 'img/materiales/delirium.webp'],
    ['id' => 'evitando-riesgos',  'titulo' => 'Evitando riesgos',      'archivo' => 'evitando-riesgos.pdf',  'miniatura' => 'img/materiales/evitando-riesgos.webp'],   
    ['id' => 'sindrome-crepuscular','titulo' => 'Síndorme crepuscular',  'archivo' => 'sindrome-crepuscular.pdf','miniatura' => 'img/materiales/sindrome-crepuscular.webp'],
    ['id' => 'temporada-decalor', 'titulo' => 'Temporada de calor',   'archivo' => 'temporada-decalor.pdf', 'miniatura' => 'img/materiales/temporada-decalor.webp'],
    ['id' => 'temporada-defrio',  'titulo' => 'Temporada de frio',      'archivo' => 'temporada-defrio.pdf',  'miniatura' => 'img/materiales/temporada-defrio.webp'],
];

const PERFILES = ['Cuidador', 'Estudiante', 'Familiar', 'Profesional de la salud', 'Público en general'];
const SEXOS    = ['Mujer', 'Hombre', 'Otro'];

function iniciar_sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
    }
}
