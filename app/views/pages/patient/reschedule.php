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

        <a href="<?= BASE_URL ?>/dashboard" class="back-link">
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
        <div class="two-week-grid">
            <div class="week-block">
                <div class="week-block-label" id="week1Label"></div>
                <div class="rsch-week-hdr" id="rschHdr1"></div>
                <div class="rsch-week-dates" id="rschDates1"></div>
            </div>
            <div class="week-block">
                <div class="week-block-label" id="week2Label"></div>
                <div class="rsch-week-hdr" id="rschHdr2"></div>
                <div class="rsch-week-dates" id="rschDates2"></div>
            </div>
        </div>

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
    var DAY_NAMES  = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var DAY_SHORT  = ['Su','Mo','Tu','We','Th','Fr','Sa'];
    var selectedDate = null, selectedStart = null, selectedEnd = null, bookedOnDate = [];

    function getNPTNow() {
        var now = new Date();
        var utcMs = now.getTime() + now.getTimezoneOffset() * 60000;
        return new Date(utcMs + (5 * 60 + 45) * 60000);
    }

    function toISO(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }
    function timeToMin(t) { var p = t.split(':'); return parseInt(p[0])*60 + parseInt(p[1]); }
    function minToTime(m) { return String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0') + ':00'; }
    function fmt12(t) {
        var h = parseInt(t.split(':')[0]), m = t.split(':')[1];
        return (h%12||12) + ':' + m + ' ' + (h>=12?'PM':'AM');
    }

    var nptNow     = getNPTNow();
    var todayISO   = toISO(nptNow);
    var nptMinutes = nptNow.getHours() * 60 + nptNow.getMinutes();

    var MONTH_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    function buildWeek(weekIndex) {
        var npt = getNPTNow(); npt.setHours(0,0,0,0);
        var weekStart = new Date(npt);
        weekStart.setDate(npt.getDate() - npt.getDay() + weekIndex * 7);

        var weekEnd = new Date(weekStart); weekEnd.setDate(weekStart.getDate() + 6);
        document.getElementById('week' + (weekIndex+1) + 'Label').textContent =
            MONTH_SHORT[weekStart.getMonth()] + ' ' + weekStart.getDate() +
            ' – ' + MONTH_SHORT[weekEnd.getMonth()] + ' ' + weekEnd.getDate();

        var headerRow = document.getElementById('rschHdr'   + (weekIndex+1));
        var dateRow   = document.getElementById('rschDates' + (weekIndex+1));
        headerRow.innerHTML = '';
        dateRow.innerHTML   = '';

        for (var col = 0; col < 7; col++) {
            var cellDate = new Date(weekStart);
            cellDate.setDate(weekStart.getDate() + col);
            var dayISO   = toISO(cellDate);
            var dayName  = DAY_NAMES[cellDate.getDay()];
            var isPast   = dayISO < todayISO;
            var hasAvail = !!AVAIL[dayName];

            var hdr = document.createElement('div');
            hdr.className   = 'rsch-day-hdr';
            hdr.textContent = DAY_SHORT[col];
            headerRow.appendChild(hdr);

            if (isPast) {
                var empty = document.createElement('div');
                empty.className = 'day-btn filler';
                dateRow.appendChild(empty);
            } else {
                var btn = document.createElement('button');
                btn.className    = 'day-btn' + (!hasAvail ? ' unavailable' : '');
                btn.disabled     = !hasAvail;
                btn.dataset.date = dayISO;
                btn.dataset.day  = dayName;
                btn.innerHTML    = '<span class="day-name">' + DAY_SHORT[col] + '</span><span class="day-num">' + cellDate.getDate() + '</span>';
                if (hasAvail) btn.addEventListener('click', onDayClick);
                dateRow.appendChild(btn);
            }
        }
    }

    function buildDateGrid() {
        buildWeek(0);
        buildWeek(1);
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
        fetch(BASE_URL + '/api/slots?doctor_id=' + DOCTOR.id + '&date=' + date)
            .then(function(r){ return r.json(); })
            .then(function(res){ bookedOnDate = res.booked || []; renderSlots(dayName, date); })
            .catch(function(){ bookedOnDate = []; renderSlots(dayName, date); });
    }

    function renderSlots(dayName, dateStr) {
        var avail = AVAIL[dayName], wrap = document.getElementById('slotsWrap');
        if (!avail) { wrap.innerHTML = '<div class="slots-placeholder">No availability on this day.</div>'; return; }

        var grid = document.createElement('div');
        grid.className = 'slots-grid';

        var isToday = (dateStr === todayISO);
        // Always use 1-hour slots (60 min), ignore DOCTOR.slot_minutes for consistency
        var cur = timeToMin(avail.start), last = timeToMin(avail.end);

        while (cur + 60 <= last) {
            var s = minToTime(cur), e = minToTime(cur + 60);
            var hourKey = String(Math.floor(cur/60)).padStart(2,'0') + ':00:00';

            // A slot is fully booked only when it appears 5+ times in bookedOnDate
            var bookingCount = bookedOnDate.filter(function(b){ return b === hourKey; }).length;
            var isBooked = bookingCount >= 5;

            // Block slots in the past for today (NPT)
            var isPast = isToday && (cur <= nptMinutes);

            var btn = document.createElement('button');
            btn.className = 'slot-btn' + (isBooked || isPast ? ' booked' : '');
            btn.textContent = fmt12(s) + ' – ' + fmt12(e);
            btn.dataset.start = s; btn.dataset.end = e;
            btn.disabled = isBooked || isPast;
            if (isPast) btn.title = 'This time has already passed';
            if (!isBooked && !isPast) btn.addEventListener('click', onSlotClick);
            grid.appendChild(btn);
            cur += 60;
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
        fetch(BASE_URL + '/api/appointments/' + APPT_ID + '/reschedule', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ date: selectedDate, start_time: selectedStart, end_time: selectedEnd })
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) { showToast('Appointment rescheduled successfully.', 'success'); setTimeout(function(){ window.location.href = BASE_URL + '/dashboard'; }, 1400); }
            else { showToast(res.message || 'Could not reschedule. Please try again.', 'error'); btn.disabled = false; btn.textContent = 'Confirm reschedule'; }
        })
        .catch(function(){ showToast('Network error. Please try again.', 'error'); btn.disabled = false; btn.textContent = 'Confirm reschedule'; });
    });

    buildDateGrid();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';