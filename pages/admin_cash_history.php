<?php
/**
 * Vista de Historial de Cierres de Caja (Administrador)
 */
?>
<div style="margin-bottom: var(--spacing-2xl);">
    <h1 style="font-size: 2rem; margin-bottom: var(--spacing-sm);">Auditoría e Historial de Caja</h1>
    <p class="text-muted">Supervisa los turnos abiertos, cierres de caja, arqueos detallados y descuadres de tus colaboradores</p>
</div>

<!-- Filtros de Caja -->
<div class="card mb-lg">
    <div class="card-body" style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" id="search-cash-user" class="form-input" placeholder="🔍 Buscar por nombre del cajero..." oninput="filterCashHistory()">
        </div>
        <div>
            <select id="filter-cash-status" class="form-select" onchange="filterCashHistory()">
                <option value="">Todos los turnos</option>
                <option value="open">Abierto (Activo)</option>
                <option value="closed">Cerrado</option>
            </select>
        </div>
    </div>
</div>

<!-- Tabla de Turnos de Caja -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Vendedor / Cajero</th>
                <th>Fecha Apertura</th>
                <th>Fecha Cierre</th>
                <th class="text-right">Monto Inicial</th>
                <th class="text-right">Monto Cierre (Efectivo)</th>
                <th>Estado</th>
                <th>Arqueo Detallado y Observaciones</th>
            </tr>
        </thead>
        <tbody id="cash-history-body">
            <tr>
                <td colspan="7" class="text-center text-muted" style="padding: 2rem;">
                    Cargando historial de turnos...
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    let allCashHistory = [];

    async function loadCashHistory() {
        try {
            const response = await fetch('actions/get_cash_history.php');
            const result = await response.json();

            if (result.success) {
                allCashHistory = result.data;
                renderCashHistory(allCashHistory);
            } else {
                showToast('Error al cargar historial de caja', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de conexión', 'error');
        }
    }

    function renderCashHistory(sessions) {
        const tbody = document.getElementById('cash-history-body');
        if (!tbody) return;

        if (sessions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 2rem;">
                        No se registran sesiones de caja aún
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = sessions.map(s => {
            const openedDate = new Date(s.opened_at).toLocaleString();
            const closedDate = s.closed_at ? new Date(s.closed_at).toLocaleString() : '<span class="text-muted">— En Turno —</span>';
            const closingAmtText = s.closing_amount !== null ? `S/ ${s.closing_amount.toFixed(2)}` : '<span class="text-muted">—</span>';
            
            // Separar notas personales de log de arqueo si contiene el log de cierre
            let notesText = s.notes || '<span class="text-muted">Ninguna observación</span>';
            if (s.notes && s.notes.includes('=== ARQUEO DETALLADO')) {
                const parts = s.notes.split('=== ARQUEO DETALLADO');
                const userNotes = parts[0].trim();
                const arqueoLog = "=== ARQUEO DETALLADO" + parts[1];
                
                notesText = `
                    ${userNotes ? `<p style="margin-bottom:6px;"><strong>Obs:</strong> ${userNotes}</p>` : ''}
                    <pre style="font-family: monospace; font-size: 0.8125rem; background: var(--color-bg); padding: var(--spacing-sm); border-radius: var(--radius-sm); border: 1px solid var(--color-border); white-space: pre-wrap; word-break: break-all; margin-top: 4px; color: var(--color-text); text-align: left; line-height: 1.4;">${arqueoLog}</pre>
                `;
            }

            return `
                <tr>
                    <td>
                        <strong>👤 ${s.user_name}</strong><br>
                        <small class="text-muted">${s.user_email}</small>
                    </td>
                    <td>${openedDate}</td>
                    <td>${closedDate}</td>
                    <td class="text-right"><strong>S/ ${s.opening_amount.toFixed(2)}</strong></td>
                    <td class="text-right" style="color: var(--color-primary); font-weight:700;">${closingAmtText}</td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: ${s.status === 'open' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; color: ${s.status === 'open' ? 'var(--color-success)' : 'var(--color-error)'};">
                            ${s.status === 'open' ? 'Abierto' : 'Cerrado'}
                        </span>
                    </td>
                    <td>
                        <div style="max-width: 450px; text-align: left;">
                            ${notesText}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function filterCashHistory() {
        const query = document.getElementById('search-cash-user').value.toLowerCase();
        const status = document.getElementById('filter-cash-status').value;

        const filtered = allCashHistory.filter(s => {
            const matchesQuery = s.user_name.toLowerCase().includes(query) || 
                                 s.user_email.toLowerCase().includes(query);
            const matchesStatus = status === "" || s.status === status;

            return matchesQuery && matchesStatus;
        });

        renderCashHistory(filtered);
    }

    loadCashHistory();
</script>