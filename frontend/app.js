'use strict';

const API = '../backend/public/api';
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => [...document.querySelectorAll(selector)];
const categoryIcons = {
  'Informatique':'💻','Téléphones':'📱','Audio':'🎧','Électroménager':'☕','Mode':'👟','Accessoires':'🎒'
};
let products = [];
let categories = [];
let cart = JSON.parse(localStorage.getItem('shopnow-cart') || '[]');
let favorites = JSON.parse(localStorage.getItem('shopnow-favorites') || '[]');
let currency = localStorage.getItem('shopnow-currency') || 'USD';
let currentUser = null;
let activeCategory = '';
let detailQty = 1;
let toastTimer;

const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, char => ({
  '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
}[char]));
const selectedPrice = product => currency === 'CDF' ? product.price_cdf : product.price_usd;
const money = amount => {
  if (amount === null || amount === undefined || amount === '') return 'Prix non défini';
  const number = Number(amount);
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: currency === 'CDF' ? 0 : 2
  }).format(number) + (currency === 'CDF' ? ' FC' : ' $');
};
async function api(path, options = {}) {
  const response = await fetch(API + path, {
    credentials: 'same-origin',
    headers: { 'Content-Type':'application/json', ...(options.headers || {}) },
    ...options
  });
  let payload;
  try { payload = await response.json(); }
  catch { throw new Error('Réponse invalide du serveur. Vérifiez PHP et MySQL.'); }
  if (!response.ok || payload.success === false) throw new Error(payload.message || 'Une erreur est survenue.');
  return payload;
}
function showToast(message) {
  const toast = $('#toast');
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
}
function saveCart() {
  localStorage.setItem('shopnow-cart', JSON.stringify(cart));
  renderCart();
  updateCount();
}
function updateCount() {
  $('#cart-count').textContent = cart.reduce((sum, line) => sum + line.qty, 0);
}
function normalizeCart() {
  const validIds = new Set(products.map(product => Number(product.id)));
  cart = cart.filter(line => validIds.has(Number(line.id)) && Number(line.qty) > 0)
    .map(line => ({id:Number(line.id), qty:Math.min(100, Number(line.qty))}));
  saveCart();
}
function renderCategoryFilters() {
  $('#category-filters').innerHTML = categories.map(category =>
    '<label class="filter-option"><input type="checkbox" value="' + escapeHTML(category.name) +
    '" class="category-filter"> ' + escapeHTML(category.name) + '</label>'
  ).join('');
  $('#category-grid').innerHTML = categories.map(category =>
    '<button class="category-card" data-category="' + escapeHTML(category.name) + '">' +
    '<span>' + (categoryIcons[category.name] || '🛍️') + '</span><strong>' +
    escapeHTML(category.name) + '</strong></button>'
  ).join('');
}
function filteredProducts() {
  let list = [...products];
  const query = $('#search-input').value.trim().toLocaleLowerCase('fr');
  if (query) list = list.filter(p => (p.name + ' ' + p.category + ' ' + (p.description || '')).toLocaleLowerCase('fr').includes(query));
  const checked = $$('.category-filter:checked').map(input => input.value);
  if (activeCategory) list = list.filter(p => p.category === activeCategory);
  else if (checked.length) list = list.filter(p => checked.includes(p.category));
  const priceFilter = $('input[name="price"]:checked')?.value || 'all';
  list = list.filter(p => {
    const price = Number(selectedPrice(p));
    if (!Number.isFinite(price)) return false;
    if (priceFilter === '0-50') return price < 50;
    if (priceFilter === '50-100') return price >= 50 && price <= 100;
    if (priceFilter === '100-200') return price > 100 && price <= 200;
    if (priceFilter === '200+') return price > 200;
    return true;
  });
  if ($('#new-only').checked) list = [];
  if ($('#used-only').checked) list = [];
  const sort = $('#sort-select').value;
  if (sort === 'price-asc') list.sort((a,b) => Number(selectedPrice(a)) - Number(selectedPrice(b)));
  if (sort === 'price-desc') list.sort((a,b) => Number(selectedPrice(b)) - Number(selectedPrice(a)));
  if (sort === 'name') list.sort((a,b) => a.name.localeCompare(b.name, 'fr'));
  return list;
}
function productVisual(product, large = false) {
  if (product.image_path) {
    const path = String(product.image_path).replace(/^\/+/, '');
    return '<img src="../' + escapeHTML(path) + '" alt="' + escapeHTML(product.name) + '" loading="lazy">';
  }
  return '<span class="product-emoji">' + (categoryIcons[product.category] || '🛍️') + '</span>';
}
function renderProducts() {
  const list = filteredProducts();
  $('#results-count').textContent = list.length + ' produit' + (list.length > 1 ? 's' : '') + ' trouvé' + (list.length > 1 ? 's' : '');
  $('#product-grid').innerHTML = list.length ? list.map(product => {
    const price = selectedPrice(product);
    const unavailable = price === null || price === '' || Number(product.stock) <= 0;
    return '<article class="product-card">' +
      (Number(product.stock) <= 0 ? '<span class="product-tag out-tag">Rupture</span>' : '') +
      '<div class="product-image" data-detail="' + Number(product.id) + '">' + productVisual(product) + '</div>' +
      '<div class="product-info"><h3 class="product-name" data-detail="' + Number(product.id) + '">' + escapeHTML(product.name) + '</h3>' +
      '<div class="stock-note">' + (Number(product.stock) > 0 ? 'En stock : ' + Number(product.stock) : 'Indisponible') + '</div>' +
      '<div class="price">' + money(price) + '</div><div class="product-actions">' +
      '<button class="add-button" data-add="' + Number(product.id) + '" ' + (unavailable ? 'disabled' : '') + '>' +
      (unavailable ? (price == null ? 'Prix à définir' : 'Indisponible') : '🛒 Ajouter au panier') + '</button>' +
      '<button class="favorite-button ' + (favorites.includes(Number(product.id)) ? 'active' : '') +
      '" data-favorite="' + Number(product.id) + '" aria-label="Ajouter aux favoris">♥</button></div></div></article>';
  }).join('') : '<div class="empty-state"><div style="font-size:35px">🔎</div><h3>Aucun produit trouvé</h3><p>Modifiez vos filtres ou votre recherche.</p></div>';
}
function addToCart(id, qty = 1) {
  const product = products.find(p => Number(p.id) === Number(id));
  if (!product) return;
  if (selectedPrice(product) === null || selectedPrice(product) === '') return showToast('Le prix dans cette devise n’est pas encore défini.');
  const line = cart.find(item => Number(item.id) === Number(id));
  const nextQty = (line?.qty || 0) + qty;
  if (nextQty > Number(product.stock)) return showToast('Stock insuffisant pour cette quantité.');
  if (line) line.qty = nextQty;
  else cart.push({id:Number(id), qty});
  saveCart();
  showToast(product.name + ' ajouté au panier');
}
function renderCart() {
  const items = $('#cart-items');
  if (!cart.length) {
    items.innerHTML = '<div class="empty-state"><div style="font-size:38px">🛒</div><h3>Votre panier est vide</h3><p>Ajoutez des produits pour commencer vos achats.</p></div>';
  } else {
    items.innerHTML = cart.map(line => {
      const product = products.find(p => Number(p.id) === Number(line.id));
      if (!product) return '';
      return '<div class="cart-line"><div class="cart-thumb">' + (categoryIcons[product.category] || '🛍️') +
        '</div><div><h4>' + escapeHTML(product.name) + '</h4><p>' + money(selectedPrice(product)) +
        '</p><div class="quantity-control"><button data-qty="' + Number(product.id) +
        '" data-change="-1" aria-label="Diminuer">−</button><strong>' + line.qty +
        '</strong><button data-qty="' + Number(product.id) +
        '" data-change="1" aria-label="Augmenter">+</button></div></div><button class="remove-item" data-remove="' +
        Number(product.id) + '" aria-label="Supprimer">×</button></div>';
    }).join('');
  }
  const total = cart.reduce((sum,line) => {
    const product = products.find(p => Number(p.id) === Number(line.id));
    return sum + (product && selectedPrice(product) !== null ? Number(selectedPrice(product)) * line.qty : 0);
  }, 0);
  $('#cart-subtotal').textContent = money(total);
  $('#cart-total').textContent = money(total);
  $('#checkout-button').disabled = !cart.length;
  $('#checkout-button').style.opacity = cart.length ? 1 : .5;
}
function openCart() {
  $('#cart-drawer').classList.add('open');
  $('#cart-drawer').setAttribute('aria-hidden','false');
  $('#overlay').classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeCart() {
  $('#cart-drawer').classList.remove('open');
  $('#cart-drawer').setAttribute('aria-hidden','true');
  $('#overlay').classList.remove('show');
  document.body.style.overflow = '';
}
function openProduct(id) {
  const product = products.find(p => Number(p.id) === Number(id));
  if (!product) return;
  detailQty = 1;
  $('#product-detail').innerHTML = '<div class="detail-layout"><div class="detail-visual">' + productVisual(product, true) +
    '</div><div class="detail-copy"><span class="eyebrow">' + escapeHTML(product.category || 'BOUTIQUE').toUpperCase() +
    '</span><h2>' + escapeHTML(product.name) + '</h2><div class="detail-price">' + money(selectedPrice(product)) +
    '</div><p>' + escapeHTML(product.description || 'Découvrez ce produit dans notre boutique.') +
    '</p><p>Stock disponible : <strong>' + Number(product.stock) +
    '</strong></p><div class="detail-buy"><div class="quantity-control"><button id="detail-minus">−</button><strong id="detail-qty">1</strong><button id="detail-plus">+</button></div>' +
    '<button class="button button-primary" id="detail-add" ' + (selectedPrice(product) == null || Number(product.stock) <= 0 ? 'disabled' : '') +
    '>🛒 Ajouter au panier</button></div></div></div>';
  $('#product-dialog').showModal();
  $('#detail-minus').onclick = () => { detailQty = Math.max(1, detailQty - 1); $('#detail-qty').textContent = detailQty; };
  $('#detail-plus').onclick = () => { detailQty = Math.min(Number(product.stock), detailQty + 1); $('#detail-qty').textContent = detailQty; };
  $('#detail-add').onclick = () => { addToCart(id, detailQty); $('#product-dialog').close(); };
}
function renderAccount() {
  $('#account-content').innerHTML = '<div class="account-panel"><span class="eyebrow">ESPACE CLIENT</span><h2>' +
    (currentUser ? 'Bonjour ' + escapeHTML(currentUser.name) : 'Bienvenue sur ShopNow') +
    '</h2>' + (currentUser
      ? '<p class="account-email">' + escapeHTML(currentUser.email) + '</p><div class="account-actions"><button class="button button-primary" id="show-orders">Mes commandes</button><button class="button button-light" id="logout-button">Se déconnecter</button></div><div id="orders-content"></div>'
      : '<div class="auth-tabs"><button class="auth-tab active" data-auth-tab="login">Connexion</button><button class="auth-tab" data-auth-tab="register">Créer un compte</button></div><form id="auth-form" class="checkout-form"><label id="name-field" hidden>Nom complet<input name="name" minlength="2" autocomplete="name"></label><label>Adresse e-mail<input name="email" type="email" required autocomplete="email"></label><label>Mot de passe<input name="password" type="password" minlength="8" required autocomplete="current-password"></label><button class="button button-primary" type="submit" id="auth-submit">Se connecter</button><p class="tiny-note" id="auth-message">Votre compte permet de suivre vos commandes.</p></form>') +
    '</div>';
  if (!currentUser) {
    let mode = 'login';
    $$('[data-auth-tab]').forEach(button => button.onclick = () => {
      mode = button.dataset.authTab;
      $$('[data-auth-tab]').forEach(tab => tab.classList.toggle('active', tab === button));
      $('#name-field').hidden = mode !== 'register';
      $('#name-field input').required = mode === 'register';
      $('#auth-submit').textContent = mode === 'register' ? 'Créer mon compte' : 'Se connecter';
      $('#auth-form input[name="password"]').autocomplete = mode === 'register' ? 'new-password' : 'current-password';
    });
    $('#auth-form').onsubmit = async event => {
      event.preventDefault();
      const form = new FormData(event.currentTarget);
      const payload = Object.fromEntries(form.entries());
      $('#auth-submit').disabled = true;
      try {
        const result = await api(mode === 'register' ? '/auth/register.php' : '/auth/login.php', {
          method:'POST', body:JSON.stringify(payload)
        });
        currentUser = result.user;
        renderAccount();
        showToast(result.message || 'Connexion réussie.');
      } catch (error) {
        $('#auth-message').textContent = error.message;
      } finally {
        const submit = $('#auth-submit');
        if (submit) submit.disabled = false;
      }
    };
  } else {
    $('#logout-button').onclick = async () => {
      try { await api('/auth/logout.php',{method:'POST',body:'{}'}); currentUser = null; renderAccount(); showToast('Vous êtes déconnecté.'); }
      catch(error) { showToast(error.message); }
    };
    $('#show-orders').onclick = loadOrders;
  }
}
async function loadOrders() {
  const target = $('#orders-content');
  target.innerHTML = '<p class="muted">Chargement de vos commandes…</p>';
  try {
    const result = await api('/orders/index.php');
    if (!result.data.length) { target.innerHTML = '<p class="muted">Vous n’avez pas encore passé de commande.</p>'; return; }
    target.innerHTML = '<h3 class="orders-title">Historique des commandes</h3>' + result.data.map(order =>
      '<article class="order-card"><div><strong>Commande #' + Number(order.id) + '</strong><span>' +
      new Date(order.created_at).toLocaleString('fr-FR') + '</span></div><p>' +
      escapeHTML(order.status) + ' · ' + moneyForOrder(order.total_amount, order.currency) +
      '</p><ul>' + order.items.map(item => '<li>' + escapeHTML(item.product_name) + ' × ' +
      Number(item.quantity) + '</li>').join('') + '</ul></article>'
    ).join('');
  } catch(error) { target.innerHTML = '<p class="error-text">' + escapeHTML(error.message) + '</p>'; }
}
function moneyForOrder(amount, orderCurrency) {
  return new Intl.NumberFormat('fr-FR',{maximumFractionDigits:2}).format(Number(amount)) + (orderCurrency === 'CDF' ? ' FC' : ' $');
}
async function openCheckout() {
  if (!cart.length) return;
  closeCart();
  if (!currentUser) {
    renderAccount();
    $('#account-dialog').showModal();
    showToast('Connectez-vous ou créez un compte avant de commander.');
    return;
  }
  const total = cart.reduce((sum,line) => {
    const product = products.find(p => Number(p.id) === Number(line.id));
    return sum + Number(selectedPrice(product)) * line.qty;
  }, 0);
  $('#checkout-content').innerHTML = '<div class="checkout-content"><span class="eyebrow">DERNIÈRE ÉTAPE</span><h2>Finaliser la commande</h2><p>Total : <strong>' +
    money(total) + '</strong></p><form class="checkout-form" id="checkout-form"><label>Adresse de livraison<textarea name="address" required rows="3" placeholder="Rue, commune, ville, pays"></textarea></label><button class="button button-primary" type="submit">Confirmer la commande</button><p class="tiny-note">Aucun paiement en ligne n’est effectué par cette démonstration.</p></form></div>';
  $('#checkout-dialog').showModal();
  $('#checkout-form').onsubmit = async event => {
    event.preventDefault();
    const button = event.currentTarget.querySelector('button[type="submit"]');
    button.disabled = true;
    const shippingAddress = new FormData(event.currentTarget).get('address').trim();
    const items = cart.map(line => ({product_id:Number(line.id), quantity:Number(line.qty)}));
    try {
      const result = await api('/orders/create.php', {
        method:'POST',
        body:JSON.stringify({currency, shipping_address:shippingAddress, items})
      });
      cart = [];
      saveCart();
      $('#checkout-content').innerHTML = '<div class="checkout-success"><div class="success-icon">✅</div><h2>Commande enregistrée !</h2><p>Votre commande <strong>#' +
        Number(result.data.order_id) + '</strong> a été enregistrée pour un total de <strong>' +
        moneyForOrder(result.data.total_amount, result.data.currency) +
        '</strong>.</p><button class="button button-primary" id="finish-order">Continuer mes achats</button></div>';
      $('#finish-order').onclick = () => $('#checkout-dialog').close();
      showToast('Commande enregistrée avec succès.');
    } catch(error) {
      showToast(error.message);
      button.disabled = false;
    }
  };
}
async function loadInitialData() {
  try {
    const [productResult, categoryResult, sessionResult] = await Promise.all([
      api('/products.php'), api('/categories.php'), api('/auth/me.php')
    ]);
    products = productResult.data || [];
    categories = categoryResult.data || [];
    currentUser = sessionResult.authenticated ? sessionResult.user : null;
    renderCategoryFilters();
    normalizeCart();
    renderProducts();
    renderCart();
    updateCount();
    $('#api-status')?.remove();
  } catch(error) {
    $('#product-grid').innerHTML = '<div class="empty-state"><h3>Connexion au serveur impossible</h3><p>' +
      escapeHTML(error.message) + '</p><p>Vérifiez Apache, MySQL et l’import de database/schema.sql dans phpMyAdmin.</p></div>';
    showToast('Impossible de charger la boutique.');
  }
}

$('#product-grid').addEventListener('click', event => {
  const add = event.target.closest('[data-add]');
  const detail = event.target.closest('[data-detail]');
  const favorite = event.target.closest('[data-favorite]');
  if (add && !add.disabled) addToCart(Number(add.dataset.add));
  if (detail) openProduct(Number(detail.dataset.detail));
  if (favorite) {
    const id = Number(favorite.dataset.favorite);
    favorites = favorites.includes(id) ? favorites.filter(value => value !== id) : [...favorites,id];
    localStorage.setItem('shopnow-favorites',JSON.stringify(favorites));
    renderProducts();
  }
});
$('#cart-items').addEventListener('click', event => {
  const quantity = event.target.closest('[data-qty]');
  const remove = event.target.closest('[data-remove]');
  if (quantity) {
    const id = Number(quantity.dataset.qty);
    const line = cart.find(item => item.id === id);
    const product = products.find(item => Number(item.id) === id);
    line.qty += Number(quantity.dataset.change);
    if (line.qty <= 0) cart = cart.filter(item => item.id !== id);
    else if (line.qty > Number(product.stock)) { line.qty = Number(product.stock); showToast('Stock maximum atteint.'); }
    saveCart();
  }
  if (remove) { cart = cart.filter(item => item.id !== Number(remove.dataset.remove)); saveCart(); }
});
$('#category-grid').addEventListener('click', event => {
  const card = event.target.closest('[data-category]');
  if (!card) return;
  activeCategory = activeCategory === card.dataset.category ? '' : card.dataset.category;
  $$('.category-card').forEach(item => item.classList.toggle('selected',item.dataset.category === activeCategory));
  $$('.category-filter').forEach(input => input.checked = input.value === activeCategory);
  renderProducts();
  $('#catalogue').scrollIntoView();
});
$('#category-filters').addEventListener('change', () => {
  activeCategory = '';
  $$('.category-card').forEach(item => item.classList.remove('selected'));
  renderProducts();
});
$$('input[name="price"],#new-only,#used-only').forEach(input => input.addEventListener('change',renderProducts));
$('#sort-select').addEventListener('change',renderProducts);
$('#search-input').addEventListener('input',renderProducts);
$('#search-form').addEventListener('submit',event => { event.preventDefault(); $('#catalogue').scrollIntoView(); });
$('#reset-filters').onclick = () => {
  $('#search-input').value = '';
  $('input[name="price"][value="all"]').checked = true;
  $('#new-only').checked = false; $('#used-only').checked = false;
  $$('.category-filter').forEach(input => input.checked = false);
  activeCategory = '';
  $$('.category-card').forEach(item => item.classList.remove('selected'));
  $('#sort-select').value = 'popular';
  renderProducts();
};
$('#currency-select').value = currency;
$('#currency-select').addEventListener('change', event => {
  currency = event.target.value;
  localStorage.setItem('shopnow-currency',currency);
  renderProducts();
  renderCart();
});
$('#cart-button').onclick = openCart;
$('#close-cart').onclick = closeCart;
$('#overlay').onclick = closeCart;
$('#checkout-button').onclick = openCheckout;
$('#close-product').onclick = () => $('#product-dialog').close();
$('#close-checkout').onclick = () => $('#checkout-dialog').close();
$('#account-button').onclick = () => { renderAccount(); $('#account-dialog').showModal(); };
$('#close-account').onclick = () => $('#account-dialog').close();
$('#mobile-menu').onclick = () => $('#main-nav').classList.toggle('open');
$$('.nav-link').forEach(link => link.addEventListener('click',() => $('#main-nav').classList.remove('open')));
$('#year').textContent = new Date().getFullYear();
loadInitialData();
