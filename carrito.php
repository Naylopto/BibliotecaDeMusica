<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Carrito | Instrumentarium</title>
  <link rel="stylesheet" href="Bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="CSS/Asset/CSS/estilos.css">
  <style>
    body {
      background: #f3f4f6;
      color: #252525
    }

    .cart-page {
      max-width: 1080px;
      min-height: 65vh
    }

    .cart-heading {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.25rem
    }

    .cart-panel {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      overflow: hidden
    }

    .cart-select-all {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid #ddd;
      font-size: 1.1rem;
      font-weight: 600
    }

    .cart-line {
      display: grid;
      grid-template-columns: 28px 96px minmax(0, 1fr) auto;
      gap: 14px;
      align-items: center;
      padding: 1rem 1.25rem;
      border-bottom: 1px solid #e1e1e1
    }

    .cart-line img,
    .cart-image-placeholder {
      width: 96px;
      height: 96px;
      border-radius: 12px;
      object-fit: contain;
      background: #f1f1f1
    }

    .cart-item-name {
      font-size: 1.05rem;
      font-weight: 500;
      margin: 0 0 .25rem
    }

    .cart-item-meta {
      color: #777;
      font-size: .9rem
    }

    .cart-quantity {
      width: 96px;
      margin-top: .65rem
    }

    .cart-line-price {
      text-align: right;
      white-space: nowrap
    }

    .cart-price {
      font-size: 1.25rem;
      font-weight: 650
    }

    .cart-remove {
      border: 0;
      background: transparent;
      color: #666;
      margin-top: .4rem
    }

    .cart-shipping {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid #ddd
    }

    .cart-progress {
      height: 6px;
      background: #d8efe2;
      border-radius: 8px;
      margin: .75rem 0;
      overflow: hidden
    }

    .cart-progress span {
      display: block;
      height: 100%;
      width: 100%;
      background: #159b5a
    }

    .cart-summary {
      position: sticky;
      bottom: 0;
      z-index: 2;
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      margin-top: 1rem;
      box-shadow: 0 -5px 18px #00000012;
      padding: 1rem 1.25rem
    }

    .cart-total {
      font-size: 1.7rem;
      font-weight: 700;
      line-height: 1.1
    }

    .cart-continue {
      min-height: 54px;
      background: #3483fa;
      border-color: #3483fa;
      color: #fff;
      font-size: 1.1rem;
      font-weight: 600
    }

    .cart-continue:hover {
      background: #2968c8;
      color: #fff
    }

    .cart-page .form-select {
      background-color: #fff;
      color: #333;
      border-color: #ccc
    }

    .empty-cart {
      padding: 3rem 1rem;
      text-align: center
    }

    .notice {
      min-height: 1.5em
    }

    @media(max-width:575.98px) {
      .cart-page {
        padding-left: .75rem;
        padding-right: .75rem
      }

      .cart-line {
        grid-template-columns: 24px 76px minmax(0, 1fr);
        gap: 10px;
        padding: .85rem .65rem
      }

      .cart-line img,
      .cart-image-placeholder {
        width: 76px;
        height: 76px
      }

      .cart-line-price {
        grid-column: 3;
        display: flex;
        justify-content: space-between;
        align-items: center
      }

      .cart-quantity {
        width: 84px;
        margin-top: .4rem
      }

      .cart-summary {
        padding: .8rem
      }

      .cart-heading {
        align-items: flex-start
      }
    }
  </style>
</head>

<body>
  <div id="navbar"></div>
  <main class="container cart-page py-4 py-md-5">
    <div class="cart-heading">
      <div>
        <h1 class="h2 mb-1">Mi carrito</h1>
        <p class="text-secondary mb-0">Revisa los instrumentos que agregaste.</p>
      </div>
      <div class="d-flex align-items-center gap-3"><span class="text-secondary">Artículos: <strong id="cart-count">0</strong></span><button class="btn btn-outline-primary" onclick="showAccount()">Mi cuenta</button></div>
    </div>
    <div id="notice" class="notice text-primary mb-2" role="status"></div>
    <section id="cart-panel" class="cart-panel">
      <div id="cart-body">
        <div class="empty-cart text-secondary">Cargando carrito…</div>
      </div>
    </section>
    <section id="cart-summary" class="cart-summary" hidden>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <div class="text-secondary small">Total de productos seleccionados</div>
          <div id="cart-total" class="cart-total">$0.00</div>
          <div id="cart-selected-count" class="text-success small"></div>
        </div><button id="checkout-btn" class="btn cart-continue px-4" onclick="checkout()">Continuar (0)</button>
      </div>
      <p class="small text-secondary mb-0 mt-2">El pedido se registrará como pendiente para continuar con el proceso de compra.</p>
    </section>
  </main>
  <div id="footer"></div>

  <div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-light">
        <div class="modal-header">
          <h5 class="modal-title">Tu cuenta</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="btn-group mb-3"><button class="btn btn-outline-warning" onclick="accountMode(false)">Iniciar sesión</button><button class="btn btn-outline-warning" onclick="accountMode(true)">Crear cuenta</button></div>
          <form id="account-form">
            <div id="name-field" class="mb-2" hidden><label class="form-label">Nombre</label><input name="nombre" class="form-control"></div><label class="form-label">Correo</label><input name="correo" type="email" required class="form-control mb-2"><label class="form-label">Contraseña</label><input name="password" type="password" minlength="8" required class="form-control mb-2"><button class="btn btn-warning mt-2">Continuar</button>
          </form>
          <div id="account-msg" class="text-warning mt-2"></div>
        </div>
      </div>
    </div>
  </div>
  <script src="Bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="componentes.js"></script>
  <script>
    const API = 'api/index.php?action=';
    let accountAction = 'login',
      cartItems = [],
      selectedIds = new Set(),
      pendingAction = null;
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    } [c]));
    async function call(action, options = {}) {
      const r = await fetch(API + action, {
        credentials: 'same-origin',
        ...options,
        headers: {
          'Content-Type': 'application/json',
          ...(options.headers || {})
        }
      });
      const data = await r.json();
      if (!r.ok) throw Error(data.error || 'Ocurrió un error.');
      return data
    }

    function msg(text) {
      document.getElementById('notice').textContent = text || ''
    }

    function clearAction() {
      const p = new URLSearchParams(location.search);
      p.delete('accion');
      history.replaceState({}, '', `${location.pathname}${p.toString()?'?'+p.toString():''}`)
    }

    function showAccount() {
      bootstrap.Modal.getOrCreateInstance(document.getElementById('accountModal')).show()
    }

    function accountMode(register) {
      accountAction = register ? 'register' : 'login';
      document.getElementById('name-field').hidden = !register;
      document.getElementById('account-msg').textContent = ''
    }
    async function init() {
      const p = new URLSearchParams(location.search);
      const action = p.get('accion'),
        query = p.get('buscar');
      try {
        await loadCart();
        if (query && ['carrito', 'comprar'].includes(action)) {
          const results = await call('products&q=' + encodeURIComponent(query));
          const q = query.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
          const product = results.products.find(x => x.nombre.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase() === q) || results.products[0];
          if (!product) {
            msg(`No hay una publicación disponible para ${query}.`);
            return
          }
          pendingAction = {
            action,
            id: Number(product.idproducto)
          };
          await runPendingAction()
        }
      } catch (e) {
        msg(e.message)
      }
    }
    async function loadCart() {
      try {
        const d = await call('cart');
        cartItems = d.items;
        selectedIds = new Set(cartItems.map(x => Number(x.idproducto)));
        renderCart();
        document.getElementById('cart-count').textContent = cartItems.reduce((n, x) => n + Number(x.cantidad), 0)
      } catch (e) {
        cartItems = [];
        selectedIds.clear();
        renderCart(e.message);
        document.getElementById('cart-count').textContent = '0'
      }
    }

    function renderCart(error = '') {
      const body = document.getElementById('cart-body');
      const summary = document.getElementById('cart-summary');
      if (!cartItems.length) {
        summary.hidden = true;
        const message = error ? esc(error) : 'Agrega instrumentos desde el catálogo para verlos aquí.';
        body.innerHTML = '<div class="empty-cart"><h2 class="h4">Tu carrito está vacío</h2><p class="text-secondary">' + message + '</p><a class="btn btn-primary" href="instrumento.html">Explorar instrumentos</a></div>';
        return;
      }
      summary.hidden = false;
      const rows = cartItems.map(x => {
        const id = Number(x.idproducto);
        const name = esc(x.nombre);
        const image = x.imagen ?
          '<img src="' + esc(x.imagen) + '" alt="' + name + '" onerror="this.style.display=\'none\';this.nextElementSibling.hidden=false"><div class="cart-image-placeholder d-flex align-items-center justify-content-center text-secondary" hidden>♫</div>' :
          '<div class="cart-image-placeholder d-flex align-items-center justify-content-center text-secondary">♫</div>';
        const qty = Number(x.cantidad);
        const maxQty = Math.min(20, Math.max(Number(x.stock), qty));
        const options = Array.from({
          length: maxQty
        }, (_, i) => i + 1).map(n =>
          '<option value="' + n + '"' + (n === qty ? ' selected' : '') + '>' + n + ' u.</option>'
        ).join('');
        const amount = (Number(x.precio) * qty).toLocaleString('es-MX', {
          minimumFractionDigits: 2
        });
        return '<article class="cart-line">' +
          '<input class="form-check-input" type="checkbox" aria-label="Seleccionar ' + name + '" checked onchange="toggleItem(' + id + ',this.checked)">' +
          image +
          '<div><h3 class="cart-item-name">' + name + '</h3><div class="cart-item-meta">' + esc(x.categoria) + '</div>' +
          '<select class="form-select form-select-sm cart-quantity" aria-label="Cantidad de ' + name + '" onchange="updateQuantity(' + id + ',this.value)">' + options + '</select></div>' +
          '<div class="cart-line-price"><div class="cart-price">' + String.fromCharCode(36) + amount + '</div>' +
          '<button class="cart-remove" aria-label="Quitar ' + name + '" onclick="removeItem(' + id + ')">🗑 Quitar</button></div></article>';
      }).join('');
      body.innerHTML = '<div class="cart-select-all"><label class="d-flex align-items-center gap-3"><input id="select-all" class="form-check-input" type="checkbox" checked onchange="selectAll(this.checked)"><span>Productos</span></label></div>' +
        rows +
        '<section class="cart-shipping"><div class="d-flex justify-content-between align-items-center"><strong>Envío</strong><span class="text-secondary">Se calcula al continuar</span></div><div class="cart-progress"><span></span></div><div class="text-secondary small">Los detalles de entrega se completarán en el proceso de compra.</div></section>';
      updateSummary();
    }

    function selectAll(checked) {
      selectedIds = checked ? new Set(cartItems.map(x => Number(x.idproducto))) : new Set();
      document.querySelectorAll('.cart-line input[type=checkbox]').forEach(x => x.checked = checked);
      updateSummary()
    }

    function toggleItem(id, checked) {
      checked ? selectedIds.add(Number(id)) : selectedIds.delete(Number(id));
      updateSummary()
    }

    function updateSummary() {
      const items = cartItems.filter(x => selectedIds.has(Number(x.idproducto)));
      const total = items.reduce((s, x) => s + Number(x.precio) * Number(x.cantidad), 0);
      document.getElementById('cart-total').textContent = `$${total.toLocaleString('es-MX',{minimumFractionDigits:2})} MXN`;
      document.getElementById('cart-selected-count').textContent = `${items.reduce((s,x)=>s+Number(x.cantidad),0)} unidad(es) seleccionadas`;
      const b = document.getElementById('checkout-btn');
      b.textContent = `Continuar (${items.length})`;
      b.disabled = !items.length;
      const all = document.getElementById('select-all');
      if (all) {
        all.checked = items.length === cartItems.length;
        all.indeterminate = items.length > 0 && items.length < cartItems.length
      }
    }
    async function addProduct(id) {
      try {
        await call('cart', {
          method: 'POST',
          body: JSON.stringify({
            idproducto: id,
            cantidad: 1
          })
        });
        clearAction();
        msg('Instrumento agregado al carrito.');
        pendingAction = null;
        await loadCart()
      } catch (e) {
        msg(e.message);
        if (e.message.includes('Inicia sesión')) showAccount()
      }
    }
    async function buyProduct(id) {
      try {
        const r = await call('buy', {
          method: 'POST',
          body: JSON.stringify({
            idproducto: id
          })
        });
        clearAction();
        pendingAction = null;
        msg(`Pedido #${r.pedido} creado por $${r.total} MXN. El pago está pendiente.`);
        await loadCart()
      } catch (e) {
        msg(e.message);
        if (e.message.includes('Inicia sesión')) showAccount()
      }
    }
    async function runPendingAction() {
      if (!pendingAction) return;
      const a = pendingAction;
      if (a.action === 'comprar') await buyProduct(a.id);
      else await addProduct(a.id)
    }
    async function updateQuantity(id, quantity) {
      try {
        await call('cart', {
          method: 'PUT',
          body: JSON.stringify({
            idproducto: id,
            cantidad: Number(quantity)
          })
        });
        await loadCart()
      } catch (e) {
        msg(e.message);
        await loadCart()
      }
    }
    async function removeItem(id) {
      try {
        await call('cart', {
          method: 'DELETE',
          body: JSON.stringify({
            idproducto: id
          })
        });
        await loadCart()
      } catch (e) {
        msg(e.message)
      }
    }
    async function checkout() {
      try {
        const r = await call('checkout', {
          method: 'POST',
          body: JSON.stringify({
            metodo_pago: 'contra_entrega',
            productos: Array.from(selectedIds)
          })
        });
        msg(`Pedido #${r.pedido} creado por $${r.total} MXN. El pago está pendiente.`);
        await loadCart()
      } catch (e) {
        msg(e.message)
      }
    }
    document.getElementById('account-form').addEventListener('submit', async e => {
      e.preventDefault();
      try {
        const b = Object.fromEntries(new FormData(e.currentTarget));
        await call(accountAction, {
          method: 'POST',
          body: JSON.stringify(b)
        });
        bootstrap.Modal.getInstance(document.getElementById('accountModal')).hide();
        await loadCart();
        await runPendingAction()
      } catch (x) {
        document.getElementById('account-msg').textContent = x.message
      }
    });
    init();
  </script>
</body>

</html>