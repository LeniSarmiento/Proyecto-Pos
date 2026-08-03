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
            <h3 style="font-size: 1.25rem; margin-bottom: var(--spacing-md); border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-sm);">🔒 Cerrar Caja y Arqueo Detallado</h3>
            
            <form id="close-cash-form" onsubmit="handleCloseCash(event)">
                <input type="hidden" id="cash-session-id" name="id">
                
                <p class="text-muted" style="font-size: 0.875rem; margin-bottom: var(--spacing-lg);">
                    Cuenta físicamente el efectivo, vouchers de tarjetas y montos de Yape/Plin de tu turno, e ingrésalos para calcular automáticamente el balance y descuadre de caja.
                </p>

                <div class="table-container mb-lg" style="box-shadow: none; border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                    <table class="table" style="font-size: 0.9375rem;">
                        <thead>
                            <tr style="background: var(--color-bg);">
                                <th>Método Pago</th>
                                <th class="text-right">Sistema (Esperado)</th>
                                <th class="text-center" style="width: 160px;">Arqueo (Real)</th>
                                <th class="text-right">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Efectivo -->
                            <tr>
                                <td><strong>💵 Efectivo</strong></td>
                                <td class="text-right" id="close-expected-cash">S/ 0.00</td>
                                <td>
                                    <input type="number" id="close-actual-cash" name="actual_cash" class="form-input" required step="0.01" min="0" value="0.00" oninput="calculateArqueoDiffs()" style="padding: 4px var(--spacing-sm); text-align: right; font-weight:700;">
                                </td>
                                <td class="text-right" id="close-diff-cash" style="font-weight:700;">S/ 0.00</td>
                            </tr>
                            <!-- Tarjeta -->
                            <tr>
                                <td><strong>💳 Tarjeta</strong></td>
                                <td class="text-right" id="close-expected-card">S/ 0.00</td>
                                <td>
                                    <input type="number" id="close-actual-card" name="actual_card" class="form-input" required step="0.01" min="0" value="0.00" oninput="calculateArqueoDiffs()" style="padding: 4px var(--spacing-sm); text-align: right; font-weight:700;">
                                </td>
                                <td class="text-right" id="close-diff-card" style="font-weight:700;">S/ 0.00</td>
                            </tr>
                            <!-- Yape / Plin -->
                            <tr>
                                <td><strong>📱 Yape / Plin</strong></td>
                                <td class="text-right" id="close-expected-yape">S/ 0.00</td>
                                <td>
                                    <input type="number" id="close-actual-yape" name="actual_yape" class="form-input" required step="0.01" min="0" value="0.00" oninput="calculateArqueoDiffs()" style="padding: 4px var(--spacing-sm); text-align: right; font-weight:700;">
                                </td>
                                <td class="text-right" id="close-diff-yape" style="font-weight:700;">S/ 0.00</td>
                            </tr>
                            <!-- Total General -->
                            <tr style="background: var(--color-bg); font-weight: 700;">
                                <td>TOTAL GENERAL</td>
                                <td class="text-right" id="close-total-expected" style="color: var(--color-primary);">S/ 0.00</td>
                                <td class="text-right" id="close-total-actual" style="text-align: right; padding-right: 2.5rem;">S/ 0.00</td>
                                <td class="text-right" id="close-total-diff">S/ 0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas de Cierre y Descuadres (Opcional)</label>
                    <textarea id="close-notes" name="notes" class="form-textarea" rows="2" placeholder="Ej: Todo cuadra conforme, descuadre de S/ 0.50 por vuelto, etc..."></textarea>
                </div>

                <button type="submit" class="btn btn-accent btn-full btn-lg" id="btn-close-cash">
                    🔒 Realizar Arqueo Detallado y Cerrar Caja
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let activeCashSession = null;
    let expectedAmounts = { cash: 0, card: 0, yape: 0 };

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
                
                // Procesar montos esperados
                const cashSales = parseFloat(result.cash_sales_total || 0);
                const cardSales = parseFloat(result.card_sales_total || 0);
                const yapeSales = parseFloat(result.yape_sales_total || 0);
                
                expectedAmounts.cash = parseFloat(activeCashSession.opening_amount) + cashSales;
                expectedAmounts.card = cardSales;
                expectedAmounts.yape = yapeSales;

                // Mostrar esperados en la tabla de arqueo
                document.getElementById('close-expected-cash').textContent = formatCurrency(expectedAmounts.cash);
                document.getElementById('close-expected-card').textContent = formatCurrency(expectedAmounts.card);
                document.getElementById('close-expected-yape').textContent = formatCurrency(expectedAmounts.yape);

                // Inicializar valores de arqueo con los montos del sistema para facilitar el conteo
                document.getElementById('close-actual-cash').value = expectedAmounts.cash.toFixed(2);
                document.getElementById('close-actual-card').value = expectedAmounts.card.toFixed(2);
                document.getElementById('close-actual-yape').value = expectedAmounts.yape.toFixed(2);

                // Calcular total estimado en caja chica
                document.getElementById('cash-show-estimated').textContent = formatCurrency(expectedAmounts.cash);

                calculateArqueoDiffs();

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

    function calculateArqueoDiffs() {
        const actualCash = parseFloat(document.getElementById('close-actual-cash').value || 0);
        const actualCard = parseFloat(document.getElementById('close-actual-card').value || 0);
        const actualYape = parseFloat(document.getElementById('close-actual-yape').value || 0);

        // Diferencias
        const diffCash = actualCash - expectedAmounts.cash;
        const diffCard = actualCard - expectedAmounts.card;
        const diffYape = actualYape - expectedAmounts.yape;

        // Totales
        const totalExpected = expectedAmounts.cash + expectedAmounts.card + expectedAmounts.yape;
        const totalActual = actualCash + actualCard + actualYape;
        const totalDiff = totalActual - totalExpected;

        // Pintar diferencias en la tabla
        renderDiffCell('close-diff-cash', diffCash);
        renderDiffCell('close-diff-card', diffCard);
        renderDiffCell('close-diff-yape', diffYape);

        // Pintar totales
        document.getElementById('close-total-expected').textContent = formatCurrency(totalExpected);
        document.getElementById('close-total-actual').textContent = formatCurrency(totalActual);
        renderDiffCell('close-total-diff', totalDiff);
    }

    function renderDiffCell(elementId, value) {
        const el = document.getElementById(elementId);
        if (!el) return;
        
        el.textContent = (value >= 0 ? "+ " : "") + formatCurrency(value);
        
        if (Math.abs(value) < 0.01) {
            el.textContent = "S/ 0.00";
            el.style.color = 'var(--color-text-light)';
        } else if (value > 0) {
            el.style.color = 'var(--color-success)'; // Sobrante
        } else {
            el.style.color = 'var(--color-error)'; // Faltante
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
        
        const actualCash = parseFloat(document.getElementById('close-actual-cash').value || 0);
        const actualCard = parseFloat(document.getElementById('close-actual-card').value || 0);
        const actualYape = parseFloat(document.getElementById('close-actual-yape').value || 0);
        
        const totalActual = actualCash + actualCard + actualYape;
        
        if (!confirm(`¿Estás seguro de cerrar caja con un arqueo físico total de S/ ${totalActual.toFixed(2)}?`)) {
            return;
        }

        const btn = document.getElementById('btn-close-cash');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Cerrando Caja...';

        // Estructurar el cierre detallado
        const data = {
            id: document.getElementById('cash-session-id').value,
            closing_amount: actualCash, // Se guarda el efectivo real como monto principal de caja
            notes: document.getElementById('close-notes').value,
            arqueo_detallado: {
                cash: { expected: expectedAmounts.cash, actual: actualCash, diff: actualCash - expectedAmounts.cash },
                card: { expected: expectedAmounts.card, actual: actualCard, diff: actualCard - expectedAmounts.card },
                yape: { expected: expectedAmounts.yape, actual: actualYape, diff: actualYape - expectedAmounts.yape }
            }
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