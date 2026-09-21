/* Popups de "Material descargable": registro + listado.
   Requiere Bootstrap 5 (bundle) cargado ANTES que este archivo.
   Uso: en el menú, agrega el atributo data-materiales a los enlaces. */
(function () {
  'use strict';

  var ENDPOINT = 'registro.php';
  var DESCARGA = 'descargar.php?id=';

  var css = [
    '.mat-card{border:1px solid rgba(0,0,0,.08);border-radius:var(--radius-sm,20px);overflow:hidden;background:#fff;height:100%;display:flex;flex-direction:column}',
    '.mat-card img{width:100%;aspect-ratio:4/3;object-fit:cover;background:var(--aaa-cream,#f7f3ef)}',
    '.mat-card .mat-body{padding:.9rem;display:flex;flex-direction:column;gap:.7rem;flex:1;justify-content:space-between}',
    '.mat-card h6{margin:0;font-size:.9rem;color:var(--aaa-purple-dark,#4a2260);font-weight:600}',
    '.mat-card .btn-aaa-primary{width:100%;text-align:center;padding:.5rem 1rem;font-size:.85rem;text-decoration:none;display:inline-block}',
    '.rg-hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}'
  ].join('');

  var html = '' +
  '<div class="modal fade" id="modalRegistro" tabindex="-1" aria-labelledby="modalRegistroTitulo" aria-hidden="true">' +
    '<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0">' +
      '<div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>' +
      '<div class="modal-body px-4 pb-4">' +
        '<h4 id="modalRegistroTitulo" class="font-accent mb-1" style="color:var(--aaa-purple-dark)">Antes de descargar</h4>' +
        '<p class="small text-muted mb-3">Cuéntanos un poco sobre ti para acceder al material.</p>' +
        '<form id="formRegistro" novalidate>' +
          '<div class="mb-3"><label for="rg-nombre" class="form-label small fw-semibold">Nombre completo</label>' +
            '<input type="text" class="form-control" id="rg-nombre" name="nombre" required minlength="3" maxlength="100" autocomplete="name"></div>' +
          '<div class="mb-3"><label for="rg-edad" class="form-label small fw-semibold">Edad</label>' +
            '<input type="number" class="form-control" id="rg-edad" name="edad" required min="1" max="120" inputmode="numeric"></div>' +
          '<div class="mb-3"><span class="form-label small fw-semibold d-block">Sexo</span>' +
            '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="sexo" id="rg-sx1" value="Mujer" required><label class="form-check-label small" for="rg-sx1">Mujer</label></div>' +
            '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="sexo" id="rg-sx2" value="Hombre"><label class="form-check-label small" for="rg-sx2">Hombre</label></div>' +
            '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="sexo" id="rg-sx3" value="Otro"><label class="form-check-label small" for="rg-sx3">Otro</label></div></div>' +
          '<div class="mb-3"><label for="rg-proc" class="form-label small fw-semibold">¿Desde dónde nos ves o visitas?</label>' +
            '<input type="text" class="form-control" id="rg-proc" name="procedencia" required minlength="2" maxlength="100" placeholder="Ciudad, estado o país"></div>' +
          '<div class="mb-3"><label for="rg-perfil" class="form-label small fw-semibold">Soy</label>' +
            '<select class="form-select" id="rg-perfil" name="perfil" required>' +
              '<option value="" selected disabled>Selecciona una opción</option>' +
              '<option>Cuidador</option><option>Estudiante</option><option>Familiar</option>' +
              '<option>Profesional de la salud</option><option>Público en general</option>' +
            '</select></div>' +
          '<div class="rg-hp" aria-hidden="true"><label>Web <input type="text" name="web" tabindex="-1" autocomplete="off"></label></div>' +
          '<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="rg-consent" name="consentimiento" value="1" required>' +
            '<label class="form-check-label small" for="rg-consent">He leído y acepto el <a href="aviso-de-privacidad.html" target="_blank" rel="noopener" style="color:var(--aaa-purple)">aviso de privacidad</a>.</label></div>' +
          '<div id="rg-error" class="alert alert-danger py-2 small d-none" role="alert"></div>' +
          '<button type="submit" class="btn-aaa-primary w-100" id="rg-enviar">Continuar</button>' +
        '</form>' +
      '</div></div></div></div>' +

  '<div class="modal fade" id="modalMateriales" tabindex="-1" aria-labelledby="modalMaterialesTitulo" aria-hidden="true">' +
    '<div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content border-0">' +
      '<div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>' +
      '<div class="modal-body px-4 pb-4">' +
        '<h4 id="modalMaterialesTitulo" class="font-accent mb-1" style="color:var(--aaa-purple-dark)">Material descargable</h4>' +
        '<p class="small text-muted mb-4">Gracias por registrarte. Elige el material que quieras descargar.</p>' +
        '<div class="row g-3" id="listaMateriales"></div>' +
      '</div></div></div></div>';

  function init() {
    var estilo = document.createElement('style');
    estilo.textContent = css;
    document.head.appendChild(estilo);
    document.body.insertAdjacentHTML('beforeend', html);

    var elReg  = document.getElementById('modalRegistro');
    var elMat  = document.getElementById('modalMateriales');
    var modalReg = bootstrap.Modal.getOrCreateInstance(elReg);
    var modalMat = bootstrap.Modal.getOrCreateInstance(elMat);
    var form   = document.getElementById('formRegistro');
    var errBox = document.getElementById('rg-error');
    var btn    = document.getElementById('rg-enviar');

    function pintar(materiales) {
      var cont = document.getElementById('listaMateriales');
      cont.textContent = '';
      materiales.forEach(function (m) {
        var col  = document.createElement('div'); col.className = 'col-6 col-md-4';
        var card = document.createElement('div'); card.className = 'mat-card';
        var img  = document.createElement('img'); img.src = m.miniatura; img.alt = m.titulo; img.loading = 'lazy';
        var body = document.createElement('div'); body.className = 'mat-body';
        var h    = document.createElement('h6');  h.textContent = m.titulo;
        var a    = document.createElement('a');   a.className = 'btn-aaa-primary';
        a.href = DESCARGA + encodeURIComponent(m.id);
        a.innerHTML = '<i class="fa-solid fa-download me-1"></i> Descargar';
        body.appendChild(h); body.appendChild(a);
        card.appendChild(img); card.appendChild(body);
        col.appendChild(card); cont.appendChild(col);
      });
    }

    function abrir() {
      fetch(ENDPOINT, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.registrado) { pintar(d.materiales); modalMat.show(); }
          else { modalReg.show(); }
        })
        .catch(function () { modalReg.show(); });
    }

    document.addEventListener('click', function (e) {
      var enlace = e.target.closest('[data-materiales]');
      if (!enlace) return;
      e.preventDefault();
      abrir();
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      errBox.classList.add('d-none');
      form.classList.add('was-validated');
      if (!form.checkValidity()) return;

      btn.disabled = true;
      btn.textContent = 'Enviando…';

      fetch(ENDPOINT, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.ok) {
            pintar(d.materiales);
            elReg.addEventListener('hidden.bs.modal', function once() {
              elReg.removeEventListener('hidden.bs.modal', once);
              modalMat.show();
            });
            modalReg.hide();
            form.reset();
            form.classList.remove('was-validated');
          } else {
            errBox.textContent = d.error || 'Ocurrió un error. Intenta de nuevo.';
            errBox.classList.remove('d-none');
          }
        })
        .catch(function () {
          errBox.textContent = 'No pudimos conectar con el servidor. Intenta de nuevo.';
          errBox.classList.remove('d-none');
        })
        .then(function () { btn.disabled = false; btn.textContent = 'Continuar'; });
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
