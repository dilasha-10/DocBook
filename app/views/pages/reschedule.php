<?php
$title = 'Reschedule Appointment';

// ── PHP-side data ─────────────────────────────────────────────────────────────
$doc        = $doctor ?? [];
$a          = $appt   ?? [];

$doctorName = htmlspecialchars($doc['name']      ?? 'Doctor');
$specialty  = htmlspecialchars($doc['specialty'] ?? '');
$slotMins   = (int) ($doc['avg_slot_minutes']    ?? 30);

$apptId     = (int)   ($a['id']               ?? 0);
$apptRef    = htmlspecialchars($a['reference_number'] ?? '—');
$apptDate   = isset($a['date']) ? date('M j, Y', strtotime($a['date'])) : '—';
$apptTime   = isset($a['time']) ? date('g:i A', strtotime($a['time']))  : '—';

$nameParts  = explode(' ', preg_replace('/^Dr\.\s*/i', '', $doc['name'] ?? 'Doctor'));
$initials   = strtoupper(substr($nameParts[0] ?? 'D', 0, 1) . substr($nameParts[1] ?? '', 0, 1));

// Availability map for JS
$availMap = [];
foreach (($availability ?? []) as $row) {
    $availMap[$row['day_of_week']] = [
        'start'         => $row['start_time'],
        'end'           => $row['end_time'],
        'break_minutes' => (int)$row['break_minutes'],
    ];
}
$availJson  = json_encode($availMap);
$doctorJson = json_encode(['id' => $doc['id'] ?? 0, 'slot_minutes' => $slotMins]);

$extra_styles = <<<CSS
<style>
/* ── Layout ──────────────────────────────────────────────────────────────── */
.reschedule-wrap {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 0;
    min-height: calc(100vh - 60px);
    align-items: start;
}

/* ── Left panel ──────────────────────────────────────────────────────────── */
.left-panel {
    padding: 36px 40px;
    border-right: 1px solid var(--border);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--muted);
    text-decoration: none;
    margin-bottom: 28px;
    transition: color .15s;
}
.back-link:hover { color: var(--text); }

.doctor-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 28px;
}

.doctor-avatar-lg {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4a9eff 0%, #6366f1 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    letter-spacing: .03em;
}

.doctor-info h1 {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 4px;
}

.doctor-meta {
    font-size: 13px;
    color: #4a9eff;
    font-weight: 500;
}

/* ── Current appointment box ─────────────────────────────────────────────── */
.current-appt-box {
    background: rgba(245, 158, 11, .08);
    border: 1px solid rgba(245, 158, 11, .25);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 28px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.current-appt-box i {
    color: #fbbf24;
    font-size: 16px;
    margin-top: 2px;
    flex-shrink: 0;
}
.current-appt-box .label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #fbbf24;
    margin-bottom: 4px;
}
.current-appt-box .val {
    font-size: 14px;
    color: var(--text);
    font-weight: 500;
}
.current-appt-box .sub {
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
}

/* ── Date grid ───────────────────────────────────────────────────────────── */
.section-title {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--hint);
    margin-bottom: 12px;
}

.date-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 28px;
}

.day-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 10px 4px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--surface);
    cursor: pointer;
    color: var(--text);
    font-size: 12px;
    transition: all .15s;
    min-height: 62px;
}
.day-btn .day-name  { font-size: 10px; font-weight: 600; text-transform: uppercase; color: var(--hint); letter-spacing: .05em; }
.day-btn .day-num   { font-size: 18px; font-weight: 700; line-height: 1; }
.day-btn:disabled   { opacity: .3; cursor: not-allowed; background: transparent; }
.day-btn:not(:disabled):hover { border-color: var(--accent); background: rgba(99,102,241,.08); }
.day-btn.active     { border-color: var(--accent); background: rgba(99,102,241,.15); color: var(--accent); }
.day-btn.active .day-name { color: var(--accent); }

/* ── Slots ───────────────────────────────────────────────────────────────── */
.slots-wrap {
    min-height: 80px;
}

.slots-placeholder {
    text-align: center;
    padding: 24px;
    color: var(--muted);
    font-size: 14px;
    border: 1px dashed var(--border);
    border-radius: 10px;
}

.slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
    gap: 8px;
}

.slot-btn {
    padding: 9px 6px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s;
    text-align: center;
}
.slot-btn:disabled {
    opacity: .3;
    cursor: not-allowed;
    text-decoration: line-through;
    background: transparent;
}
.slot-btn:not(:disabled):hover { border-color: var(--accent); background: rgba(99,102,241,.08); }
.slot-btn.active  { border-color: var(--accent); background: rgba(99,102,241,.15); color: var(--accent); font-weight: 600; }

/* ── Right: summary panel ────────────────────────────────────────────────── */
.summary-panel {
    padding: 36px 28px;
    position: sticky;
    top: 60px;
}

.summary-panel h2 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
    font-size: 14px;
}
.summary-row:last-of-type { border-bottom: none; }
.summary-row .s-label { color: var(--muted); flex-shrink: 0; }
.summary-row .s-val   { font-weight: 500; text-align: right; }
.summary-row .s-val.highlight { color: var(--accent); }

.summary-placeholder {
    color: var(--hint);
    font-style: italic;
    font-size: 13px;
    font-weight: 400;
}

#confirmBtn {
    width: 100%;
    margin-top: 24px;
    padding: 13px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 10px;
    border: none;
    background: #4a9eff;
    color: #ffffff;
    cursor: pointer;
    transition: background .15s;
}
#confirmBtn:disabled { background: #1a3a5c; color: #4a7aaa; cursor: not-allowed; }
#confirmBtn:not(:disabled):hover { background: #3a8eef; }

.fee-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 18px;
    padding: 14px 16px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
}
.fee-row span:last-child { font-size: 17px; font-weight: 700; }

/* ── Toast ───────────────────────────────────────────────────────────────── */
.toast {
    position: fixed;
    bottom: 28px;
    right: 28px;
    padding: 14px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    z-index: 9999;
    opacity: 0;
    transform: translateY(10px);
    transition: opacity .25s, transform .25s;
    pointer-events: none;
    max-width: 340px;
}
.toast.show { opacity: 1; transform: translateY(0); }
.toast.success { background: #059669; }
.toast.error   { background: #dc2626; }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .reschedule-wrap { grid-template-columns: 1fr; }
    .left-panel      { border-right: none; border-bottom: 1px solid var(--border); padding: 24px 20px; }
    .summary-panel   { padding: 24px 20px; position: static; }
    .date-grid       { grid-template-columns: repeat(7, 1fr); gap: 5px; }
    .day-btn .day-num { font-size: 15px; }
}
@media (max-width: 480px) {
    .date-grid { gap: 4px; }
    .day-btn   { padding: 8px 2px; min-height: 52px; }
    .day-btn .day-num { font-size: 13px; }
}
</style>
CSS;

ob_start();
?>
<div class="reschedule-wrap">

    <!-- ── Left: date + slot picker ─────────────────────────────────────── -->
    <div class="left-panel">

        <a href="/dashboard" class="back-link">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Doctor header -->
        <div class="doctor-header">
            <div class="doctor-avatar-lg"><?php echo $initials; ?></div>
            <div class="doctor-info">
                <h1><?php echo $doctorName; ?></h1>
                <div class="doctor-meta"><?php echo $specialty; ?></div>
            </div>
        </div>

        <!-- Current appointment info -->
        <div class="current-appt-box">
            <i class="fa fa-calendar-xmark"></i>
            <div>
                <div class="label">Current appointment</div>
                <div class="val"><?php echo $apptDate; ?> &middot; <?php echo $apptTime; ?></div>
                <div class="sub">Ref: <?php echo $apptRef; ?></div>
            </div>
        </div>

        <!-- Date grid -->
        <div class="section-title">Select new date</div>
        <div class="date-grid" id="dateGrid"></div>

        <!-- Slots -->
        <div class="section-title">Select new time</div>
        <div class="slots-wrap" id="slotsWrap">
            <div class="slots-placeholder">
                <i class="fa fa-clock" style="opacity:.4;display:block;font-size:24px;margin-bottom:8px;"></i>
                Pick a date above to see available slots
            </div>
        </div>

    </div>

    <!-- ── Right: booking summary ────────────────────────────────────────── -->
    <div class="summary-panel">
        <h2>Reschedule summary</h2>

        <div class="summary-row">
            <span class="s-label">Doctor</span>
            <span class="s-val"><?php echo $doctorName; ?></span>
        </div>
        <div class="summary-row">
            <span class="s-label">New date</span>
            <span class="s-val" id="sumDate"><span class="summary-placeholder">—</span></span>
        </div>
        <div class="summary-row">
            <span class="s-label">New time</span>
            <span class="s-val highlight" id="sumTime"><span class="summary-placeholder">—</span></span>
        </div>

        <button id="confirmBtn" disabled>Confirm reschedule</button>
    </div>

</div>

<div class="toast" id="toast"></div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
(function () {
    // ── Data from PHP ──────────────────────────────────────────────────────
    var AVAIL      = {$availJson};
    var DOCTOR     = {$doctorJson};
    var APPT_ID    = {$apptId};

    // ── State ──────────────────────────────────────────────────────────────
    var selectedDate  = null;
    var selectedStart = null;
    var selectedEnd   = null;
    var bookedOnDate  = [];

    var DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    // ── Date grid ──────────────────────────────────────────────────────────
    function buildDateGrid() {
        var grid  = document.getElementById('dateGrid');
        grid.innerHTML = '';
        var today = new Date();
        today.setHours(0, 0, 0, 0);

        for (var i = 0; i < 7; i++) {
            var d = new Date(today);
            d.setDate(today.getDate() + i);
            var dayName  = DAY_NAMES[d.getDay()];
            var isAvail  = !!AVAIL[dayName];

            var btn = document.createElement('button');
            btn.className        = 'day-btn';
            btn.disabled         = !isAvail;
            btn.dataset.date     = toISO(d);
            btn.dataset.day      = dayName;
            btn.innerHTML        = '<span class="day-name">' + dayName.slice(0,3) + '</span>'
                                 + '<span class="day-num">'  + d.getDate()        + '</span>';

            if (isAvail) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.day-btn').forEach(function (b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    selectDate(this.dataset.date, this.dataset.day);
                });
            }
            grid.appendChild(btn);
        }
    }

    function toISO(d) {
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

    // ── Date selection: fetch booked slots then render ─────────────────────
    function selectDate(date, dayName) {
        selectedDate  = date;
        selectedStart = null;
        selectedEnd   = null;
        updateSummary();
        updateConfirmBtn();

        var wrap = document.getElementById('slotsWrap');
        wrap.innerHTML = '<div class="slots-placeholder"><i class="fa fa-spinner fa-spin" style="font-size:20px;opacity:.5;"></i></div>';

        fetch('/api/slots?doctor_id=' + DOCTOR.id + '&date=' + date)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                bookedOnDate = res.booked || [];
                renderSlots(dayName);
            })
            .catch(function () {
                bookedOnDate = [];
                renderSlots(dayName);
            });
    }

    // ── Slot rendering ─────────────────────────────────────────────────────
    function renderSlots(dayName) {
        var avail = AVAIL[dayName];
        var wrap  = document.getElementById('slotsWrap');

        if (!avail) {
            wrap.innerHTML = '<div class="slots-placeholder">No availability on this day.</div>';
            return;
        }

        var slots   = generateSlots(avail.start, avail.end, DOCTOR.slot_minutes, avail.break_minutes);
        var grid    = document.createElement('div');
        grid.className = 'slots-grid';

        slots.forEach(function (s) {
            var btn     = document.createElement('button');
            btn.className  = 'slot-btn';
            btn.textContent = fmt12(s.start);
            btn.dataset.start = s.start;
            btn.dataset.end   = s.end;

            // Disable if already booked (server-side check)
            var isBooked = bookedOnDate.some(function (b) { return b.slice(0,5) === s.start.slice(0,5); });
            if (isBooked) {
                btn.disabled = true;
            } else {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.slot-btn').forEach(function (b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    selectedStart = this.dataset.start;
                    selectedEnd   = this.dataset.end;
                    updateSummary();
                    updateConfirmBtn();
                });
            }
            grid.appendChild(btn);
        });

        wrap.innerHTML = '';
        wrap.appendChild(grid);
    }

    function generateSlots(start, end, slotMins, breakMins) {
        var slots = [];
        var cur   = timeToMin(start);
        var last  = timeToMin(end);
        breakMins = breakMins || 0;

        while (cur + slotMins <= last) {
            var s = minToTime(cur);
            var e = minToTime(cur + slotMins);
            slots.push({ start: s, end: e });
            cur += slotMins + breakMins;
        }
        return slots;
    }

    function timeToMin(t) {
        var p = t.split(':');
        return parseInt(p[0]) * 60 + parseInt(p[1]);
    }

    function minToTime(m) {
        var h  = Math.floor(m / 60);
        var mn = m % 60;
        return String(h).padStart(2, '0') + ':' + String(mn).padStart(2, '0') + ':00';
    }

    function fmt12(t) {
        var p    = t.split(':');
        var h    = parseInt(p[0]);
        var m    = p[1];
        var ampm = h >= 12 ? 'PM' : 'AM';
        var h12  = h % 12 || 12;
        return h12 + ':' + m + ' ' + ampm;
    }

    // ── Summary panel ──────────────────────────────────────────────────────
    function updateSummary() {
        var sumDate = document.getElementById('sumDate');
        var sumTime = document.getElementById('sumTime');

        if (selectedDate) {
            var d = new Date(selectedDate + 'T00:00:00');
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            sumDate.textContent = months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
        } else {
            sumDate.innerHTML = '<span class="summary-placeholder">—</span>';
        }

        if (selectedStart) {
            sumTime.textContent = fmt12(selectedStart);
        } else {
            sumTime.innerHTML = '<span class="summary-placeholder">—</span>';
        }
    }

    function updateConfirmBtn() {
        document.getElementById('confirmBtn').disabled = !(selectedDate && selectedStart);
    }

    // ── Confirm button ─────────────────────────────────────────────────────
    document.getElementById('confirmBtn').addEventListener('click', function () {
        if (!selectedDate || !selectedStart) return;

        this.disabled    = true;
        this.textContent = 'Rescheduling\u2026';

        fetch('/api/appointments/' + APPT_ID + '/reschedule', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                date:       selectedDate,
                start_time: selectedStart,
                end_time:   selectedEnd
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showToast('Appointment rescheduled successfully.', 'success');
                setTimeout(function () { window.location.href = '/dashboard'; }, 1400);
            } else {
                showToast(res.message || 'Could not reschedule. Please try again.', 'error');
                document.getElementById('confirmBtn').disabled    = false;
                document.getElementById('confirmBtn').textContent = 'Confirm reschedule';
            }
        })
        .catch(function () {
            showToast('Network error. Please try again.', 'error');
            document.getElementById('confirmBtn').disabled    = false;
            document.getElementById('confirmBtn').textContent = 'Confirm reschedule';
        });
    });

    // ── Toast ──────────────────────────────────────────────────────────────
    function showToast(msg, type) {
        var t = document.getElementById('toast');
        t.textContent = msg;
        t.className   = 'toast ' + (type || 'success');
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3500);
    }

    // ── Init ───────────────────────────────────────────────────────────────
    buildDateGrid();
})();
</script>
JS;

include __DIR__ . '/../layouts/app.php';