const cartItems = document.getElementById("cartItems");
const subtotalEl = document.getElementById("subtotal");
const totalEl = document.getElementById("total");
const itemCountEl = document.getElementById("itemCount");
const couponInput = document.getElementById("coupon");
const couponFeedback = document.getElementById("couponFeedback");
const toast = document.getElementById("toast");

let discount = 0;

const money = value => new Intl.NumberFormat("es-MX", {
  style: "currency", currency: "MXN"
}).format(value);

function showToast(message) {
  toast.textContent = message;
  toast.classList.add("show");
  window.clearTimeout(showToast.timer);
  showToast.timer = window.setTimeout(() => toast.classList.remove("show"), 2600);
}

function updateCart() {
  let subtotal = 0;
  let count = 0;

  document.querySelectorAll(".cart-item").forEach(item => {
    const price = Number(item.dataset.price);
    const quantity = Number(item.querySelector(".qty-value").textContent);
    subtotal += price * quantity;
    count += quantity;
    item.querySelector(".item-price").textContent = money(price * quantity);
  });

  const total = Math.max(0, subtotal * (1 - discount));
  subtotalEl.textContent = money(subtotal);
  totalEl.textContent = money(total);
  itemCountEl.textContent = count;

  if (count === 0) {
    cartItems.innerHTML = `
      <div class="empty-cart">
        <div>♬</div>
        <h3>Tu carrito está vacío</h3>
        <p>Explora nuestro catálogo y encuentra tu próximo instrumento.</p>
        <a href="#">VOLVER AL CATÁLOGO</a>
      </div>`;
  }
}

cartItems.addEventListener("click", event => {
  const item = event.target.closest(".cart-item");
  if (!item) return;

  const quantityEl = item.querySelector(".qty-value");
  let quantity = Number(quantityEl.textContent);

  if (event.target.classList.contains("qty-plus")) {
    quantity += 1;
    quantityEl.textContent = quantity;
    updateCart();
  }

  if (event.target.classList.contains("qty-minus")) {
    quantity -= 1;
    if (quantity < 1) {
      showToast("La cantidad mínima es 1. Puedes eliminar el artículo.");
      return;
    }
    quantityEl.textContent = quantity;
    updateCart();
  }

  if (event.target.classList.contains("remove-btn")) {
    item.remove();
    updateCart();
    showToast("Artículo eliminado del carrito.");
  }
});

document.getElementById("applyCoupon").addEventListener("click", () => {
  const code = couponInput.value.trim().toUpperCase();

  if (code === "INSTRUMENTARIUM10") {
    discount = 0.10;
    couponFeedback.textContent = "¡Cupón aplicado! Tienes 10% de descuento.";
    updateCart();
  } else if (!code) {
    discount = 0;
    couponFeedback.textContent = "Escribe un código para aplicarlo.";
    updateCart();
  } else {
    discount = 0;
    couponFeedback.textContent = "Código no válido. Intenta con otro.";
    updateCart();
  }
});

document.getElementById("checkoutBtn").addEventListener("click", () => {
  if (Number(itemCountEl.textContent) === 0) {
    showToast("Agrega al menos un instrumento para continuar.");
    return;
  }
  showToast("Interfaz de demostración: conecta aquí tu proceso de pago.");
});

document.getElementById("menuToggle").addEventListener("click", () => {
  document.querySelector(".main-nav").classList.toggle("open");
});

updateCart();
