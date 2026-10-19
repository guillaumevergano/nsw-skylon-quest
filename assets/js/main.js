(function () {
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Pluie de baskets : crée des chaussures qui tombent sur tout l'écran pendant `duration` ms.
  function shoeRain(duration) {
    if (reduceMotion) return;
    var layer = document.createElement('div');
    layer.className = 'rain';
    layer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(layer);
    var count = Math.min(60, Math.max(24, Math.round(window.innerWidth / 28)));
    for (var i = 0; i < count; i++) {
      var shoe = document.createElement('img');
      shoe.src = 'assets/img/shoe-small.webp';
      shoe.alt = '';
      shoe.className = 'rain__shoe';
      var size = 48 + Math.random() * 90;
      shoe.style.width = size + 'px';
      shoe.style.left = (Math.random() * 100) + 'vw';
      shoe.style.animationDuration = (1.8 + Math.random() * 1.8) + 's';
      shoe.style.animationDelay = (Math.random() * (duration / 1000 - 1.5)) + 's';
      shoe.style.setProperty('--r0', (Math.random() * 360) + 'deg');
      shoe.style.setProperty('--r1', (Math.random() * 720 - 360) + 'deg');
      shoe.style.setProperty('--flip', Math.random() > .5 ? '1' : '-1');
      layer.appendChild(shoe);
    }
    setTimeout(function () { layer.classList.add('is-done'); }, duration);
    setTimeout(function () { layer.remove(); }, duration + 1200);
  }

  // Page merci (repli sans JavaScript) : pluie au chargement.
  if (document.body.classList.contains('page-merci')) {
    shoeRain(4500);
  }

  var form = document.querySelector('.form');
  if (!form) return;

  // Affiche les erreurs renvoyées par le serveur sous les champs concernés.
  function showErrors(errors) {
    clearErrors();
    var panel = form.closest('.panel');
    var msg = errors.global || 'Quelques champs sont à corriger ci-dessous.';
    var alert = document.createElement('p');
    alert.className = 'alert';
    alert.setAttribute('role', 'alert');
    alert.textContent = msg;
    form.parentNode.insertBefore(alert, form);
    var firstField = null;
    Object.keys(errors).forEach(function (k) {
      if (k === 'global') return;
      var el = form.querySelector('[name="' + k + '"]');
      if (!el) return;
      el.setAttribute('aria-invalid', 'true');
      el.setAttribute('aria-describedby', 'err-' + k);
      var p = document.createElement('p');
      p.className = 'field__error';
      p.id = 'err-' + k;
      p.textContent = errors[k];
      el.closest('.field').appendChild(p);
      if (!firstField) firstField = el;
    });
    if (firstField) firstField.focus();
    if (panel) panel.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
  }
  function clearErrors() {
    var panel = form.closest('.panel');
    panel.querySelectorAll('.alert:not(.alert--info)').forEach(function (a) { a.remove(); });
    form.querySelectorAll('.field__error').forEach(function (p) { p.remove(); });
    form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
  }

  // Met à jour les places restantes dans le sélecteur d'équipe.
  function updateTeams(remaining) {
    var select = form.querySelector('[name="equipe"]');
    if (!select || !remaining) return;
    Array.prototype.forEach.call(select.options, function (opt) {
      if (!opt.value || !(opt.value in remaining)) return;
      var n = remaining[opt.value];
      opt.disabled = n <= 0;
      opt.textContent = opt.value + ' — ' + (n <= 0 ? 'complète' : n + ' place' + (n > 1 ? 's' : ''));
      if (opt.disabled && opt.selected) select.value = '';
    });
  }

  // Remplace le formulaire par la vue « merci », sans quitter la page (la pluie continue).
  function showMerci(waitlist) {
    var tpl = document.getElementById('tpl-merci');
    var main = document.querySelector('main');
    if (!tpl || !main) { window.location.href = 'merci.php'; return; }
    var view = tpl.content.cloneNode(true);
    view.querySelector('[data-merci="' + (waitlist ? 'attente' : 'equipe') + '"]').hidden = false;
    main.replaceWith(view);
    document.body.classList.remove('page-inscription');
    document.body.classList.add('page-merci');
    document.title = document.title.replace(/^[^—]+—/, 'Merci —');
    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
  }

  var sending = false;
  var btn = form.querySelector('.btn--submit');
  var btnLabel = btn ? btn.textContent : '';

  function setSending(on) {
    sending = on;
    if (!btn) return;
    btn.disabled = on;
    btn.textContent = on ? 'Envoi en cours…' : btnLabel;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (sending) return;
    if (!form.checkValidity()) {
      form.querySelectorAll(':invalid').forEach(function (el) { el.setAttribute('aria-invalid', 'true'); });
      var first = form.querySelector(':invalid');
      if (first) first.focus();
      return;
    }
    if (!window.fetch || !window.FormData) { form.submit(); return; }

    setSending(true);
    shoeRain(5500);
    var started = Date.now();

    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' }
    }).then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    }).then(function (data) {
      if (data.ok) {
        // On laisse la pluie s'installer avant de basculer sur la vue merci.
        var wait = Math.max(0, 900 - (Date.now() - started));
        setTimeout(function () { showMerci(!!data.waitlist); }, wait);
        return;
      }
      setSending(false);
      updateTeams(data.remaining);
      showErrors(data.errors || {});
    }).catch(function () {
      // Réseau ou réponse inattendue : envoi classique avec rechargement.
      sending = false;
      form.submit();
    });
  });

  form.querySelectorAll('input, select').forEach(function (el) {
    var clear = function () { if (el.checkValidity()) el.removeAttribute('aria-invalid'); };
    el.addEventListener('input', clear);
    el.addEventListener('change', clear);
  });
})();
