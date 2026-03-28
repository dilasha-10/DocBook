<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>DocBook — Doctor Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />
<style>
/* ── Reset & Variables ─────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:        #111318;
  --surface:   #1a1d24;
  --surface2:  #22262f;
  --surface3:  #2b303c;
  --border:    rgba(255,255,255,0.07);
  --text:      #e8eaf0;
  --muted:     #7a8099;
  --accent:    #4f8ef7;
  --accent-dim:#1e3a6e;
  --green:     #34c97b;
  --green-dim: #0d3324;
  --amber:     #f5a623;
  --amber-dim: #3d2800;
  --red:       #e05252;
  --red-dim:   #3b1010;
  --purple:    #9b72f7;
  --sidebar-w: 220px;
  --topbar-h:  56px;
  --panel-w:   380px;
  --radius:    12px;
  --font:      'DM Sans', sans-serif;
  --mono:      'DM Mono', monospace;
}

html, body { height: 100%; overflow: hidden; }

body {
  font-family: var(--font);
  background: var(--bg);
  color: var(--text);
  font-size: 14px;
  line-height: 1.5;
}

/* ── Topbar ────────────────────────────────────────────────────── */
.topbar {
  position: fixed; top: 0; left: 0; right: 0;
  height: var(--topbar-h);
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center;
  padding: 0 24px;
  gap: 32px;
  z-index: 100;
}

.logo {
  font-size: 18px; font-weight: 600; letter-spacing: -0.4px;
  color: var(--text); white-space: nowrap;
}
.logo span { color: var(--accent); }

.top-nav { display: flex; gap: 4px; list-style: none; flex: 1; }
.top-nav a {
  padding: 6px 14px; border-radius: 8px;
  color: var(--muted); text-decoration: none; font-weight: 500;
  transition: color .15s, background .15s;
}
.top-nav a:hover { color: var(--text); background: var(--surface2); }
.top-nav a.active { color: var(--text); background: var(--surface3); }

.top-right {
  display: flex; align-items: center; gap: 12px; margin-left: auto;
}
.avatar-chip {
  display: flex; align-items: center; gap: 8px;
  background: var(--surface2); border-radius: 100px;
  padding: 4px 14px 4px 4px;
  font-size: 13px; font-weight: 500;
}
.avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: var(--accent); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 600;
}
.btn-signout {
  padding: 7px 16px; border-radius: 8px;
  background: var(--red-dim); color: var(--red);
  border: 1px solid rgba(224,82,82,0.3);
  font-family: var(--font); font-size: 13px; font-weight: 500;
  cursor: pointer; transition: background .15s;
}
.btn-signout:hover { background: rgba(224,82,82,0.2); }

/* ── Layout ────────────────────────────────────────────────────── */
.app {
  display: flex;
  padding-top: var(--topbar-h);
  height: 100%;
}

/* ── Sidebar ───────────────────────────────────────────────────── */
.sidebar {
  width: var(--sidebar-w);
  background: var(--surface);
  border-right: 1px solid var(--border);
  padding: 24px 12px;
  display: flex; flex-direction: column;
  gap: 32px;
  flex-shrink: 0;
  overflow-y: auto;
}

.nav-group { display: flex; flex-direction: column; gap: 2px; }
.nav-label {
  font-size: 10px; font-weight: 600; letter-spacing: 1.2px;
  color: var(--muted); padding: 0 8px 8px;
  text-transform: uppercase;
}

.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px; border-radius: 8px;
  color: var(--muted); text-decoration: none;
  font-weight: 500; font-size: 14px;
  transition: all .15s; cursor: pointer;
}
.nav-item svg { flex-shrink: 0; opacity: 0.7; }
.nav-item:hover { color: var(--text); background: var(--surface2); }
.nav-item.active { color: var(--accent); background: var(--accent-dim); }
.nav-item.active svg { opacity: 1; }

/* ── Main ──────────────────────────────────────────────────────── */
.main {
  flex: 1;
  overflow-y: auto;
  padding: 32px 28px;
  min-width: 0;
  transition: margin-right .3s ease;
}
.main.panel-open { margin-right: var(--panel-w); }

/* ── Page Header ───────────────────────────────────────────────── */
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 28px;
}
.page-title { font-size: 26px; font-weight: 600; letter-spacing: -0.5px; }
.page-sub { color: var(--muted); font-size: 14px; margin-top: 2px; }

.btn-primary {
  padding: 9px 20px; border-radius: 10px;
  background: var(--accent); color: #fff;
  border: none; font-family: var(--font); font-size: 14px; font-weight: 500;
  cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.btn-primary:hover { opacity: 0.85; }

/* ── Stats Cards ───────────────────────────────────────────────── */
.stats {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 14px; margin-bottom: 32px;
}
@media (max-width: 900px) { .stats { grid-template-columns: repeat(2, 1fr); } }

.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
}
.stat-num { font-size: 30px; font-weight: 600; letter-spacing: -1px; margin-bottom: 4px; }
.stat-lbl { color: var(--muted); font-size: 13px; margin-bottom: 10px; }

.badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 100px;
  font-size: 11px; font-weight: 600; letter-spacing: 0.2px;
}
.badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.badge-green  { background: var(--green-dim);  color: var(--green); }
.badge-amber  { background: var(--amber-dim);  color: var(--amber); }
.badge-blue   { background: var(--accent-dim); color: var(--accent); }
.badge-purple { background: rgba(155,114,247,.15); color: var(--purple); }
.badge-red    { background: var(--red-dim);    color: var(--red); }

/* ── Section Header ────────────────────────────────────────────── */
.section-title {
  font-size: 16px; font-weight: 600; margin-bottom: 14px;
  letter-spacing: -0.2px;
}

/* ── Appointment List ──────────────────────────────────────────── */
.appt-list { display: flex; flex-direction: column; gap: 8px; }

.appt-row {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px 20px;
  display: flex; align-items: center; gap: 16px;
  cursor: pointer;
  transition: border-color .15s, background .15s;
}
.appt-row:hover { border-color: rgba(79,142,247,0.3); background: var(--surface2); }
.appt-row.active { border-color: var(--accent); background: rgba(79,142,247,0.06); }

.appt-time {
  font-family: var(--mono); font-size: 13px; font-weight: 500;
  color: var(--accent); min-width: 72px;
}
.appt-icon {
  width: 38px; height: 38px; border-radius: 50%;
  background: var(--accent-dim); color: var(--accent);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.appt-info { flex: 1; min-width: 0; }
.appt-name { font-weight: 600; font-size: 14px; }
.appt-reason { color: var(--muted); font-size: 13px; margin-top: 1px; }
.appt-actions { display: flex; align-items: center; gap: 8px; }

.btn-sm {
  padding: 5px 14px; border-radius: 7px;
  font-family: var(--font); font-size: 12px; font-weight: 600;
  cursor: pointer; border: 1px solid transparent;
  transition: all .15s;
}
.btn-accept { background: var(--green-dim); color: var(--green); border-color: rgba(52,201,123,0.3); }
.btn-accept:hover { background: rgba(52,201,123,0.2); }
.btn-reject { background: var(--red-dim); color: var(--red); border-color: rgba(224,82,82,0.3); }
.btn-reject:hover { background: rgba(224,82,82,0.2); }
.btn-view { background: var(--surface3); color: var(--text); border-color: var(--border); }
.btn-view:hover { background: var(--surface2); }

/* ── Patient Detail Panel ──────────────────────────────────────── */
.detail-panel {
  position: fixed; top: var(--topbar-h); right: 0; bottom: 0;
  width: var(--panel-w);
  background: var(--surface);
  border-left: 1px solid var(--border);
  display: flex; flex-direction: column;
  transform: translateX(100%);
  transition: transform .3s cubic-bezier(.4,0,.2,1);
  z-index: 90;
}
.detail-panel.open { transform: translateX(0); }

.panel-header {
  padding: 20px 20px 16px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: flex-start; gap: 12px;
  flex-shrink: 0;
}
.panel-avatar {
  width: 46px; height: 46px; border-radius: 50%;
  background: var(--accent-dim); color: var(--accent);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; font-weight: 700; flex-shrink: 0;
}
.panel-meta { flex: 1; min-width: 0; }
.panel-name { font-size: 16px; font-weight: 600; }
.panel-reason { color: var(--muted); font-size: 13px; margin-top: 3px; }
.panel-time { font-family: var(--mono); font-size: 12px; color: var(--accent); margin-top: 4px; }

.btn-close {
  background: none; border: none; color: var(--muted);
  cursor: pointer; padding: 4px; border-radius: 6px;
  transition: color .15s, background .15s; flex-shrink: 0;
}
.btn-close:hover { color: var(--text); background: var(--surface2); }

.comment-history {
  flex: 1; overflow-y: auto;
  padding: 16px 20px;
  display: flex; flex-direction: column; gap: 12px;
}
.comment-empty {
  color: var(--muted); font-size: 13px;
  text-align: center; padding: 32px 0;
}
.comment-item {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 12px 14px;
  animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

.comment-meta {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 6px;
}
.comment-author { font-size: 12px; font-weight: 600; color: var(--accent); }
.comment-date   { font-size: 11px; color: var(--muted); font-family: var(--mono); }
.comment-text   { font-size: 13px; color: var(--text); line-height: 1.55; }

.comment-composer {
  padding: 16px 20px;
  border-top: 1px solid var(--border);
  display: flex; flex-direction: column; gap: 10px;
  flex-shrink: 0;
}
.composer-label { font-size: 12px; font-weight: 600; color: var(--muted); letter-spacing: 0.5px; text-transform: uppercase; }

.comment-textarea {
  width: 100%; min-height: 80px; max-height: 140px;
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: 10px; padding: 10px 14px;
  color: var(--text); font-family: var(--font); font-size: 13px;
  resize: vertical; outline: none; transition: border-color .15s;
}
.comment-textarea:focus { border-color: var(--accent); }
.comment-textarea::placeholder { color: var(--muted); }

.btn-save {
  align-self: flex-end;
  padding: 9px 20px; border-radius: 9px;
  background: var(--accent); color: #fff;
  border: none; font-family: var(--font); font-size: 13px; font-weight: 600;
  cursor: pointer; transition: opacity .15s;
}
.btn-save:hover { opacity: 0.85; }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Scrollbar ─────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--surface3); border-radius: 4px; }
</style>
</head>
<body>

<!-- ── Topbar ───────────────────────────────────────────────────── -->
<header class="topbar">
  <div class="logo">Doc<span>Book</span></div>
  <nav>
    <ul class="top-nav">
      <li><a href="#" class="active">Dashboard</a></li>
      <li><a href="#">Schedule</a></li>
      <li><a href="#">Patients</a></li>
      <li><a href="#">Availability</a></li>
    </ul>
  </nav>
  <div class="top-right">
    <div class="avatar-chip">
      <div class="avatar">SL</div>
      Dr. Sarah Lim
    </div>
    <button class="btn-signout">Sign out</button>
  </div>
</header>

<!-- ── App Shell ────────────────────────────────────────────────── -->
<div class="app">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="nav-group">
      <div class="nav-label">Doctor Menu</div>
      <a class="nav-item active" href="#">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>
      <a class="nav-item" href="#">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        My Schedule
      </a>
      <a class="nav-item" href="#">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Patients
      </a>
      <a class="nav-item" href="#">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        Availability
      </a>
    </div>
    <div class="nav-group">
      <div class="nav-label">Account</div>
      <a class="nav-item" href="#">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Profile
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main" id="mainContent">

    <div class="page-header">
      <div>
        <div class="page-title">Good morning, Dr. Lim</div>
        <div class="page-sub">You have <?= count($appointments) ?> appointments today</div>
      </div>
      <button class="btn-primary">Update availability</button>
    </div>

    <!-- Stats -->
    <?php
    $totalAppts   = count($appointments);
    $pendingCount = count(array_filter($appointments, fn($a) => $a['status'] === 'Pending'));
    ?>
    <div class="stats">
      <div class="stat-card">
        <div class="stat-num"><?= $totalAppts ?></div>
        <div class="stat-lbl">Today's appointments</div>
        <span class="badge badge-green">Active</span>
      </div>
      <div class="stat-card">
        <div class="stat-num" id="pendingCount"><?= $pendingCount ?></div>
        <div class="stat-lbl">Pending approvals</div>
        <span class="badge badge-amber">Action needed</span>
      </div>
      <div class="stat-card">
        <div class="stat-num">18</div>
        <div class="stat-lbl">This week</div>
        <span class="badge badge-green">On track</span>
      </div>
      <div class="stat-card">
        <div class="stat-num">124</div>
        <div class="stat-lbl">Total patients</div>
        <span class="badge badge-purple">All time</span>
      </div>
    </div>

    <!-- Today's Schedule -->
    <div class="section-title">Today's schedule</div>
    <div class="appt-list" id="apptList"></div>

  </main>

  <!-- Patient Detail Panel -->
  <aside class="detail-panel" id="detailPanel">
    <div class="panel-header">
      <div class="panel-avatar" id="panelAvatar"></div>
      <div class="panel-meta">
        <div class="panel-name"   id="panelName"></div>
        <div class="panel-reason" id="panelReason"></div>
        <div class="panel-time"   id="panelTime"></div>
      </div>
      <button class="btn-close" id="panelClose" title="Close">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="comment-history" id="commentHistory"></div>
    <div class="comment-composer">
      <div class="composer-label">Add a note</div>
      <textarea class="comment-textarea" id="commentInput" placeholder="Type your clinical note here…"></textarea>
      <button class="btn-save" id="saveCommentBtn">Save Comment</button>
    </div>
  </aside>

</div>

<script>
// ── Data injected from PHP (controller → view) ────────────────────
let appointments = <?= $apptJson ?>;
let activeId = null;

// ── Helpers ───────────────────────────────────────────────────────
function statusBadge(status) {
  const map = {
    'Confirmed': ['badge-green', 'Confirmed'],
    'Pending':   ['badge-amber', 'Pending'],
    'Done':      ['badge-blue',  'Done'],
    'Rejected':  ['badge-red',   'Rejected'],
  };
  const [cls, label] = map[status] || ['badge-blue', status];
  return `<span class="badge ${cls}">${label}</span>`;
}

function initials(name) {
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
}

function escapeHtml(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

// ── Render appointment list ───────────────────────────────────────
function renderList() {
  const list = document.getElementById('apptList');
  list.innerHTML = '';
  let pendingCount = 0;

  appointments.forEach(appt => {
    if (appt.status === 'Pending') pendingCount++;

    const row = document.createElement('div');
    row.className = 'appt-row' + (activeId === appt.id ? ' active' : '');
    row.dataset.id = appt.id;

    const actions = appt.status === 'Pending'
      ? `<button class="btn-sm btn-accept" data-id="${appt.id}" data-action="accept">Accept</button>
         <button class="btn-sm btn-reject" data-id="${appt.id}" data-action="reject">Reject</button>`
      : `<button class="btn-sm btn-view"   data-id="${appt.id}" data-action="view">View details</button>`;

    row.innerHTML = `
      <div class="appt-time">${appt.time}</div>
      <div class="appt-icon">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
        </svg>
      </div>
      <div class="appt-info">
        <div class="appt-name">${appt.name}</div>
        <div class="appt-reason">${appt.reason} · ${appt.duration}</div>
      </div>
      <div class="appt-actions">
        ${statusBadge(appt.status)}
        ${actions}
      </div>`;

    row.addEventListener('click', (e) => {
      if (!e.target.closest('button')) openPanel(appt.id);
    });

    list.appendChild(row);
  });

  document.getElementById('pendingCount').textContent = pendingCount;

  list.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const id  = parseInt(btn.dataset.id);
      const act = btn.dataset.action;
      if      (act === 'accept') updateStatus(id, 'Confirmed');
      else if (act === 'reject') updateStatus(id, 'Rejected');
      else                       openPanel(id);
    });
  });
}

// ── Open patient panel ────────────────────────────────────────────
function openPanel(id) {
  activeId = id;
  const appt = appointments.find(a => a.id === id);
  if (!appt) return;

  document.getElementById('panelAvatar').textContent  = initials(appt.name);
  document.getElementById('panelName').textContent    = appt.name;
  document.getElementById('panelReason').textContent  = appt.reason;
  document.getElementById('panelTime').textContent    = appt.time + ' · ' + appt.duration;
  document.getElementById('commentInput').value = '';

  renderComments(appt.comments);

  document.getElementById('detailPanel').classList.add('open');
  document.getElementById('mainContent').classList.add('panel-open');
  renderList();
}

// ── Render comments ───────────────────────────────────────────────
function renderComments(comments) {
  const hist = document.getElementById('commentHistory');
  if (!comments || comments.length === 0) {
    hist.innerHTML = '<div class="comment-empty">No notes yet for this appointment.</div>';
    return;
  }
  hist.innerHTML = comments.map(c => `
    <div class="comment-item">
      <div class="comment-meta">
        <span class="comment-author">${c.author}</span>
        <span class="comment-date">${c.date}</span>
      </div>
      <div class="comment-text">${escapeHtml(c.text)}</div>
    </div>`).join('');
  hist.scrollTop = hist.scrollHeight;
}

// ── Close panel ───────────────────────────────────────────────────
function closePanel() {
  activeId = null;
  document.getElementById('detailPanel').classList.remove('open');
  document.getElementById('mainContent').classList.remove('panel-open');
  renderList();
}

document.getElementById('panelClose').addEventListener('click', closePanel);

// ── Save comment (AJAX) ───────────────────────────────────────────
document.getElementById('saveCommentBtn').addEventListener('click', async () => {
  const text = document.getElementById('commentInput').value.trim();
  if (!text || activeId === null) return;

  const btn = document.getElementById('saveCommentBtn');
  btn.disabled = true;
  btn.textContent = 'Saving…';

  try {
    const fd = new FormData();
    fd.append('action', 'save_comment');
    fd.append('appointment_id', activeId);
    fd.append('comment', text);

    const res  = await fetch('index.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      const appt = appointments.find(a => a.id === activeId);
      if (appt) {
        appt.comments.push(data.comment);
        renderComments(appt.comments);
      }
      document.getElementById('commentInput').value = '';
    } else {
      alert(data.message || 'Failed to save comment.');
    }
  } catch (err) {
    alert('Network error. Please try again.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Save Comment';
  }
});

// ── Update status (AJAX) ──────────────────────────────────────────
async function updateStatus(id, status) {
  const fd = new FormData();
  fd.append('action', 'update_status');
  fd.append('appointment_id', id);
  fd.append('status', status);

  try {
    const res  = await fetch('index.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      const appt = appointments.find(a => a.id === id);
      if (appt) appt.status = data.status;
      renderList();
      if (activeId === id) openPanel(id);
    } else {
      alert(data.message || 'Failed to update status.');
    }
  } catch (err) {
    alert('Network error. Please try again.');
  }
}

// ── Sidebar nav highlight ─────────────────────────────────────────
document.querySelectorAll('.nav-item, .top-nav a').forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    document.querySelectorAll('.nav-item').forEach(l => l.classList.remove('active'));
    if (this.classList.contains('nav-item')) this.classList.add('active');
  });
});

// ── Init ──────────────────────────────────────────────────────────
renderList();
</script>
</body>
</html>
