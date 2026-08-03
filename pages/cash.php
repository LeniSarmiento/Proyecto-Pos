<?php
/**
 * Vista de Control de Caja (Apertura y Cierre)
 */
?>
<div style="margin-bottom: var(--spacing-2xl);">
    <h1 style="font-size: 2rem; margin-bottom: var(--spacing-sm);">Control de Caja Chica</h1>
    <p class="text-muted">Apertura, cierre y cuadre de efectivo para tu turno de trabajo</p>
</div>

<div id="cash-container" style="max-width: 800px; margin: 0 auto;">
    <!-- Skeletons / Cargando -->
    <div id="cash-loading" class="card">
        <div class="card-body text-center" style="padding: 3rem;">
            <div class="skeleton skeleton-title" style="margin: 0 auto 1rem;"></div>
            <div class="skeleton skeleton-text" style="width: 80%; margin: 0 auto;"></div>
        </div>
    </div>

    <!-- ESTADO: CAJA CERRADA (Apertura) -->
    <div id="cash-closed-section" class="card hidden">
        <div class="card-header" style="background: rgba(239, 68, 68, 0.05); border-bottom-color: rgba(239, 68, 68, 0.1);">
            <h3 style="color: var(--color-error); display: flex; align-items: center; gap: var(--spacing-sm);">
                🔴 Caja Cerrada
            </h3>
        </div>
        <form id="open-cash-form" onsubmit="handleOpenCash(event)">
            <div class="card-body" style="padding: var(--spacing-xl);">
                <p class="text-muted" style="margin-bottom: var(--spacing-lg);">
                    Para poder registrar ventas en el sistema, debes realizar la **Apertura de Caja** ingresando el monto de dinero en efectivo (sencillo) con el que estás iniciando tu turno de trabajo.
                </p>
                
                <div class="form-group">
                    <label class="form-label" style="font-size: 1.125rem;">Monto Inicial de Apertura (S/)</label>
                    <input 
                        type="number" 
                        id="open-amount" 
                        name="opening_amount" 
                        class="form-input" 
                        step="0.10" 
                        min="0" 
                        required 
                        placeholder="0.00"
                        style="font-size: 1.5rem; padding: 0.75rem 1rem; font-weight: 700; color: var(--color-primary);"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Notas de Apertura (Opcional)</label>
                    <textarea id="open-notes" name="notes" class="form-textarea" rows="2" placeholder="Observaciones sobre el sencillo inicial..."></textarea>
                </div>
            </div>
            <div class="card-footer" style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary btn-lg btn-full" id="btn-open-cash">
                    🔑 Abrir Caja y Comenzar Turno
                </button>
            </div>
        </form>
    </div>

    <!-- ESTADO: CAJA ABIERTA (Resumen + Cierre) -->
    <div id="cash-open-section" class="card hidden">
        <div class="card-header" style="background: rgba(16, 185, 129, 0.05); border-bottom-color: rgba(16, 185, 129, 0.1); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="color: var(--color-success); display: flex; align-items: center; gap: var(--spacing-sm);">
                🟢 Caja Abierta y Operativa
            </h3>
            <span class="tag" style="background: var(--color-primary); color: white;" id="cash-tag-opened-at">Abierto: --:--</span>
        </div>
        <div class="card-body" style="padding: var(--spacing-xl);">
            <!-- Resumen de Efectivo -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-xl); margin-bottom: var(--spacing-2xl);">
                <div style="background: var(--color-bg); padding: var(--spacing-lg); border-radius: var(--radius-lg); text-align: center;">
                    <span class="text-muted" style="font-size: 0.875rem; font-weight:600;">MONTO DE APERTURA</span>
                    <h2 style="font-size: 2rem; color: var(--color-text); margin-top: var(--spacing-sm);" id="cash-show-opening">S/ 0.00</h2>
                </div>
                <div style="background: rgba(37,99,235,0.05); padding: var(--spacing-lg); border-radius: var(--radius-lg); text-align: center; border: 1px solid rgba(37,99,235,0.1);">
                    <span class="text-muted" style="font-size: 0.875rem; font-weight:600;">TOTAL EFECTIVO EN CAJA (ESTIMADO)</span>
                    <h2 style="font-size: 2.25rem; color: var(--color-primary); margin-top: var(--spacing-sm);" id="cash-show-estimated">S/ 0.00</h2>
                </div>
            </div>

            <!-- Cierre de Caja Form -->
            <h3 style="font-size: 1.25rem; margin-bottom: var(--spacing-md); border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-sm);">🔒 Cerrar Caja y Finalizar Turno</h3>
            
            <form id="close-cash-form" onsubmit="handleCloseCash(event)">
                <input type="hidden" id="cash-session-id" name="id">
                
                <div class="form-group">
                    <label class="form-label" style="font-size: 1.125rem;">Monto Real en Caja (Arqueo de Efectivo)</label>
                    <p class="text-muted" style="font-size: 0.8125rem; margin-bottom: var(--spacing-sm);">
                        Cuenta físicamente todo el dinero en efectivo que tienes en tu gaveta e ingresa el monto total exacto aquí:
                    </p>
                    <input 
                        type="number" 
                        id="close-amount" 
                        name="closing_amount" 
                        class="form-input" 
                        step="0.10" 
                        min="0" 
                        required 
                        placeholder="0.00"
                        style="font-size: 1.5rem; padding: 0.75rem 1rem; font-weight: 700; color: var(--color-accent);"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Notas de Cierre y Descuadres (Opcional)</label>
                    <textarea id="close-notes" name="notes" class="form-textarea" rows="2" placeholder="Ej: Sencillo completo, descuadre de S/ 0.50 por vuelto, etc..."></textarea>
                </div>

                <button type="submit" class="btn btn-accent btn-full btn-lg" id="btn-close-cash">
                    🔒 Realizar Arqueo y Cerrar Caja
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let activeCashSession = null;

    async function checkCashStatus() {
        try {
            document.getElementById('cash-loading').style.display = 'block';
            document.getElementById('cash-closed-section').style.display = 'none';
            document.getElementById('cash-open-section').style.display = 'none';

            const response = await fetch('actions/get_cash_status.php');
            const result = await response.json();

            document.getElementById('cash-loading').style.display = 'none';

            if (result.success && result.session) {
                // Caja Abierta
                activeCashSession = result.session;
                document.getElementById('cash-session-id').value = activeCashSession.id;
                document.getElementById('cash-tag-opened-at').textContent = "Abierto: " + new Date(activeCashSession.opened_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                document.getElementById('cash-show-opening').textContent = formatCurrency(activeCashSession.opening_amount);
                
                // Calcular total estimado (Apertura + Ventas en Efectivo)
                const cashSales = parseFloat(result.cash_sales_total || 0);
                const estimatedTotal = parseFloat(activeCashSession.opening_amount) + cashSales;
                document.getElementById('cash-show-estimated').textContent = formatCurrency(estimatedTotal);

                document.getElementById('cash-open-section').style.display = 'block';
                document.getElementById('cash-open-section').classList.remove('hidden');
            } else {
                // Caja Cerrada
                activeCashSession = null;
                document.getElementById('cash-closed-section').style.display = 'block';
                document.getElementById('cash-closed-section').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de conexión con el módulo de caja', 'error');
        }
    }

    async function handleOpenCash(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-open-cash');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Abriendo Caja...';

        const data = {
            opening_amount: parseFloat(document.getElementById('open-amount').value),
            notes: document.getElementById('open-notes').value
        };

        try {
            const response = await fetch('actions/open_cash.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                showToast('🔑 ¡Caja abierta con éxito! Ya puedes registrar ventas.', 'success');
                checkCashStatus();
            } else {
                showToast(result.error || 'Error al abrir caja', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de red', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function handleCloseCash(event) {
        event.preventDefault();
        
        const closeAmount = parseFloat(document.getElementById('close-amount').value);
        if (!confirm(`¿Estás seguro de cerrar caja con un arqueo físico de S/ ${closeAmount.toFixed(2)}? Esta acción finalizará tu turno.`)) {
            return;
        }

        const btn = document.getElementById('btn-close-cash');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Cerrando Caja...';

        const data = {
            id: document.getElementById('cash-session-id').value,
            closing_amount: closeAmount,
            notes: document.getElementById('close-notes').value
        };

        try {
            const response = await fetch('actions/close_cash.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                showToast('🔒 Caja cerrada y turno finalizado correctamente.', 'success');
                checkCashStatus();
            } else {
                showToast(result.error || 'Error al cerrar caja', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de red', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    // Inicializar comprobación
    checkCashStatus();
</script>