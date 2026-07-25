/**
 * Punto de Venta Arquitec - JavaScript Principal
 * Manejo de UI, carrito de compras y comunicación con API
 */

// ============================================
// ESTADO GLOBAL DE LA APLICACIÓN
// ============================================
const AppState = {
    cart: [],
    products: [],
    user: null,
    isLoading: false,
    apiUrl: 'actions/'
};

// ============================================
// UTILIDADES
// ============================================

/**
 * Mostrar notificación toast
 */
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:1.25rem;">&times;</button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

/**
 * Formatear moneda
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(amount);
}

/**
 * Generar ID único
 */
function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// ============================================
// MANEJO DEL CARRITO
// ============================================

class CartManager {
    static add(product, quantity = 1) {
        const existingItem = AppState.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            AppState.cart.push({
                ...product,
                quantity
            });
        }
        
        this.save();
        this.render();
        showToast(`"${product.name}" agregado al carrito`, 'success');
    }
    
    static addById(productId, quantity = 1) {
        const product = AppState.products.find(p => p.id === productId);
        if (product) {
            this.add(product, quantity);
        } else {
            showToast('Producto no encontrado', 'error');
        }
    }
    
    static remove(productId) {
        AppState.cart = AppState.cart.filter(item => item.id !== productId);
        this.save();
        this.render();
        showToast('Producto eliminado del carrito', 'info');
    }
    
    static updateQuantity(productId, quantity) {
        const item = AppState.cart.find(item => item.id === productId);
        
        if (item) {
            if (quantity <= 0) {
                this.remove(productId);
            } else {
                item.quantity = quantity;
                this.save();
                this.render();
            }
        }
    }
    
    static clear() {
        AppState.cart = [];
        this.save();
        this.render();
    }
    
    static getTotal() {
        return AppState.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    static getItemCount() {
        return AppState.cart.reduce((count, item) => count + item.quantity, 0);
    }
    
    static save() {
        localStorage.setItem('pv_cart', JSON.stringify(AppState.cart));
    }
    
    static load() {
        const saved = localStorage.getItem('pv_cart');
        if (saved) {
            AppState.cart = JSON.parse(saved);
        }
    }
    
    static render() {
        const cartItems = document.getElementById('cart-items');
        const cartCount = document.getElementById('cart-count');
        const cartTotal = document.getElementById('cart-total');
        
        if (!cartItems) return;
        
        // Actualizar contador
        if (cartCount) {
            const count = this.getItemCount();
            cartCount.textContent = count;
            cartCount.style.display = count > 0 ? 'inline-block' : 'none';
        }
        
        // Renderizar items
        if (AppState.cart.length === 0) {
            cartItems.innerHTML = `
                <div class="text-center text-muted" style="padding: 2rem;">
                    <p>El carrito está vacío</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem;">Agrega productos para comenzar</p>
                </div>
            `;
        } else {
            cartItems.innerHTML = AppState.cart.map(item => `
                <div class="cart-item">
                    <img src="${item.image_url || 'assets/img/placeholder.png'}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <h4 style="font-size: 0.9375rem; margin-bottom: 0.25rem;">${item.name}</h4>
                        <p style="color: var(--color-primary); font-weight: 600;">${formatCurrency(item.price)}</p>
                        <div class="cart-item-quantity">
                            <button class="btn btn-sm btn-secondary" onclick="CartManager.updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                            <span style="min-width: 2rem; text-align: center;">${item.quantity}</span>
                            <button class="btn btn-sm btn-secondary" onclick="CartManager.updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline" onclick="CartManager.remove('${item.id}')" style="color: var(--color-error); border-color: var(--color-error);">&times;</button>
                </div>
            `).join('');
        }
        
        // Actualizar total
        if (cartTotal) {
            cartTotal.textContent = formatCurrency(this.getTotal());
        }
    }
}

// ============================================
// MANEJO DE PRODUCTOS
// ============================================

class ProductManager {
    static async load() {
        try {
            AppState.isLoading = true;
            this.renderSkeleton();
            
            const response = await fetch(`${AppState.apiUrl}get_products.php`);
            const result = await response.json();
            
            if (result.success) {
                AppState.products = result.data;
                this.render();
            } else {
                showToast('Error al cargar productos', 'error');
            }
        } catch (error) {
            console.error('Error loading products:', error);
            showToast('No se pudo conectar con el servidor', 'error');
        } finally {
            AppState.isLoading = false;
        }
    }
    
    static render(products = AppState.products) {
        const container = document.getElementById('products-grid');
        
        if (!container) return;
        
        if (products.length === 0) {
            container.innerHTML = `
                <div class="text-center" style="grid-column: 1/-1; padding: 3rem;">
                    <h3>No hay productos disponibles</h3>
                    <p class="text-muted">Agrega productos desde el panel de administración</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = products.map(product => `
            <div class="product-card" onclick="CartManager.addById('${product.id}')">
                <img src="${product.image_url || 'assets/img/placeholder.svg'}" alt="${product.name}" class="product-image" onerror="this.onerror=null; this.src='assets/img/placeholder.svg'">
                <div class="product-body">
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-price">${formatCurrency(product.price)}</p>
                    <span class="product-stock ${product.stock <= 0 ? 'out' : product.stock <= product.min_stock ? 'low' : ''}">
                        ${product.stock <= 0 ? 'Agotado' : product.stock <= product.min_stock ? 'Stock bajo' : `Stock: ${product.stock}`}
                    </span>
                </div>
            </div>
        `).join('');
    }
    
    static renderSkeleton() {
        const container = document.getElementById('products-grid');
        
        if (!container) return;
        
        container.innerHTML = Array(6).fill(0).map(() => `
            <div class="card">
                <div class="skeleton" style="height: 200px;"></div>
                <div class="card-body">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text" style="width: 40%;"></div>
                </div>
            </div>
        `).join('');
    }
    
    static search(query) {
        const filtered = AppState.products.filter(product =>
            product.name.toLowerCase().includes(query.toLowerCase()) ||
            (product.sku && product.sku.toLowerCase().includes(query.toLowerCase()))
        );
        this.render(filtered);
    }
}

// ============================================
// MANEJO DE VENTAS
// ============================================

class SaleManager {
    static async checkout(customerData = {}) {
        if (AppState.cart.length === 0) {
            showToast('El carrito está vacío', 'warning');
            return;
        }
        
        try {
            AppState.isLoading = true;
            
            const saleData = {
                items: AppState.cart,
                customer: customerData,
                total: CartManager.getTotal(),
                payment_method: customerData.payment_method || 'cash',
                notes: customerData.notes || ''
            };
            
            const response = await fetch(`${AppState.apiUrl}create_sale.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(saleData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Venta registrada exitosamente', 'success');
                CartManager.clear();
                
                // Recargar productos para actualizar stock
                await ProductManager.load();
                
                // Cerrar panel del carrito si está abierto
                const cartPanel = document.querySelector('.cart-panel');
                if (cartPanel) {
                    cartPanel.classList.remove('open');
                }
                
                // Mostrar comprobante si existe
                if (result.sale_number) {
                    setTimeout(() => {
                        if (confirm(`Venta ${result.sale_number} registrada. ¿Imprimir comprobante?`)) {
                            window.print();
                        }
                    }, 500);
                }
            } else {
                showToast(result.error || 'Error al registrar la venta', 'error');
            }
        } catch (error) {
            console.error('Checkout error:', error);
            showToast('No se pudo procesar la venta', 'error');
        } finally {
            AppState.isLoading = false;
        }
    }
}

// ============================================
// MANEJO DE AUTENTICACIÓN
// ============================================

class AuthManager {
    static async login(email, password) {
        try {
            const response = await fetch(`${AppState.apiUrl}login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            
            const result = await response.json();
            
            if (result.success) {
                sessionStorage.setItem('pv_user', JSON.stringify(result.user));
                sessionStorage.setItem('pv_token', result.access_token);
                AppState.user = result.user;
                showToast('Bienvenido', 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                showToast(result.error || 'Credenciales inválidas', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            showToast('Error de conexión', 'error');
        }
    }
    
    static async logout() {
        const token = sessionStorage.getItem('pv_token');
        
        if (token) {
            await fetch(`${AppState.apiUrl}logout.php`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
        }
        
        sessionStorage.clear();
        AppState.user = null;
        window.location.href = 'index.php';
    }
    
    static check() {
        const user = sessionStorage.getItem('pv_user');
        if (user) {
            AppState.user = JSON.parse(user);
        }
    }
}

// ============================================
// MANEJO DEL PANEL DE CARRITO
// ============================================

function toggleCart() {
    const cartPanel = document.querySelector('.cart-panel');
    if (cartPanel) {
        cartPanel.classList.toggle('open');
    }
}

function closeCart() {
    const cartPanel = document.querySelector('.cart-panel');
    if (cartPanel) {
        cartPanel.classList.remove('open');
    }
}

// ============================================
// MANEJO DEL MENÚ MÓVIL
// ============================================

function toggleMenu() {
    const nav = document.querySelector('.main-nav');
    if (nav) {
        nav.classList.toggle('open');
    }
}

// ============================================
// BÚSQUEDA EN TIEMPO REAL
// ============================================

let searchTimeout;
function handleSearch(event) {
    clearTimeout(searchTimeout);
    const query = event.target.value;
    
    searchTimeout = setTimeout(() => {
        ProductManager.search(query);
    }, 300);
}

// ============================================
// INICIALIZACIÓN
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Cargar estado del carrito
    CartManager.load();
    CartManager.render();
    
    // Verificar autenticación
    AuthManager.check();
    
    // Cargar productos si estamos en la página principal
    const productsGrid = document.getElementById('products-grid');
    if (productsGrid) {
        ProductManager.load();
    }
    
    // Configurar búsqueda
    const searchInput = document.getElementById('product-search');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }
    
    // Configurar formulario de login
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            await AuthManager.login(email, password);
        });
    }
    
    // Configurar formulario de checkout
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const customerData = Object.fromEntries(formData);
            await SaleManager.checkout(customerData);
        });
    }
    
    // Cerrar carrito al hacer clic fuera
    document.addEventListener('click', (e) => {
        const cartPanel = document.querySelector('.cart-panel');
        const cartToggle = document.querySelector('[onclick="toggleCart()"]');
        
        if (cartPanel && cartPanel.classList.contains('open') &&
            !cartPanel.contains(e.target) && !cartToggle?.contains(e.target)) {
            closeCart();
        }
    });
    
    // Animaciones de scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.fade-in, .slide-up').forEach(el => {
        observer.observe(el);
    });
});

// ============================================
// EXPORTAR PARA USO GLOBAL
// ============================================

window.CartManager = CartManager;
window.ProductManager = ProductManager;
window.SaleManager = SaleManager;
window.AuthManager = AuthManager;
window.toggleCart = toggleCart;
window.closeCart = closeCart;
window.toggleMenu = toggleMenu;
window.formatCurrency = formatCurrency;
window.showToast = showToast;