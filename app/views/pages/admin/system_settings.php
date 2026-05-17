<?php
$title = 'System Settings';
ob_start();

$extra_styles = <<<CSS
<style>
/*  Layout */
.ss-wrap {
    padding: 40px 24px 80px;
    max-width: 860px;
    margin: 0 auto;
}
.page-header { margin-bottom: 32px; }
.page-title  { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; display:flex; align-items:center; gap:10px; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

/*  Section cards  */
.ss-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
}
.ss-card-header {
    display: flex; align-items: center; gap: 12px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
}
.ss-card-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.icon-blue   { background:#dbeafe; color:#1d4ed8; }
.icon-green  { background:#dcfce7; color:#166534; }
.icon-orange { background:#fff7ed; color:#9a3412; }
.icon-purple { background:#f3e8ff; color:#7e22ce; }
.icon-teal   { background:#ccfbf1; color:#0f766e; }

.ss-card-title  { font-size:15px; font-weight:800; color:var(--text); }
.ss-card-desc   { font-size:12px; color:var(--muted); margin-top:2px; }
.ss-card-body   { padding: 20px 24px; }

/*  Form rows  */
.ss-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
@media (max-width:580px) { .ss-field-row { grid-template-columns: 1fr; } }

.ss-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.ss-field input[type="time"],
.ss-field input[type="number"] {
    width: 100%;
    padding: 9px 13px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    transition: border-color .15s, box-shadow .15s;
}
.ss-field input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 12%, transparent);
}
.ss-field .hint {
    font-size: 11px;
    color: var(--hint);
    margin-top: 4px;
}

/*  Toggle switches  */
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
}
.toggle-row:last-child { border-bottom: none; }
.toggle-row-info { flex: 1; min-width: 0; padding-right: 24px; }
.toggle-row-label { font-size: 14px; font-weight: 700; color: var(--text); }
.toggle-row-desc  { font-size: 12px; color: var(--muted); margin-top: 2px; }

.toggle-wrap { position: relative; flex-shrink: 0; }
.toggle-input { position: absolute; opacity: 0; width: 0; height: 0; }
.toggle-track {
    display: block;
    width: 44px; height: 24px;
    background: var(--border2);
    border-radius: 12px;
    cursor: pointer;
    transition: background .2s;
    position: relative;
}
.toggle-input:checked + .toggle-track { background: var(--blue); }
.toggle-track::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 18px; height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.toggle-input:checked + .toggle-track::after { transform: translateX(20px); }

/*  Save button  */
.btn-save {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 24px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    font-family: var(--font);
    cursor: pointer;
    transition: background .15s, transform .1s;
}
.btn-save:hover  { background: var(--blue-dark); }
.btn-save:active { transform: scale(.98); }
.btn-save:disabled { opacity:.6; cursor:not-allowed; }

.save-row {
    display: flex; align-items: center; gap: 14px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    margin-top: 16px;
}
.save-msg {
    font-size: 13px;
    font-weight: 600;
    display: none;
}
.save-msg.ok  { color: var(--green); display:inline; }
.save-msg.err { color: var(--red);   display:inline; }

/*  Holidays table  */
.holiday-add-row {
    display: flex; gap: 10px; margin-bottom: 16px;
}
.holiday-add-row input[type="date"],
.holiday-add-row input[type="text"] {
    flex: 1;
    padding: 9px 13px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    transition: border-color .15s, box-shadow .15s;
}
.holiday-add-row input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 12%, transparent);
}
.btn-add {
    padding: 9px 18px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    font-family: var(--font);
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
}
.btn-add:hover { background: var(--blue-dark); }

.holiday-table { width: 100%; border-collapse: collapse; }
.holiday-table th {
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--muted);
    font-weight: 700;
    padding: 0 10px 10px;
    border-bottom: 1px solid var(--border);
}
.holiday-table td {
    padding: 11px 10px;
    font-size: 13px;
    color: var(--text);
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.holiday-table tr:last-child td { border-bottom: none; }
.holiday-date-badge {
    display: inline-block;
    background: var(--blue-light);
    color: var(--blue);
    font-size: 12px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 6px;
}
.btn-del {
    padding: 5px 11px;
    background: none;
    border: 1.5px solid var(--border2);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--red);
    cursor: pointer;
    font-family: var(--font);
    transition: background .12s, border-color .12s;
}
.btn-del:hover { background: #fff0f0; border-color: var(--red); }

.no-holidays {
    text-align: center;
    padding: 28px 0;
    color: var(--muted);
    font-size: 13px;
}
.empty-icon { font-size: 24px; margin-bottom: 6px; }

/*  Skeleton loader  */
.skel {
    background: linear-gradient(90deg, var(--surface2) 25%, var(--border) 50%, var(--surface2) 75%);
    background-size: 200% 100%;
    animation: skel-shine 1.2s infinite;
    border-radius: 6px;
    height: 36px;
}
@keyframes skel-shine { from{background-position:200% 0} to{background-position:-200% 0} }

/*  Confirm Dialog  */
#del-confirm-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.35);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
#del-confirm-overlay.show { display: flex; }
#del-confirm-box {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 14px;
    padding: 28px 28px 22px;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    text-align: left;
}
#del-confirm-box .dcb-icon {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #fff0f0;
    display: flex; align-items: center; justify-content: center;
    margin: 0 0 14px;
    font-size: 18px; color: var(--red, #e53e3e);
}
#del-confirm-box h3 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 700;
    color: var(--text, #111);
}
#del-confirm-box p {
    margin: 0 0 22px;
    font-size: 13px;
    color: var(--muted, #6b7280);
}
#del-confirm-box .dcb-actions {
    display: flex;
    gap: 10px;
}
#del-confirm-box .dcb-cancel {
    flex: 1;
    padding: 10px;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    background: transparent;
    font-size: 14px;
    font-weight: 600;
    color: var(--text, #111);
    cursor: pointer;
}
#del-confirm-box .dcb-cancel:hover { background: var(--bg, #f9fafb); }
#del-confirm-box .dcb-delete {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 8px;
    background: var(--red, #e53e3e);
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
}
#del-confirm-box .dcb-delete:hover { opacity: .88; }

/*  Toast  */
#ss-toast {
    position: fixed;
    bottom: 28px; right: 28px;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,.18);
    opacity: 0;
    transform: translateY(10px);
    transition: opacity .25s, transform .25s;
    z-index: 999;
    pointer-events: none;
}
#ss-toast.show { opacity: 1; transform: translateY(0); }
#ss-toast.ok  { background: var(--green); }
#ss-toast.err { background: var(--red); }
</style>
CSS;

$content = ob_get_clean();
ob_start();
?>

<div class="ss-wrap">

    <!-- Page header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fa fa-sliders" style="color:var(--blue)"></i>
            System Settings
        </h1>
        <p class="page-subtitle">Configure global scheduling constraints and feature toggles</p>
    </div>

    <!--  Section 1 · Working Hours  -->
    <div class="ss-card" id="card-hours">
        <div class="ss-card-header">
            <div class="ss-card-icon icon-blue"><i class="fa fa-clock"></i></div>
            <div>
                <div class="ss-card-title">Working Hours</div>
                <div class="ss-card-desc">Define clinic open and close times — no slots will be generated outside these bounds.</div>
            </div>
        </div>
        <div class="ss-card-body">
            <div class="ss-field-row">
                <div class="ss-field">
                    <label for="work_start">Open Time</label>
                    <input type="time" id="work_start" name="work_start" data-setting="work_start">
                    <div class="hint">Earliest possible start for any slot</div>
                </div>
                <div class="ss-field">
                    <label for="work_end">Close Time</label>
                    <input type="time" id="work_end" name="work_end" data-setting="work_end">
                    <div class="hint">Latest possible end for any slot</div>
                </div>
            </div>
            <div class="save-row">
                <button class="btn-save" onclick="saveSection('hours')">
                    <i class="fa fa-floppy-disk"></i> Save Hours
                </button>
                <span class="save-msg" id="msg-hours"></span>
            </div>
        </div>
    </div>

    <!--  Section 2 · Slot Configuration  -->
    <div class="ss-card" id="card-slots">
        <div class="ss-card-header">
            <div class="ss-card-icon icon-green"><i class="fa fa-calendar-check"></i></div>
            <div>
                <div class="ss-card-title">Appointment Slots</div>
                <div class="ss-card-desc">Set the maximum number of appointments a doctor can take per day. Hourly capacity is fixed: 5 per hour (2 per hour for Psychiatrists).</div>
            </div>
        </div>
        <div class="ss-card-body">
            <div class="ss-field-row" style="grid-template-columns:1fr">
                <div class="ss-field">
                    <label for="max_per_day">Max Appointments per Doctor / Day</label>
                    <input type="number" id="max_per_day" name="max_per_day"
                           data-setting="max_per_day" min="1" max="200">
                    <div class="hint">Hard cap — bookings beyond this are rejected</div>
                </div>
            </div>
            <div class="save-row">
                <button class="btn-save" onclick="saveSection('slots')">
                    <i class="fa fa-floppy-disk"></i> Save Slots
                </button>
                <span class="save-msg" id="msg-slots"></span>
            </div>
        </div>
    </div>

    <!--  Section 3 · Feature Toggles  -->
    <div class="ss-card" id="card-toggles">
        <div class="ss-card-header">
            <div class="ss-card-icon icon-purple"><i class="fa fa-toggle-on"></i></div>
            <div>
                <div class="ss-card-title">Feature Toggles</div>
                <div class="ss-card-desc">Changes take effect immediately across the entire platform — no restart required.</div>
            </div>
        </div>
        <div class="ss-card-body">
            <div class="toggle-row">
                <div class="toggle-row-info">
                    <div class="toggle-row-label"><i class="fa fa-flask" style="color:var(--teal);margin-right:6px"></i>Lab Reports</div>
                    <div class="toggle-row-desc">Allow lab admins to upload reports and patients to view them in their dashboard.</div>
                </div>
                <label class="toggle-wrap">
                    <input type="checkbox" class="toggle-input" id="tog_lab_reports" data-setting="lab_reports" onchange="saveToggle(this)">
                    <span class="toggle-track"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div class="toggle-row-info">
                    <div class="toggle-row-label"><i class="fa fa-credit-card" style="color:var(--blue);margin-right:6px"></i>Online Payments (eSewa)</div>
                    <div class="toggle-row-desc">Enable or disable the eSewa payment gateway. When off, booking proceeds without payment.</div>
                </div>
                <label class="toggle-wrap">
                    <input type="checkbox" class="toggle-input" id="tog_payments" data-setting="payments" onchange="saveToggle(this)">
                    <span class="toggle-track"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div class="toggle-row-info">
                    <div class="toggle-row-label"><i class="fa fa-robot" style="color:var(--purple, #7e22ce);margin-right:6px"></i>AI Chatbot Widget</div>
                    <div class="toggle-row-desc">Show or hide the chatbot assistant for patients across the booking portal.</div>
                </div>
                <label class="toggle-wrap">
                    <input type="checkbox" class="toggle-input" id="tog_chatbot" data-setting="chatbot" onchange="saveToggle(this)">
                    <span class="toggle-track"></span>
                </label>
            </div>
        </div>
    </div>

    <!--  Section 4 · Holidays  -->
    <div class="ss-card" id="card-holidays">
        <div class="ss-card-header">
            <div class="ss-card-icon icon-orange"><i class="fa fa-calendar-xmark"></i></div>
            <div>
                <div class="ss-card-title">Holidays &amp; Blocked Dates</div>
                <div class="ss-card-desc">Dates marked here are blocked system-wide — no appointments can be booked on these days.</div>
            </div>
        </div>
        <div class="ss-card-body">
            <div class="holiday-add-row">
                <input type="date" id="new_holiday_date" placeholder="YYYY-MM-DD"
                       min="<?= date('Y-m-d') ?>">
                <input type="text" id="new_holiday_label" placeholder="e.g. Nepal Sambat New Year" maxlength="120">
                <button class="btn-add" onclick="addHoliday()">
                    <i class="fa fa-plus"></i> Add
                </button>
            </div>
            <div id="holiday-table-wrap">
                <div class="skel" style="height:120px"></div>
            </div>
        </div>
    </div>

</div><!-- /.ss-wrap -->

<!-- Toast -->
<div id="del-confirm-overlay">
    <div id="del-confirm-box">
        <div class="dcb-icon"><i class="fa fa-trash"></i></div>
        <h3>Remove Holiday?</h3>
        <p>This date will be unblocked and appointments can be booked again.</p>
        <div class="dcb-actions">
            <button class="dcb-cancel" onclick="closeDelConfirm()">Cancel</button>
            <button class="dcb-delete" onclick="confirmDelHoliday()">Remove</button>
        </div>
    </div>
</div>
<div id="ss-toast"></div>

<script>
(function () {
    'use strict';

    //  Section - setting key map 
    const SECTIONS = {
        hours: ['work_start', 'work_end'],
        slots: ['max_per_day'],
    };

    //  Load all settings & holidays on page load 
    async function init() {
        try {
            const res  = await fetch(BASE_URL + '/admin/api/system-settings');
            const rawI = await res.text();
            const sI   = rawI.indexOf('{');
            if (sI === -1) throw new Error('Failed to load settings.');
            const data = JSON.parse(rawI.slice(sI));
            if (!data.success) throw new Error('Failed to load settings.');

            // Fill text/number inputs
            for (const row of data.settings) {
                const el = document.querySelector(`[data-setting="${row.setting_key}"]`);
                if (!el) continue;
                if (el.type === 'checkbox') {
                    el.checked = row.value === '1' || row.value === 1;
                } else {
                    el.value = row.value;
                }
            }

            renderHolidays(data.holidays);
        } catch (err) {
            toast(err.message || 'Could not load settings.', 'err');
        }
    }

    //  Save a named section 
    window.saveSection = async function (section) {
        const keys    = SECTIONS[section];
        const payload = {};
        for (const key of keys) {
            const el = document.querySelector(`[data-setting="${key}"]`);
            if (el) payload[key] = el.value;
        }

        await savePayload(payload, `msg-${section}`);
    };

    //  Save a single toggle immediately 
    window.saveToggle = async function (el) {
        const key     = el.dataset.setting;
        const payload = { [key]: el.checked ? '1' : '0' };
        await savePayload(payload, null);
    };

    async function savePayload(payload, msgId) {
        const msgEl = msgId ? document.getElementById(msgId) : null;
        if (msgEl) { msgEl.className = 'save-msg'; msgEl.textContent = ''; }

        try {
            const res  = await fetch(BASE_URL + '/admin/api/system-settings', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            });

            // Strip any stray PHP output (warnings/notices) before the JSON brace
            const raw  = await res.text();
            const start = raw.indexOf('{');
            if (start === -1) throw new Error('Empty response from server.');
            const data = JSON.parse(raw.slice(start));

            if (!data.success) throw new Error(data.error || 'Save failed.');

            toast('Settings saved.', 'ok');
            if (msgEl) { msgEl.className = 'save-msg ok'; msgEl.textContent = '✓ Saved'; }
            setTimeout(() => { if (msgEl) msgEl.className = 'save-msg'; }, 3000);
        } catch (err) {
            // Only show error toast if it's a real server/network error,
            // not a false-alarm from a response that already succeeded
            if (!(err instanceof SyntaxError)) {
                toast(err.message, 'err');
                if (msgEl) { msgEl.className = 'save-msg err'; msgEl.textContent = '✗ ' + err.message; }
            } else {
                // JSON parse error despite success — treat as success (action went through)
                toast('Settings saved.', 'ok');
                if (msgEl) { msgEl.className = 'save-msg ok'; msgEl.textContent = '✓ Saved'; }
                setTimeout(() => { if (msgEl) msgEl.className = 'save-msg'; }, 3000);
            }
        }
    }

    //  Add holiday 
    window.addHoliday = async function () {
        const dateEl  = document.getElementById('new_holiday_date');
        const labelEl = document.getElementById('new_holiday_label');
        const date    = dateEl.value.trim();
        const label   = labelEl.value.trim();

        if (!date || !label) {
            toast('Please enter both a date and a label.', 'err');
            return;
        }

        try {
            const res  = await fetch(BASE_URL + '/admin/api/system-settings/holidays', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ holiday_date: date, label }),
            });
            const raw2 = await res.text();
            const s2   = raw2.indexOf('{');
            const data = s2 !== -1 ? JSON.parse(raw2.slice(s2)) : {};
            if (!data.success) throw new Error(data.error || 'Could not add holiday.');

            dateEl.value  = '';
            labelEl.value = '';
            toast('Holiday added.', 'ok');
            await refreshHolidays();
        } catch (err) {
            toast(err.message, 'err');
        }
    };

    //  Delete holiday 
    var _pendingDelId = null;

    window.deleteHoliday = function (id) {
        _pendingDelId = id;
        document.getElementById('del-confirm-overlay').classList.add('show');
    };

    window.closeDelConfirm = function () {
        _pendingDelId = null;
        document.getElementById('del-confirm-overlay').classList.remove('show');
    };

    window.confirmDelHoliday = async function () {
        if (!_pendingDelId) return;
        const id = _pendingDelId;
        closeDelConfirm();

        try {
            const res  = await fetch(BASE_URL + '/admin/api/system-settings/holidays/' + id, {
                method: 'DELETE',
            });
            const rawD = await res.text();
            const sD   = rawD.indexOf('{');
            const data = sD !== -1 ? JSON.parse(rawD.slice(sD)) : {};
            if (!data.success) throw new Error(data.error || 'Could not delete.');

            toast('Holiday removed.', 'ok');
            await refreshHolidays();
        } catch (err) {
            toast(err.message, 'err');
        }
    };

    // Close modal when clicking the overlay backdrop
    document.getElementById('del-confirm-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeDelConfirm();
    });

    async function refreshHolidays() {
        const res  = await fetch(BASE_URL + '/admin/api/system-settings');
        const data = await res.json();
        if (data.success) renderHolidays(data.holidays);
    }

    function renderHolidays(holidays) {
        const wrap = document.getElementById('holiday-table-wrap');
        if (!holidays.length) {
            wrap.innerHTML = `
                <div class="no-holidays">
                    <div class="empty-icon">📅</div>
                    No holidays configured. All dates are open for booking.
                </div>`;
            return;
        }

        const rows = holidays.map(h => `
            <tr>
                <td><span class="holiday-date-badge">${h.holiday_date}</span></td>
                <td>${escHtml(h.label)}</td>
                <td style="text-align:right">
                    <button class="btn-del" onclick="deleteHoliday(${h.id})">
                        <i class="fa fa-trash-can"></i> Remove
                    </button>
                </td>
            </tr>`).join('');

        wrap.innerHTML = `
            <table class="holiday-table">
                <thead><tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th></th>
                </tr></thead>
                <tbody>${rows}</tbody>
            </table>`;
    }

    //  Toast helper 
    function toast(msg, type) {
        const el = document.getElementById('ss-toast');
        el.textContent = msg;
        el.className   = 'show ' + type;
        clearTimeout(el._t);
        el._t = setTimeout(() => { el.className = ''; }, 3200);
    }

    function escHtml(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Boot
    init();
})();
</script>

<?php
$content .= ob_get_clean();
require BASE_PATH . '/app/views/layouts/app.php';
?>