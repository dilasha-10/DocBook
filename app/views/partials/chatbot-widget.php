<?php
// chatbot-widget.php
// Included at the bottom of app.php (patient layout).
// Only renders when a patient is logged in.
if (!isset($user) || ($user['role'] ?? '') !== 'patient') return;
?>

<!-- CHATBOT FLOATING WIDGET -->
<style>
/* ── Launcher button ── */
#cbLauncher {
    position: fixed;
    bottom: 28px;
    right: 28px;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: var(--blue, #2563eb);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 4px 18px rgba(37,99,235,.38);
    z-index: 9990;
    transition: transform .18s, box-shadow .18s;
}
#cbLauncher:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(37,99,235,.48); }
#cbLauncher .cb-open-icon  { display: block; }
#cbLauncher .cb-close-icon { display: none; }
#cbLauncher.open .cb-open-icon  { display: none; }
#cbLauncher.open .cb-close-icon { display: block; }

/* Unread badge */
#cbBadge {
    position: absolute;
    top: 2px; right: 2px;
    width: 16px; height: 16px;
    background: #ef4444;
    border-radius: 50%;
    border: 2px solid #fff;
    display: none;
    font-size: 9px;
    font-weight: 700;
    color: #fff;
    align-items: center;
    justify-content: center;
}
#cbBadge.visible { display: flex; }

/* ── Chat window ── */
#cbWindow {
    position: fixed;
    bottom: 92px;
    right: 28px;
    width: 360px;
    max-width: calc(100vw - 32px);
    height: 500px;
    max-height: calc(100vh - 120px);
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 18px;
    box-shadow: 0 12px 48px rgba(0,0,0,.14);
    display: flex;
    flex-direction: column;
    z-index: 9989;
    overflow: hidden;
    opacity: 0;
    transform: translateY(16px) scale(.97);
    pointer-events: none;
    transition: opacity .22s, transform .22s;
}
#cbWindow.open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: all;
}

/* Header */
.cb-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    background: var(--blue, #2563eb);
    color: #fff;
    flex-shrink: 0;
}
.cb-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: rgba(255,255,255,.22);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.cb-header-info { flex: 1; }
.cb-header-info strong { display: block; font-size: 14px; font-weight: 700; }
.cb-header-info span   { font-size: 11px; opacity: .82; }
.cb-clear-btn {
    background: rgba(255,255,255,.18);
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 4px 9px;
    font-size: 11px;
    cursor: pointer;
    transition: background .15s;
}
.cb-clear-btn:hover { background: rgba(255,255,255,.30); }

/* Messages area */
.cb-messages {
    flex: 1;
    overflow-y: auto;
    padding: 14px 14px 6px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}
.cb-messages::-webkit-scrollbar { width: 4px; }
.cb-messages::-webkit-scrollbar-thumb { background: var(--border2, #d1d5db); border-radius: 4px; }

/* Bubbles */
.cb-msg {
    display: flex;
    flex-direction: column;
    max-width: 84%;
}
.cb-msg.user  { align-self: flex-end; align-items: flex-end; }
.cb-msg.bot   { align-self: flex-start; align-items: flex-start; }

.cb-bubble {
    padding: 10px 13px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.6;
    white-space: normal;
    word-break: break-word;
    text-align: left;
}
.cb-bullet-block {
    margin: 3px 0;
}
.cb-bullet-line {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    line-height: 1.5;
    margin: 1px 0;
}
.cb-bullet-dot {
    flex-shrink: 0;
    font-size: 9px;
    margin-top: 4px;
    color: currentColor;
}
.cb-msg.user .cb-bubble {
    background: var(--blue, #2563eb);
    color: #fff;
    border-bottom-right-radius: 4px;
}
.cb-msg.bot .cb-bubble {
    background: var(--bg, #f3f4f6);
    color: var(--text, #111);
    border-bottom-left-radius: 4px;
    border: 1px solid var(--border, #e5e7eb);
}
[data-theme="dark"] .cb-msg.bot .cb-bubble {
    background: var(--surface2, #404040);
    border-color: var(--border, #4a4a4a);
}
.cb-time {
    font-size: 10.5px;
    color: var(--muted, #6b7280);
    margin-top: 3px;
    padding: 0 3px;
}

/* Typing indicator */
.cb-typing .cb-bubble {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 12px 16px;
}
.cb-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--muted, #9ca3af);
    animation: cbDot 1.2s infinite ease-in-out;
}
.cb-dot:nth-child(2) { animation-delay: .18s; }
.cb-dot:nth-child(3) { animation-delay: .36s; }
@keyframes cbDot { 0%,80%,100%{transform:scale(.7);opacity:.5} 40%{transform:scale(1);opacity:1} }

/* Escalated notice */
.cb-escalated-notice {
    margin: 4px 0 2px;
    padding: 9px 12px;
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 10px;
    font-size: 12px;
    color: #92400e;
    display: flex;
    align-items: flex-start;
    gap: 7px;
}
[data-theme="dark"] .cb-escalated-notice {
    background: rgba(251,191,36,.12);
    border-color: rgba(251,191,36,.30);
    color: #fcd34d;
}
.cb-escalated-notice i { margin-top: 1px; flex-shrink: 0; }

/* Quick replies */
.cb-quick-replies {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 4px 14px 10px;
}
.cb-qr {
    background: var(--bg, #f3f4f6);
    border: 1px solid var(--border, #e5e7eb);
    color: var(--blue, #2563eb);
    border-radius: 20px;
    padding: 5px 13px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .14s, border-color .14s;
    white-space: nowrap;
}
.cb-qr:hover {
    background: #eff6ff;
    border-color: var(--blue, #2563eb);
}
[data-theme="dark"] .cb-qr {
    background: var(--surface2, #404040);
    border-color: var(--border, #4a4a4a);
    color: #60a5fa;
}
[data-theme="dark"] .cb-qr:hover { background: rgba(96,165,250,.12); }

/* Input bar */
.cb-input-bar {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding: 10px 12px 12px;
    border-top: 1px solid var(--border, #e5e7eb);
    flex-shrink: 0;
}
.cb-input {
    flex: 1;
    resize: none;
    border: 1px solid var(--border, #d1d5db);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 13.5px;
    font-family: inherit;
    background: var(--bg, #f9fafb);
    color: var(--text, #111);
    outline: none;
    max-height: 90px;
    line-height: 1.4;
    transition: border-color .15s;
}
.cb-input:focus { border-color: var(--blue, #2563eb); }
[data-theme="dark"] .cb-input {
    background: var(--surface2, #404040);
    border-color: var(--border, #4a4a4a);
    color: var(--text, #f1f5f9);
}
.cb-send-btn {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: var(--blue, #2563eb);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    transition: background .15s, transform .12s;
}
.cb-send-btn:hover  { background: #1d4ed8; }
.cb-send-btn:active { transform: scale(.92); }
.cb-send-btn:disabled { opacity: .5; cursor: not-allowed; }
</style>

<!-- Launcher -->
<button id="cbLauncher" aria-label="Open assistant">
    <i class="fa fa-comment-medical cb-open-icon"></i>
    <i class="fa fa-xmark cb-close-icon"></i>
    <span id="cbBadge"></span>
</button>

<!-- Chat window -->
<div id="cbWindow" role="dialog" aria-label="DocBook Assistant">
    <div class="cb-header">
        <div class="cb-avatar"><i class="fa fa-robot"></i></div>
        <div class="cb-header-info">
            <strong>DocBook Assistant</strong>
            <span>Usually replies instantly</span>
        </div>
        <button class="cb-clear-btn" onclick="cbClear()" title="Clear chat">
            <i class="fa fa-rotate-right"></i> Clear
        </button>
    </div>

    <div class="cb-messages" id="cbMessages"></div>

    <div class="cb-quick-replies" id="cbQuickReplies">
        <button class="cb-qr" onclick="cbQuickSend('How do I book an appointment?')">Book Appointment</button>
        <button class="cb-qr" onclick="cbQuickSend('How do I cancel my appointment?')">Cancel Appointment</button>
        <button class="cb-qr" onclick="cbQuickSend('How do I reschedule my appointment?')">Reschedule Appointment</button>
        <button class="cb-qr" onclick="cbQuickSend('How does payment work?')">Payment &amp; Fees</button>
        <button class="cb-qr" onclick="cbQuickSend('How do I find a doctor?')">Find a Doctor</button>
        <button class="cb-qr" onclick="cbQuickSend('What is my appointment status?')">Appointment Status</button>
        <button class="cb-qr" onclick="cbQuickSend('How do I message my doctor?')">Message Doctor</button>
        <button class="cb-qr" onclick="cbQuickSend('How do I update my profile?')">Update Profile</button>
    </div>

    <div class="cb-input-bar">
        <textarea class="cb-input" id="cbInput" rows="1"
                  placeholder="Ask me anything…" aria-label="Message"></textarea>
        <button class="cb-send-btn" id="cbSendBtn" onclick="cbSend()" aria-label="Send">
            <i class="fa fa-arrow-up"></i>
        </button>
    </div>
</div>

<script>
(function () {
    var STORAGE_KEY = 'docbook_chat_history';
    var history     = [];   // [{role:'user'|'bot', message:'', ts:''}]
    var badgeCount  = 0;
    var isOpen      = false;
    var isBusy      = false;

    var launcher  = document.getElementById('cbLauncher');
    var win       = document.getElementById('cbWindow');
    var msgArea   = document.getElementById('cbMessages');
    var input     = document.getElementById('cbInput');
    var sendBtn   = document.getElementById('cbSendBtn');
    var badge     = document.getElementById('cbBadge');
    var quickWrap = document.getElementById('cbQuickReplies');

    // Persist & restore
    function saveHistory() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(history)); } catch(e) {}
    }
    function loadHistory() {
        try {
            var stored = localStorage.getItem(STORAGE_KEY);
            if (stored) history = JSON.parse(stored);
        } catch(e) { history = []; }
    }

    // Render
    function ts() {
        return new Date().toLocaleTimeString('en-US', {hour:'numeric', minute:'2-digit'});
    }

    function renderMessage(role, message, time, extraClass) {
        var div = document.createElement('div');
        div.className = 'cb-msg ' + role + (extraClass ? ' ' + extraClass : '');

        var bubble = document.createElement('div');
        bubble.className = 'cb-bubble';
        // Render **bold**, convert bullet lines to tight compact list
        var lines = escHtml(message)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .split('\n');

        var html = '';
        var i = 0;
        while (i < lines.length) {
            var line = lines[i];
            if (line.startsWith('• ')) {
                // Collect consecutive bullet lines into one block
                html += '<div class="cb-bullet-block">';
                while (i < lines.length && lines[i].startsWith('• ')) {
                    var text = lines[i].substring(2);
                    html += '<div class="cb-bullet-line"><span class="cb-bullet-dot">●</span><span>' + text + '</span></div>';
                    i++;
                }
                html += '</div>';
            } else if (line === '') {
                i++;
            } else {
                html += '<div>' + line + '</div>';
                i++;
            }
        }

        bubble.innerHTML = html;

        var timeEl = document.createElement('span');
        timeEl.className = 'cb-time';
        timeEl.textContent = time || ts();

        div.appendChild(bubble);
        div.appendChild(timeEl);
        msgArea.appendChild(div);
        scrollBottom();
        return div;
    }

    function renderEscalatedNotice() {
        var div = document.createElement('div');
        div.className = 'cb-escalated-notice';
        div.innerHTML = '<i class="fa fa-circle-exclamation"></i><span>Your message has been forwarded to our admin team. They\'ll follow up with you soon.</span>';
        msgArea.appendChild(div);
        scrollBottom();
    }

    function renderTyping() {
        var div = document.createElement('div');
        div.className = 'cb-msg bot cb-typing';
        div.id = 'cbTyping';
        div.innerHTML = '<div class="cb-bubble"><span class="cb-dot"></span><span class="cb-dot"></span><span class="cb-dot"></span></div>';
        msgArea.appendChild(div);
        scrollBottom();
        return div;
    }

    function removeTyping() {
        var el = document.getElementById('cbTyping');
        if (el) el.remove();
    }

    function scrollBottom() {
        msgArea.scrollTop = msgArea.scrollHeight;
    }

    function escHtml(s) {
        return String(s || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function rebuildMessages() {
        msgArea.innerHTML = '';
        history.forEach(function(m) { renderMessage(m.role, m.message, m.ts); });
    }

    // Open / Close
    function openChat() {
        isOpen = true;
        win.classList.add('open');
        launcher.classList.add('open');
        badgeCount = 0;
        badge.classList.remove('visible');
        if (history.length === 0) showWelcome();
        else rebuildMessages();
        setTimeout(function(){ input.focus(); }, 220);
    }

    function closeChat() {
        isOpen = false;
        win.classList.remove('open');
        launcher.classList.remove('open');
    }

    function showWelcome() {
        var welcome = "Hi! I'm the DocBook assistant. How can I help you today?\n\nYou can ask me about booking, cancelling, or rescheduling appointments, payments, or finding a doctor.";
        var t = ts();
        renderMessage('bot', welcome, t);
        history.push({ role: 'bot', message: welcome, ts: t });
        saveHistory();
    }

    launcher.addEventListener('click', function() {
        isOpen ? closeChat() : openChat();
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) closeChat();
    });

    // Send
    function cbSend() {
        if (isBusy) return;
        var msg = input.value.trim();
        if (!msg) return;

        // Hide quick replies after first message
        if (quickWrap) quickWrap.style.display = 'none';

        var t = ts();
        renderMessage('user', msg, t);
        history.push({ role: 'user', message: msg, ts: t });
        saveHistory();

        input.value = '';
        input.style.height = 'auto';
        isBusy = true;
        sendBtn.disabled = true;

        // Small delay to feel natural
        var typing = renderTyping();
        setTimeout(function() { doFetch(msg); }, 420);
    }

    function doFetch(msg) {
        fetch(BASE_URL + '/api/chatbot/message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msg, history: history })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            removeTyping();
            if (!res.success) {
                var errT = ts();
                renderMessage('bot', 'Sorry, something went wrong. Please try again.', errT);
                history.push({ role: 'bot', message: 'Sorry, something went wrong. Please try again.', ts: errT });
                saveHistory();
                return;
            }

            var t = ts();
            renderMessage('bot', res.answer, t);
            history.push({ role: 'bot', message: res.answer, ts: t });
            saveHistory();

            if (res.escalated) {
                // Save escalation to backend
                fetch(BASE_URL + '/api/chatbot/escalate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_query: msg, conversation: history })
                }).catch(function(){});
                renderEscalatedNotice();
            }

            if (!isOpen) {
                badgeCount++;
                badge.textContent = badgeCount;
                badge.classList.add('visible');
            }
        })
        .catch(function() {
            removeTyping();
            var t = ts();
            renderMessage('bot', 'Network error. Please check your connection.', t);
            history.push({ role: 'bot', message: 'Network error. Please check your connection.', ts: t });
            saveHistory();
        })
        .finally(function() {
            isBusy = false;
            sendBtn.disabled = false;
            input.focus();
        });
    }

    // Public helpers
    window.cbSend = cbSend;

    window.cbQuickSend = function(msg) {
        if (isBusy) return;
        input.value = msg;
        cbSend();
    };

    window.cbClear = function() {
        history = [];
        saveHistory();
        msgArea.innerHTML = '';
        if (quickWrap) quickWrap.style.display = 'flex';
        showWelcome();
    };

    // Input auto-resize + Enter key
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 90) + 'px';
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); cbSend(); }
    });

    // Init
    loadHistory();
})();
</script>
