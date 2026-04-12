<?php
$title = 'Reschedule Appointment';

$doc        = $doctor ?? [];
$a          = $appt   ?? [];
$doctorName = htmlspecialchars($doc['name']      ?? 'Doctor');
$specialty  = htmlspecialchars($doc['specialty'] ?? '');
$slotMins   = (int) ($doc['avg_slot_minutes']    ?? 30);
$apptId     = (int)   ($a['id']               ?? 0);
$apptRef    = htmlspecialchars($a['reference_number'] ?? '—');
$apptDate   = isset($a['date']) ? date('M j, Y', strtotime($a['date'])) : '—';
$apptTime   = isset($a['time']) ? date('g:i A',  strtotime($a['time'])) : '—';

$nameParts = explode(' ', preg_replace('/^Dr\.\s*/i', '', $doc['name'] ?? 'Doctor'));
$initials  = strtoupper(substr($nameParts[0] ?? 'D', 0, 1) . substr($nameParts[1] ?? '', 0, 1));

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

ob_start();
?>

<div class="reschedule-wrap">

    <!-- LEFT: date + slot picker -->
    <div class="left-panel">

        <a href="/dashboard" class="back-link">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="doctor-header">
            <div class="doctor-avatar-lg"><?= $initials ?></div>
            <div class="doctor-info">
                <h1><?= $doctorName ?></h1>
                <div class="doctor-meta"><?= $specialty ?></div>
            </div>
        </div>

        <div class="current-appt-box">
            <i class="fa fa-calendar-xmark"></i>
            <div>
                <div class="label">Current appointment</div>
                <div class="val"><?= $apptDate ?> &middot; <?= $apptTime ?></div>
                <div class="sub">Ref: <?= $apptRef ?></div>
            </div>
        </div>

        <div class="section-title">Select new date</div>
        <div class="date-grid" id="dateGrid"></div>

        <div class="section-title">Select new time</div>
        <div class="slots-wrap" id="slotsWrap">
            <div class="slots-placeholder">
                <i class="fa fa-clock" style="opacity:.4;display:block;font-size:24px;margin-bottom:8px;"></i>
                Pick a date above to see available slots
            </div>
        </div>

    </div>

    <!-- RIGHT: summary -->
    <div class="summary-panel">
        <h2>Reschedule summary</h2>
        <div class="summary-row"><span class="s-label">Doctor</span><span class="s-val"><?= $doctorName ?></span></div>
        <div class="summary-row"><span class="s-label">New date</span><span class="s-val" id="sumDate"><span class="summary-placeholder">—</span></span></div>
        <div class="summary-row"><span class="s-label">New time</span><span class="s-val highlight" id="sumTime"><span class="summary-placeholder">—</span></span></div>
        <button id="confirmBtn" disabled>Confirm reschedule</button>
    </div>

</div>

<div class="toast" id="toast"></div>

<script>
(function () {
    var AVAIL   = <?= $availJson ?>;
    var DOCTOR  = <?= $doctorJson ?>;
    var APPT_ID = <?= $apptId ?>;
    var DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var selectedDate = null, selectedStart = null, selectedEnd = null, bookedOnDate = [];

    function toISO(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }
    function timeToMin(t) { var p = t.split(':'); return parseInt(p[0])*60 + parseInt(p[1]); }
    function minToTime(m) { return String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0') + ':00'; }
    function fmt12(t) {
        var h = parseInt(t.split(':')[0]), m = t.split(':')[1];
        return (h%12||12) + ':' + m + ' ' + (h>=12?'PM':'AM');
    }

    function buildDateGrid() {
        var grid = document.getElementById('dateGrid');
        grid.innerHTML = '';
        var today = new Date(); today.setHours(0,0,0,0);
        for (var i = 0; i < 7; i++) {
            var d = new Date(today); d.setDate(today.getDate() + i);
            var dayName = DAY_NAMES[d.getDay()];
            var btn = document.createElement('button');
            btn.className = 'day-btn';
            btn.disabled  = !AVAIL[dayName];
            btn.dataset.date = toISO(d); btn.dataset.day = dayName;
            btn.innerHTML = '<span class="day-name">' + dayName.slice(0,3) + '</span><span class="day-num">' + d.getDate() + '</span>';
            if (AVAIL[dayName]) btn.addEventListener('click', onDayClick);
            grid.appendChild(btn);
        }
    }

    function onDayClick() {
        document.querySelectorAll('.day-btn').forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        selectDate(this.dataset.date, this.dataset.day);
    }

    function selectDate(date, dayName) {
        selectedDate = date; selectedStart = null; selectedEnd = null;
        updateSummary(); updateConfirmBtn();
        document.getElementById('slotsWrap').innerHTML = '<div class="slots-placeholder"><i class="fa fa-spinner fa-spin" style="font-size:20px;opacity:.5;"></i></div>';
        fetch('/api/slots?doctor_id=' + DOCTOR.id + '&date=' + date)
            .then(function(r){ return r.json(); })
            .then(function(res){ bookedOnDate = res.booked || []; renderSlots(dayName); })
            .catch(function(){ bookedOnDate = []; renderSlots(dayName); });
    }

    function renderSlots(dayName) {
        var avail = AVAIL[dayName], wrap = document.getElementById('slotsWrap');
        if (!avail) { wrap.innerHTML = '<div class="slots-placeholder">No availability on this day.</div>'; return; }
        var grid = document.createElement('div');
        grid.className = 'slots-grid';
        var cur = timeToMin(avail.start), last = timeToMin(avail.end), breakMins = avail.break_minutes || 0;
        while (cur + DOCTOR.slot_minutes <= last) {
            var s = minToTime(cur), e = minToTime(cur + DOCTOR.slot_minutes);
            var isBooked = bookedOnDate.some(function(b){ return b.slice(0,5) === s.slice(0,5); });
            var btn = document.createElement('button');
            btn.className = 'slot-btn'; btn.textContent = fmt12(s);
            btn.dataset.start = s; btn.dataset.end = e;
            btn.disabled = isBooked;
            if (!isBooked) btn.addEventListener('click', onSlotClick);
            grid.appendChild(btn);
            cur += DOCTOR.slot_minutes + breakMins;
        }
        wrap.innerHTML = '';
        wrap.appendChild(grid);
    }

    function onSlotClick() {
        document.querySelectorAll('.slot-btn').forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        selectedStart = this.dataset.start; selectedEnd = this.dataset.end;
        updateSummary(); updateConfirmBtn();
    }

    function updateSummary() {
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var sumDate = document.getElementById('sumDate'), sumTime = document.getElementById('sumTime');
        if (selectedDate) {
            var d = new Date(selectedDate + 'T00:00:00');
            sumDate.textContent = months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
        } else { sumDate.innerHTML = '<span class="summary-placeholder">—</span>'; }
        if (selectedStart) { sumTime.textContent = fmt12(selectedStart); }
        else { sumTime.innerHTML = '<span class="summary-placeholder">—</span>'; }
    }

    function updateConfirmBtn() {
        document.getElementById('confirmBtn').disabled = !(selectedDate && selectedStart);
    }

    function showToast(msg, type) {
        var t = document.getElementById('toast');
        t.textContent = msg; t.className = 'toast ' + (type || 'success');
        t.classList.add('show');
        setTimeout(function(){ t.classList.remove('show'); }, 3500);
    }

    document.getElementById('confirmBtn').addEventListener('click', function() {
        if (!selectedDate || !selectedStart) return;
        var btn = this; btn.disabled = true; btn.textContent = 'Rescheduling\u2026';
        fetch('/api/appointments/' + APPT_ID + '/reschedule', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ date: selectedDate, start_time: selectedStart, end_time: selectedEnd })
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) { showToast('Appointment rescheduled successfully.', 'success'); setTimeout(function(){ window.location.href = '/dashboard'; }, 1400); }
            else { showToast(res.message || 'Could not reschedule. Please try again.', 'error'); btn.disabled = false; btn.textContent = 'Confirm reschedule'; }
        })
        .catch(function(){ showToast('Network error. Please try again.', 'error'); btn.disabled = false; btn.textContent = 'Confirm reschedule'; });
    });

    buildDateGrid();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';