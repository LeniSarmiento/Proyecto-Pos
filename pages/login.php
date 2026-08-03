<?php
/**
 * Página de Login
 * Formulario de autenticación de usuarios
 */

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>

<div style="min-height: calc(100vh - var(--header-height)); display: flex; align-items: center; justify-content: center; padding: var(--spacing-2xl) 0; background: linear-gradient(135deg, var(--color-bg) 0%, var(--color-bg-white) 100%);">
    <div class="card" style="width: 100%; max-width: 420px; box-shadow: var(--shadow-xl);">
        <div class="card-body" style="padding: var(--spacing-2xl);">
            <!-- Logo / Icono -->
            <div style="text-align: center; margin-bottom: var(--spacing-xl);">
                <div style="width: 80px; height: 80px; margin: 0 auto var(--spacing-md); background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 20L18 26L28 14" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 style="font-size: 1.5rem; margin-bottom: var(--spacing-sm);">Bienvenido</h1>
                <p class="text-muted">Ingresa tus credenciales para acceder al sistema</p>
            </div>
            
            <!-- Formulario de Login -->
            <form id="login-form">
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="tu@correo.com"
                        required
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            style="padding-right: 45px;"
                        >
                        <button type="button" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.25rem;" id="toggle-pw-btn" title="Ver contraseña">
                            👁️
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top: var(--spacing-lg);">
                    🔐 Iniciar Sesión
                </button>
            </form>
            
            <!-- Enlaces de Ayuda -->
            <div style="text-align: center; margin-top: var(--spacing-xl);">
                <p class="text-muted" style="font-size: 0.875rem;">
                    ¿Olvidaste tu contraseña? <a href="#" style="color: var(--color-primary);">Recuperar</a>
                </p>
            </div>
            
            <!-- Divider -->
            <div style="display: flex; align-items: center; gap: var(--spacing-md); margin: var(--spacing-xl) 0; color: var(--color-text-light); font-size: 0.875rem;">
                <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
                <span>o</span>
                <div style="flex: 1; height: 1px; background: var(--color-border);"></div>
            </div>
            
            <!-- Registro -->
            <div style="text-align: center;">
                <p class="text-muted" style="font-size: 0.875rem; margin-bottom: var(--spacing-md);">
                    ¿No tienes una cuenta?
                </p>
                <a href="register.php" class="btn btn-outline btn-full">
                    📝 Solicitar Registro
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Script de validación adicional -->
<script>
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    // Mostrar estado de carga
    btn.disabled = true;
    btn.innerHTML = '⏳ Iniciando sesión...';
    
    try {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        await AuthManager.login(email, password);
    } catch (error) {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('toggle-pw-btn');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = '🙈';
        toggleBtn.title = "Ocultar contraseña";
    } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = '👁️';
        toggleBtn.title = "Ver contraseña";
    }
}
</script>