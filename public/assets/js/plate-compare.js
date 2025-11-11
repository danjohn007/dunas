// plate-compare.js
// Requiere: endpoint público compare_plate.php y (opcional) los auto-runners que ya tienes.

async function comparePlate({ unitId = null, unitPlate = null, compareUrl }) {
  const form = new FormData();
  if (unitId)   form.append('unit_id', unitId);
  if (unitPlate) form.append('unit_plate', unitPlate);

  const res = await fetch(compareUrl, { method: 'POST', body: form });
  return res.json();
}

// Render genérico: pasa los IDs/elementos de tu UI
function renderPlateComparison({ detected, unitPlate, isMatch, detectedEl, statusEl, containerEl }) {
  if (detectedEl) detectedEl.textContent = detected || '—';
  if (statusEl)   statusEl.textContent   = isMatch ? 'Coincide ✔' : 'No coincide';

  // Colores/estilos básicos
  if (containerEl) {
    containerEl.classList.remove('match-ok', 'match-bad');
    containerEl.classList.add(isMatch ? 'match-ok' : 'match-bad');
  }
}

window.PlateCompare = { comparePlate, renderPlateComparison };
