<?php
$title = 'Chat with Doctor';

$appt_id    = (int) $appt['id'];
$ref        = htmlspecialchars($appt['reference_number'] ?? '#' . $appt_id);
$doc_name   = htmlspecialchars($appt['doctor_name']      ?? 'Your Doctor');
$appt_date  = htmlspecialchars($appt['appointment_date'] ?? '');
$appt_start = htmlspecialchars($appt['start_time']       ?? '');
$patient_id = (int) $user['id'];

$extra_styles = <<<CSS
<style>
/* ── Chat page layout: fills entire remaining viewport, no scroll ── */
.chat-page-wrap {
    display: flex;
    height: calc(100vh - 64px);
    background: var(--bg);
    /* Prevent the parent main-wrap from adding extra padding/scroll */
    margin: -32px -36px;
    overflow: hidden;
}

/* ── Chat main area ───────────────────────────────────────── */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: var(--bg);
}

/* ── Chat header ─────────────────────────────────────────── */
.chat-header {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 16px 28px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-shrink: 0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.chat-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 14px; font-weight: 600; color: var(--blue);
    text-decoration: none; padding: 6px 12px;
    border: 1px solid var(--border2); border-radius: 8px;
    background: var(--surface); transition: background .15s;
}
.chat-back-btn:hover { background: var(--bg); color: var(--blue); }
.chat-doctor-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--blue); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; flex-shrink: 0;
}
.chat-header-info { flex: 1; min-width: 0; }
.chat-header-info h2 { font-size: 16px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.chat-header-info p  { font-size: 13px; color: var(--muted); margin-top: 2px; }
.chat-ref-badge {
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    background: rgba(77,166,232,0.15); color: var(--blue);
    border-radius: 999px; border: 1px solid rgba(77,166,232,0.3); white-space: nowrap;
}

/* ── Messages area ─────────────────────────────────────────── */
.chat-messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 24px 28px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.chat-date-divider {
    text-align: center;
    font-size: 12px;
    color: var(--hint);
    position: relative;
    margin: 8px 0;
}
.chat-date-divider::before,
.chat-date-divider::after {
    content: ''; position: absolute; top: 50%;
    width: 40%; height: 1px; background: var(--border);
}
.chat-date-divider::before { right: 55%; }
.chat-date-divider::after  { left: 55%; }

.msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
}
.msg-row.patient { flex-direction: row-reverse; }
.msg-row.doctor  { flex-direction: row; }

.msg-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.msg-avatar.doctor-av  { background: var(--blue); color: #fff; }
.msg-avatar.patient-av { background: rgba(77,166,232,0.18); color: var(--blue); }

.msg-content { display: flex; flex-direction: column; gap: 3px; max-width: 65%; }
.msg-row.patient .msg-content { align-items: flex-end; }
.msg-row.doctor  .msg-content { align-items: flex-start; }

.msg-bubble {
    padding: 10px 15px;
    border-radius: 18px;
    font-size: 14.5px;
    line-height: 1.55;
    word-break: break-word;
}
.msg-row.patient .msg-bubble {
    background: var(--blue);
    color: #fff;
    border-bottom-right-radius: 4px;
}
.msg-row.doctor .msg-bubble {
    background: var(--surface);
    color: var(--text);
    border: 1px solid var(--border);
    border-bottom-left-radius: 4px;
}
.msg-time {
    font-size: 11px;
    color: var(--hint);
    padding: 0 4px;
}

.chat-empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    gap: 10px;
    padding: 40px 0;
}
.chat-empty-state i { font-size: 40px; color: var(--border2); }
.chat-empty-state p { font-size: 15px; }

/* ── Input bar ─────────────────────────────────────────────── */
.chat-input-bar {
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 14px 28px;
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-shrink: 0;
}
.chat-textarea {
    flex: 1;
    padding: 11px 16px;
    font-size: 15px;
    font-family: inherit;
    border: 1px solid var(--border2);
    border-radius: 12px;
    background: var(--bg);
    color: var(--text);
    resize: none;
    outline: none;
    min-height: 44px;
    max-height: 120px;
    line-height: 1.5;
    transition: border-color .15s;
}
.chat-textarea:focus { border-color: var(--blue); background: var(--surface); }
.chat-textarea::placeholder { color: var(--hint); }
.chat-send-btn {
    width: 44px; height: 44px; border-radius: 12px;
    background: var(--blue); color: #fff;
    border: none; cursor: pointer; font-size: 17px;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s; flex-shrink: 0;
}
.chat-send-btn:hover   { background: var(--blue-hover); }
.chat-send-btn:disabled { opacity: .45; cursor: not-allowed; }

@media (max-width: 932px) {
    .chat-sidebar { display: none; }
    .chat-messages-area { padding: 16px; }
    .chat-input-bar     { padding: 12px 16px; }
    .chat-header        { padding: 12px 16px; }
    .msg-content        { max-width: 82%; }
}
@media (max-width: 767px) {
    /* On mobile, account for the sidebar being hidden, reclaim that margin */
    .chat-page-wrap { margin: -20px -16px; height: calc(100vh - 64px); }
}
</style>
CSS;

ob_start();
?>

<div class="chat-page-wrap">

    <!-- Main chat -->
    <div class="chat-main">

        <!-- Header -->
        <div class="chat-header">
            <a href="/dashboard" class="chat-back-btn"><i class="fa fa-arrow-left"></i> Back</a>
            <div class="chat-doctor-avatar" id="docInitials">DR</div>
            <div class="chat-header-info">
                <h2><?= $doc_name ?></h2>
                <p>Appointment on <?= date('M j, Y', strtotime($appt_date)) ?> at <?= date('g:i A', strtotime($appt_start)) ?></p>
            </div>
            <span class="chat-ref-badge"><?= $ref ?></span>
        </div>

        <!-- Messages -->
        <div class="chat-messages-area" id="messagesArea">
            <div class="chat-empty-state" id="emptyState">
                <i class="fa fa-comments"></i>
                <p>No messages yet. Start the conversation!</p>
            </div>
        </div>

        <!-- Input -->
        <div class="chat-input-bar">
            <textarea class="chat-textarea" id="msgInput"
                placeholder="Type your message…" rows="1"
                onkeydown="handleKey(event)"></textarea>
            <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()" title="Send">
                <i class="fa fa-circle-arrow-right"></i>
            </button>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();

$doc_initials        = strtoupper(substr(strip_tags($doc_name), 0, 2));
$js_doc_name         = json_encode($doc_name);
$js_doc_initials     = json_encode($doc_initials);
$js_patient_initials = json_encode(strtoupper(substr($user['name'], 0, 2)));

$extra_scripts = <<<JS
<script>
const APPT_ID    = {$appt_id};
const PATIENT_ID = {$patient_id};
const DOC_NAME   = {$js_doc_name};
const DOC_INIT   = {$js_doc_initials};

document.getElementById('docInitials').textContent = DOC_INIT;

function formatTime(iso) {
    const d = new Date(iso.replace(' ', 'T'));
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
function formatDate(iso) {
    const d = new Date(iso.replace(' ', 'T'));
    return d.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric' });
}
function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

let lastDate = null;

function buildBubble(msg) {
    const isPatient = msg.sender_role === 'patient';
    const side      = isPatient ? 'patient' : 'doctor';
    const avClass   = isPatient ? 'patient-av' : 'doctor-av';
    const initials  = isPatient ? {$js_patient_initials} : DOC_INIT;
    const time      = formatTime(msg.created_at);
    const msgDate   = formatDate(msg.created_at);

    let html = '';
    if (msgDate !== lastDate) {
        lastDate = msgDate;
        html += '<div class="chat-date-divider">' + escHtml(msgDate) + '</div>';
    }
    html += '<div class="msg-row ' + side + '">'
        + '<div class="msg-avatar ' + avClass + '">' + escHtml(initials) + '</div>'
        + '<div class="msg-content">'
        + '<div class="msg-bubble">' + escHtml(msg.message) + '</div>'
        + '<span class="msg-time">' + time + '</span>'
        + '</div></div>';
    return html;
}

function loadMessages() {
    fetch('/api/messages/' + APPT_ID)
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const area  = document.getElementById('messagesArea');
            const empty = document.getElementById('emptyState');
            if (d.data.length === 0) {
                if (empty) empty.style.display = 'flex';
                return;
            }
            if (empty) empty.remove();
            lastDate = null;
            area.innerHTML = d.data.map(buildBubble).join('');
            area.scrollTop = area.scrollHeight;
        });
}

function sendMessage() {
    const input = document.getElementById('msgInput');
    const msg   = input.value.trim();
    if (!msg) return;

    const btn = document.getElementById('sendBtn');
    btn.disabled = true;

    fetch('/api/messages/' + APPT_ID, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
    })
    .then(r => r.json())
    .then(d => {
        if (!d.success) { alert('Failed to send message.'); return; }
        const area  = document.getElementById('messagesArea');
        const empty = document.getElementById('emptyState');
        if (empty) empty.remove();
        area.insertAdjacentHTML('beforeend', buildBubble(d.data));
        area.scrollTop = area.scrollHeight;
        input.value = '';
        input.style.height = 'auto';
    })
    .finally(() => { btn.disabled = false; });
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// Auto-resize textarea
document.getElementById('msgInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

loadMessages();
// Poll for new messages every 8 seconds
setInterval(loadMessages, 8000);
</script>
JS;

include __DIR__ . '/../layouts/app.php';