<?php
$title = 'Book Appointment';

$doc        = $doctor ?? [];
$doctorId   = (int) ($doc['id']               ?? 0);
$doctorName = htmlspecialchars($doc['name']    ?? 'Doctor');
$category   = htmlspecialchars($doc['category_name'] ?? '');
$experience = (int) ($doc['experience_years']  ?? 0);
$bio        = htmlspecialchars($doc['bio']      ?? '');
$slotMins   = (int) ($doc['avg_slot_minutes']   ?? 30);

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

$availJson  = json_encode($availMap,  JSON_HEX_TAG | JSON_HEX_APOS);
$doctorJson = json_encode([
    'id'        => $doctorId,
    'name'      => $doc['name']      ?? '',
    'slot_mins' => $slotMins,
], JSON_HEX_TAG | JSON_HEX_APOS);

ob_start();
?>

<div class="booking-wrap">

    <!-- LEFT: Doctor profile -->
    <div class="doctor-panel">
        <div class="doctor-header">
            <div class="doctor-avatar-lg"><?= $initials ?></div>
            <div class="doctor-info">
                <h1><?= $doctorName ?></h1>
                <div class="doctor-meta"><?= $category ?> &middot; <?= $experience ?> years experience</div>
            </div>
        </div>

        <?php if ($bio): ?>
        <div class="about-box">
            <div class="about-label">About</div>
            <p><?= $bio ?></p>
        </div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-num"><?= $experience ?> yrs</div>
                <div class="stat-lbl">Experience</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">500+</div>
                <div class="stat-lbl">Patients</div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Booking panel -->
    <div class="booking-panel">
        <h2>Book appointment</h2>

        <div class="date-label">Select date</div>
        <div class="two-week-grid">
            <div class="week-block">
                <div class="week-block-label" id="week1Label"></div>
                <div class="week-grid" id="dayHeaders1"></div>
                <div class="week-grid" id="dateGrid1"></div>
            </div>
            <div class="week-block">
                <div class="week-block-label" id="week2Label"></div>
                <div class="week-grid" id="dayHeaders2"></div>
                <div class="week-grid" id="dateGrid2"></div>
            </div>
        </div>

        <div class="slots-heading" id="slotsHeading">Available slots</div>
        <div class="slots-grid" id="slotsGrid">
            <div class="no-slots">Select a date to see available slots.</div>
        </div>

        <div class="selected-summary" id="selectedSummary">No time selected</div>
        <button class="btn-confirm-booking" id="confirmBtn" disabled>Confirm booking</button>
    </div>

</div>

<script>
(function () {
    var AVAIL  = <?= $availJson ?>;
    var DOCTOR = <?= $doctorJson ?>;
    var DAY_SHORT   = ['Su','Mo','Tu','We','Th','Fr','Sa'];
    var DAY_NAMES   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var MONTH_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var selectedDate = null, selectedStart = null, selectedEnd = null, bookedOnDate = [];

    function getNPTNow() {
        var now = new Date();
        var utcMs = now.getTime() + now.getTimezoneOffset() * 60000;
        return new Date(utcMs + (5 * 60 + 45) * 60000);
    }
    function toISO(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }
    function timeToMins(t) { var p = t.split(':'); return parseInt(p[0])*60 + parseInt(p[1]); }
    function minsToTime(m) { return String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0') + ':00'; }
    function formatTime(t) {
        var h = parseInt(t.split(':')[0]), m = t.split(':')[1];
        return (h%12||12) + ':' + m + ' ' + (h>=12?'PM':'AM');
    }

    var nptNow     = getNPTNow();
    var todayISO   = toISO(nptNow);
    var nptMinutes = nptNow.getHours() * 60 + nptNow.getMinutes();

    function buildWeek(weekIndex) {
        // weekIndex 0 = this week, 1 = next week
        var npt = getNPTNow(); npt.setHours(0,0,0,0);
        var weekStart = new Date(npt);
        weekStart.setDate(npt.getDate() - npt.getDay() + weekIndex * 7);

        var weekEnd = new Date(weekStart); weekEnd.setDate(weekStart.getDate() + 6);
        var label = MONTH_SHORT[weekStart.getMonth()] + ' ' + weekStart.getDate()
                  + ' – ' + MONTH_SHORT[weekEnd.getMonth()] + ' ' + weekEnd.getDate();
        document.getElementById('week' + (weekIndex+1) + 'Label').textContent = label;

        var headers = document.getElementById('dayHeaders' + (weekIndex+1));
        var grid    = document.getElementById('dateGrid'   + (weekIndex+1));
        headers.innerHTML = '';
        grid.innerHTML    = '';

        for (var col = 0; col < 7; col++) {
            var cellDate = new Date(weekStart);
            cellDate.setDate(weekStart.getDate() + col);
            var dayISO   = toISO(cellDate);
            var dayName  = DAY_NAMES[cellDate.getDay()];
            var isPast   = dayISO < todayISO;
            var hasAvail = !!AVAIL[dayName];

            var hdr = document.createElement('div');
            hdr.className   = 'day-hdr';
            hdr.textContent = DAY_SHORT[col];
            headers.appendChild(hdr);

            if (isPast) {
                var filler = document.createElement('div');
                filler.className = 'day-btn filler';
                grid.appendChild(filler);
            } else {
                var btn = document.createElement('button');
                btn.className    = 'day-btn' + (!hasAvail ? ' unavailable' : '');
                btn.disabled     = !hasAvail;
                btn.dataset.date = dayISO;
                btn.dataset.day  = dayName;
                btn.innerHTML    = '<span>' + cellDate.getDate() + '</span>';
                if (hasAvail) btn.addEventListener('click', onDayClick);
                grid.appendChild(btn);
            }
        }
    }

    function buildDateGrid() {
        buildWeek(0); // this week
        buildWeek(1); // next week
    }

    function onDayClick() {
        document.querySelectorAll('.day-btn').forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        selectDate(this.dataset.date, this.dataset.day);
    }

    function selectDate(dateStr, dayName) {
        selectedDate = dateStr; selectedStart = null; selectedEnd = null;
        updateSummary();
        document.getElementById('confirmBtn').disabled = true;
        var d = new Date(dateStr + 'T00:00:00');
        document.getElementById('slotsHeading').textContent = 'Available slots \u2014 ' + d.toLocaleDateString('en-US',{month:'short',day:'numeric'});
        document.getElementById('slotsGrid').innerHTML = '<div class="slots-loading"><i class="fa fa-spinner fa-spin"></i> Loading\u2026</div>';
        fetch(BASE_URL + '/api/slots?doctor_id=' + DOCTOR.id + '&date=' + dateStr)
            .then(function(r){ return r.json(); })
            .then(function(res){ bookedOnDate = res.booked || []; buildSlotsGrid(dayName, dateStr); })
            .catch(function(){ bookedOnDate = []; buildSlotsGrid(dayName, dateStr); });
    }

    function buildSlotsGrid(dayName, dateStr) {
        var info = AVAIL[dayName], grid = document.getElementById('slotsGrid');
        if (!info) return;
        grid.innerHTML = '';
        var cur = timeToMins(info.start), end = timeToMins(info.end), hasSlots = false;
        var isToday = (dateStr === todayISO);
        while (cur + 60 <= end) {
            var start = minsToTime(cur), finish = minsToTime(cur + 60);
            var hourKey = String(Math.floor(cur/60)).padStart(2,'0') + ':00:00';
            // Count how many bookings exist for this hour slot; full at 5
            var bookingCount = bookedOnDate.filter(function(b){ return b === hourKey; }).length;
            var isBooked = bookingCount >= 5;
            var isPast   = isToday && (cur <= nptMinutes);
            var btn = document.createElement('button');
            btn.className = 'slot-btn' + (isBooked || isPast ? ' booked' : '');
            btn.disabled  = isBooked || isPast;
            btn.dataset.start = start; btn.dataset.end = finish;
            btn.textContent = formatTime(start) + ' – ' + formatTime(finish);
            if (isPast)   btn.title = 'This time has already passed';
            if (isBooked) btn.title = 'Fully booked (5/5)';
            if (!isBooked && !isPast) btn.addEventListener('click', onSlotClick);
            grid.appendChild(btn);
            cur += 60;
            hasSlots = true;
        }
        if (!hasSlots) grid.innerHTML = '<div class="no-slots">No slots available.</div>';
    }

    function onSlotClick() {
        document.querySelectorAll('.slot-btn').forEach(function(b){ b.classList.remove('selected'); });
        this.classList.add('selected');
        selectedStart = this.dataset.start; selectedEnd = this.dataset.end;
        updateSummary();
        document.getElementById('confirmBtn').disabled = false;
    }

    function updateSummary() {
        var el = document.getElementById('selectedSummary');
        if (selectedDate && selectedStart) {
            var dateFmt = new Date(selectedDate+'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric'});
            el.innerHTML = '<span>' + DOCTOR.name + ' &middot; ' + dateFmt + ' &middot; ' + formatTime(selectedStart) + '</span>';
        } else {
            el.textContent = 'No time selected';
        }
    }

    // Holds eSewa fields while the payment modal is open
    var pendingEsewaData = null;

    document.getElementById('confirmBtn').addEventListener('click', function() {
        if (!selectedDate || !selectedStart) return;
        var btn = this; btn.disabled = true; btn.textContent = 'Processing\u2026';
        fetch(BASE_URL + '/api/appointments', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ doctor_id: DOCTOR.id, date: selectedDate, start_time: selectedStart, end_time: selectedEnd, visit_reason: '' })
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            btn.disabled = false; btn.textContent = 'Confirm booking';
            if (!res.success) { showToast(res.message || 'Booking failed.', 'error'); return; }
            // Store fields, move modal to body to escape main-wrap overflow:hidden, then show
            pendingEsewaData = res;
            var modal = document.getElementById('payModal');
            document.body.appendChild(modal);
            modal.style.display = 'flex';
            modal.onclick = function(e) { if (e.target === modal) closePaymentModal(); };
        })
        .catch(function(){ showToast('Network error. Please try again.', 'error'); btn.disabled = false; btn.textContent = 'Confirm booking'; });
    });

    // Exposed globally so the modal's onclick attributes can call them
    window.closePaymentModal = function() {
        document.getElementById('payModal').style.display = 'none';
        pendingEsewaData = null;
    }

    window.submitToEsewa = function() {
        if (!pendingEsewaData) return;
        var form = document.createElement('form');
        form.method = 'POST'; form.action = pendingEsewaData.esewa_url;
        for (var key in pendingEsewaData.fields) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = key; inp.value = pendingEsewaData.fields[key];
            form.appendChild(inp);
        }
        document.body.appendChild(form);
        form.submit();
    }

    buildDateGrid();
})();
</script>

<?php
$content = ob_get_clean();

// Modal rendered outside .main-wrap via extra_scripts so position:fixed
// is not clipped by main-wrap's overflow:hidden stacking context
$extra_scripts = <<<HTML
<div id="payModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:10000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px 28px;max-width:360px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,.18);text-align:center;">
        <div style="font-size:40px;margin-bottom:12px;">&#x1F4B3;</div>
        <div style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:6px;">Complete Payment</div>
        <div style="font-size:13px;color:var(--muted);margin-bottom:24px;line-height:1.5;">Your slot is reserved. Pay now to confirm your appointment.</div>
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:13px;color:var(--muted);">Consultation fee</span>
            <span style="font-size:16px;font-weight:700;color:var(--text);">Rs 500.00</span>
        </div>
        <button onclick="submitToEsewa()" style="width:100%;padding:13px;background:#60BB46;color:#fff;border:none;border-radius:9px;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;">
            <svg width="20" height="20" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#fff"/><text x="50" y="67" text-anchor="middle" font-size="52" font-weight="900" fill="#60BB46" font-family="Arial">e</text></svg>
            Pay with eSewa
        </button>
        <button onclick="closePaymentModal()" style="width:100%;padding:12px;background:transparent;color:var(--muted);border:1px solid var(--border2);border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;">
            Cancel
        </button>
    </div>
</div>
HTML;

include __DIR__ . '/../../layouts/app.php';