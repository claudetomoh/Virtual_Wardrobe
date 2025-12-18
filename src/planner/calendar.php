<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// load user's vw_outfits for external dragging
$outfitsStmt = $pdo->prepare('SELECT id, title, top_id, bottom_id, shoe_id, accessory_id FROM ' . TBL_OUTFITS . ' WHERE user_id = ? ORDER BY created_at DESC');
$outfitsStmt->execute([$_SESSION['user_id']]);
$outfits = $outfitsStmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
?>
<div class="planner-shell">
  <div class="planner-header">
    <div>
      <p class="eyebrow">Planner</p>
      <h1 class="planner-title">Planner Calendar</h1>
      <p class="muted">Drop vw_outfits onto the calendar to schedule your looks.</p>
    </div>
    <div class="planner-actions">
      <a class="btn btn-secondary" id="backToDash" href="<?=h(url_path('src/dashboard.php'))?>">Dashboard</a>
      <a class="btn btn-primary" href="<?=h(url_path('src/planner/list.php'))?>">List View</a>
    </div>
  </div>

  <div class="planner-layout">
    <aside class="planner-panel outfit-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Outfit Library</p>
          <h3>Your outfits</h3>
          <p class="muted small">Drag an outfit to a date.</p>
        </div>
      </div>
      <div class="outfit-grid">
        <?php foreach ($outfits as $of): ?>
          <?php
            $itemIds = array_values(array_filter([
              $of['top_id'],
              $of['bottom_id'],
              $of['shoe_id'],
              $of['accessory_id'],
            ]));
            $img = '';
            if (!empty($itemIds)) {
              $ph = implode(',', array_fill(0, count($itemIds), '?'));
              $stmt = $pdo->prepare("SELECT image_path FROM " . TBL_CLOTHES . " WHERE id IN ($ph) LIMIT 1");
              $stmt->execute($itemIds);
              $img = $stmt->fetchColumn() ?: '';
            }
          ?>
          <div class="outfit-card outfit-draggable" draggable="true" data-outfit-id="<?= (int)$of['id'] ?>" data-outfit-title="<?=h($of['title'] ?: 'Untitled')?>" data-image="<?=h($img)?>">
            <div class="outfit-card-body">
              <div class="outfit-thumb">
                <?php if ($img): ?><img src="<?=h($img)?>" alt="">
                <?php else: ?><span class="thumb-placeholder">👕</span><?php endif; ?>
              </div>
              <div class="outfit-meta">
                <div class="meta-title"><?=h($of['title'] ?: 'Untitled')?></div>
                <div class="meta-sub">Drag to plan</div>
              </div>
              <span class="drag-chip">Drag</span>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($outfits)): ?>
          <div class="planner-empty">No vw_outfits yet. Create one to start scheduling.</div>
        <?php endif; ?>
      </div>
    </aside>

    <section class="planner-panel calendar-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Calendar</p>
          <h3>Plan your week</h3>
          <p class="muted small">Drop vw_outfits on any date; drag to move.</p>
        </div>
      </div>
      <div class="calendar-surface">
        <div id="calendar"></div>
        <div id="outfitDropZone" class="calendar-dropzone" aria-label="Drop outfit to schedule">
          <div class="dropzone-text">
            <strong>Drop an outfit here</strong>
            <span>Pick a date and we will schedule it</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<!-- Event Edit Modal -->
<div id="eventModal" class="modal" aria-hidden="true" style="display:none;">
  <div class="modal-content card">
    <div class="modal-header">
      <h3 id="modalTitle">Edit Plan</h3>
      <button id="modalClose" class="btn">✕</button>
    </div>
    <form id="modalForm">
      <input type="hidden" name="id" id="modalPlanId" value="">
      <div style="margin:0.5rem 0;">
        <label>Outfit</label>
        <div id="modalOutfitTitle" style="font-weight:700; margin-bottom:0.4rem;"></div>
      </div>
      <div class="form-group">
        <label>Note</label>
        <input type="text" name="note" id="modalNote" maxlength="255">
      </div>
      <div class="form-group">
        <label>Season</label>
        <select name="season_hint" id="modalSeason">
          <option value="all">All</option>
          <option value="spring">Spring</option>
          <option value="summer">Summer</option>
          <option value="fall">Fall</option>
          <option value="winter">Winter</option>
        </select>
      </div>
      <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:0.5rem;">
        <button type="button" id="modalDelete" class="btn btn-danger">Delete</button>
        <button type="submit" id="modalSave" class="btn btn-primary">Save</button>
        <button type="button" id="modalCancel" class="btn btn-secondary">Close</button>
      </div>
    </form>
  </div>
</div>

<style>
/* Modal styling */
#eventModal { position: fixed; inset: 0; display: grid; place-items: center; z-index: 9999; background: rgba(0,0,0,0.7); pointer-events: auto; }
#eventModal[aria-hidden="true"] { display: none !important; pointer-events: none; }
.modal-content { width: 520px; max-width: calc(100% - 36px); padding: 1rem; pointer-events: auto; }
.modal-header { display:flex; justify-content:space-between; align-items:center; gap:0.5rem; }
#eventModal .modal-header h3 { color: #fff; margin: 0; }
#eventModal label { display:block; margin-bottom:0.4rem; color: #e2e8f0; font-weight: 500; }
#modalOutfitTitle { color: #f1f5f9; }
#eventModal .form-group label { display:block; margin-bottom:0.4rem; color: #e2e8f0; }
#eventModal .form-group input { width:100%; padding:0.6rem; border-radius:4px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.08); color:#fff; }
#eventModal .form-group select { width:100%; padding:0.6rem; border-radius:4px; border:1px solid rgba(255,255,255,0.2); background:#1e293b; color:#f1f5f9; }
#eventModal .form-group select option { background:#1e293b; color:#f1f5f9; padding:0.5rem; }
#eventModal .btn { padding:0.45rem 0.8rem; }
#eventModal .btn-danger { background: linear-gradient(135deg,#f87171,#fb7185); border: none; color:#fff; }
#eventModal .btn-secondary { background: #95a5a6; color:#fff; }
#eventModal .btn-primary { background: linear-gradient(135deg,var(--accent),var(--accent-2)); border:none; color:#fff; }
#eventModal .modal-content { background: rgba(15, 23, 42, 0.98); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; }
.calendar-dropzone {
  margin-top: 0.75rem;
  border: 1px dashed rgba(255,255,255,0.25);
  border-radius: 14px;
  min-height: 140px;
  display: grid;
  place-items: center;
  background: rgba(255,255,255,0.04);
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
  color: #dbeafe;
  text-align: center;
  transition: border-color 120ms ease, background 120ms ease;
}
.calendar-dropzone.drag-over {
  border-color: rgba(91,123,255,0.6);
  background: rgba(91,123,255,0.08);
}
.calendar-dropzone .dropzone-text strong { display:block; font-size:1rem; margin-bottom:0.15rem; }
.calendar-dropzone .dropzone-text span { color: rgba(232,237,247,0.75); font-size:0.95rem; }
</style>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const calendarEl = document.getElementById('calendar');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    editable: true,
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
    eventSources: [{
      url: '<?=h(url_path('src/planner/events.php'))?>',
      method: 'GET'
    }],
    eventDrop: async function(info) {
      // move event
      try {
        const form = new FormData();
        form.append('id', info.event.id);
        form.append('date', info.event.start.toISOString().slice(0,10));
        form.append('csrf_token', csrf);
        const res = await fetch('<?=h(url_path('src/planner/move.php'))?>', { method: 'POST', body: form });
        if (!res.ok) {
          window.showToast('Failed to move event', 'error');
          info.revert();
        } else {
          window.showToast('Event moved', 'success');
        }
      } catch (e) {
        console.error(e);
        info.revert();
      }
    },
    eventClick: function(info) {
      // open modal to edit notes/season or delete
      const props = info.event.extendedProps;
      const note = props.note || '';
      const season = props.season_hint || 'all';
      const planId = info.event.id;
      const outfitTitle = info.event.title;
      // Populate modal
      document.getElementById('modalPlanId').value = planId;
      document.getElementById('modalOutfitTitle').innerText = outfitTitle;
      document.getElementById('modalNote').value = note;
      document.getElementById('modalSeason').value = season;
      // show modal
      const modal = document.getElementById('eventModal');
      modal.setAttribute('aria-hidden', 'false');
      modal.style.display = 'grid';
      document.getElementById('modalNote').focus();
      // store current event on modal for later updates
      modal._currentEvent = info.event;
    },
    eventContent: function(arg) {
      const el = document.createElement('div');
      el.style.display = 'flex';
      el.style.alignItems = 'center';
      el.style.gap = '6px';
      if (arg.event.extendedProps.image) {
        const img = document.createElement('img');
        img.src = arg.event.extendedProps.image;
        img.style.width = '26px';
        img.style.height = '26px';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '6px';
        img.style.border = '1px solid rgba(255,255,255,0.08)';
        el.appendChild(img);
      }
      const title = document.createElement('span');
      title.innerText = arg.event.title;
      el.appendChild(title);
      return { domNodes: [el] };
    }
  });
  calendar.render();
  
  // Native HTML5 drag/drop implementation
  let draggedOutfitId = null;
  let draggedOutfitTitle = null;
  let draggedOutfitImage = null;
  
  document.querySelectorAll('.outfit-draggable').forEach(el => {
    el.addEventListener('dragstart', (e) => {
      draggedOutfitId = el.dataset.outfitId || '';
      draggedOutfitTitle = el.dataset.outfitTitle || 'Outfit';
      draggedOutfitImage = el.dataset.image || '';
      e.dataTransfer.setData('text/plain', draggedOutfitId);
      e.dataTransfer.effectAllowed = 'copy';
      el.style.opacity = '0.5';
      console.log('Drag started:', draggedOutfitId, draggedOutfitTitle);
    });
    
    el.addEventListener('dragend', (e) => {
      el.style.opacity = '1';
      console.log('Drag ended');
    });
  });
  
  // Enable drops on calendar container
  const calendarContainer = document.querySelector('.calendar-surface');
  if (calendarContainer) {
    calendarContainer.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.dataTransfer.dropEffect = 'copy';
    });
    
    calendarContainer.addEventListener('drop', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      console.log('Drop on calendar, outfit ID:', draggedOutfitId);
      
      if (!draggedOutfitId) {
        console.warn('No outfit ID found');
        window.showToast('Please try dragging again', 'error');
        return;
      }
      
      const today = new Date().toISOString().slice(0,10);
      const planned = await Modal.prompt(`Plan "${draggedOutfitTitle}" for which date?`, today, 'YYYY-MM-DD');
      if (!planned) return;
      
      try {
        const form = new FormData();
        form.append('outfit_id', draggedOutfitId);
        form.append('planned_for', planned);
        form.append('csrf_token', csrf);
        const res = await fetch('<?=h(url_path('src/planner/plan.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (res.ok) {
          window.showToast('Planned outfit for ' + planned, 'success');
          calendar.refetchEvents();
        } else {
          window.showToast('Failed to plan outfit', 'error');
        }
      } catch (err) {
        console.error(err);
        window.showToast('Error planning outfit', 'error');
      }
    });
  }
  // Real-time updates via socket.io (preferred) or SSE fallback
  const socketServerUrl = document.querySelector('meta[name="socket-server-url"]')?.getAttribute('content') || '';
  const socketToken = document.querySelector('meta[name="socket-token"]')?.getAttribute('content') || '';
  if (typeof io !== 'undefined' && socketServerUrl) {
    const sock = io(socketServerUrl, { 
      auth: { token: socketToken },
      timeout: 5000,
      reconnectionDelay: 2000,
      reconnectionDelayMax: 10000,
      reconnectionAttempts: 3
    });
    sock.on('planner_update', payload => {
      try { calendar.refetchEvents(); window.showToast('Planner updated', 'info'); if (typeof window.incrementLiveBadge === 'function') window.incrementLiveBadge(); } catch (err) {}
    });
    sock.on('connect', () => { window.showToast('Live sync connected', 'success'); });
    sock.on('disconnect', () => { window.showToast('Live sync disconnected', 'info'); });
    sock.on('connect_error', (err) => {
      console.warn('Socket connect error', err); window.showToast('Real-time updates unavailable', 'error');
    });
  } else if (!!window.EventSource) {
    const es = new EventSource('<?=h(url_path('src/planner/stream.php'))?>');
    es.onmessage = e => {
      try {
        const d = JSON.parse(e.data);
        if (d.type === 'planner_update') {
          calendar.refetchEvents();
        }
      } catch (err) {}
    };
    es.onerror = (err) => {
      console.warn('SSE connection error', err);
      es.close();
    };
  }
  // Dedicated dropzone
  const dropZone = document.getElementById('outfitDropZone');
  if (dropZone) {
    const toggleZone = (on) => dropZone.classList.toggle('drag-over', !!on);
    dropZone.addEventListener('dragover', (e) => { 
      e.preventDefault();
      e.stopPropagation();
      toggleZone(true); 
    });
    dropZone.addEventListener('dragleave', () => toggleZone(false));
    dropZone.addEventListener('drop', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleZone(false);
      console.log('Drop on dropzone, outfit ID:', draggedOutfitId);
      
      if (!draggedOutfitId) {
        console.warn('No outfit ID found in dropzone');
        window.showToast('Please try dragging again', 'error');
        return;
      }
      
      const today = new Date().toISOString().slice(0,10);
      const planned = await Modal.prompt(`Plan "${draggedOutfitTitle}" for which date?`, today, 'YYYY-MM-DD');
      if (!planned) return;
      
      try {
        const form = new FormData();
        form.append('outfit_id', draggedOutfitId);
        form.append('planned_for', planned);
        form.append('csrf_token', csrf);
        form.append('redirect', '<?=h(url_path("src/planner/calendar.php"))?>');
        const res = await fetch('<?=h(url_path('src/planner/plan.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (res.ok) {
          window.showToast('Planned outfit for ' + planned, 'success');
          calendar.refetchEvents();
        } else {
          window.showToast('Failed to plan outfit', 'error');
        }
      } catch (err) {
        console.error(err);
        window.showToast('Error planning outfit', 'error');
      }
    });
  }
  // Modal handlers
  const modal = document.getElementById('eventModal');
  const modalClose = document.getElementById('modalClose');
  const modalCancel = document.getElementById('modalCancel');
  const modalForm = document.getElementById('modalForm');
  const modalDelete = document.getElementById('modalDelete');
  const modalSave = document.getElementById('modalSave');

  function closeModal() {
    modal.setAttribute('aria-hidden', 'true');
    modal.style.display = 'none';
    modal._currentEvent = null;
  }
  modalClose.addEventListener('click', closeModal);
  modalCancel.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // Submit update via AJAX
  modalForm.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const planId = document.getElementById('modalPlanId').value;
    const note = document.getElementById('modalNote').value;
    const season = document.getElementById('modalSeason').value;
    try {
      modalSave.disabled = true;
      const form = new FormData();
      form.append('id', planId);
      form.append('note', note);
      form.append('season_hint', season);
      form.append('csrf_token', csrf);
      const res = await fetch('<?=h(url_path('src/planner/update.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const json = await res.json();
      if (json.success) {
            // reflect in event and refetch events
        const event = modal._currentEvent;
        if (event) {
          event.setExtendedProp('note', note);
          event.setExtendedProp('season_hint', season);
        }
            calendar.refetchEvents();
            closeModal();
      } else {
          window.showToast('Failed to update plan', 'error');
      }
    } catch (e) { console.error(e); window.showToast('Error while updating plan', 'error'); }
    modalSave.disabled = false;
  });

  // Delete via AJAX
  modalDelete.addEventListener('click', async () => {
    if (!await Modal.confirm('Are you sure you want to delete this plan?', 'danger')) return;
    const planId = document.getElementById('modalPlanId').value;
    try {
      modalDelete.disabled = true;
      const form = new FormData();
      form.append('id', planId);
      form.append('csrf_token', csrf);
      const res = await fetch('<?=h(url_path('src/planner/delete.php'))?>', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const json = await res.json();
      if (json.success) {
        // refresh events to update calendar view
        calendar.refetchEvents();
        closeModal();
        window.showToast('Plan deleted', 'success');
      } else {
        window.showToast('Failed to delete plan', 'error');
      }
    } catch (e) { console.error(e); window.showToast('Error while deleting plan', 'error'); }
    modalDelete.disabled = false;
  });
});
</script>
<?php include __DIR__ . '/../templates/footer.php'; ?>
