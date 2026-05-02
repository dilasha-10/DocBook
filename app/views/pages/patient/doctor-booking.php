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
        <div class="week-grid">
            <div class="day-hdr">Su</div><div class="day-hdr">Mo</div><div class="day-hdr">Tu</div>
            <div class="day-hdr">We</div><div class="day-hdr">Th</div><div class="day-hdr">Fr</div>
            <div class="day-hdr">Sa</div>
        </div>
        <div class="week-grid" id="dateGrid"></div>

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
    var DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var selectedDate = null, selectedStart = null, selectedEnd = null, bookedOnDate = [];

    function toISO(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }
    function timeToMins(t) { var p = t.split(':'); return parseInt(p[0])*60 + parseInt(p[1]); }
    function minsToTime(m) { return String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0') + ':00'; }
    function formatTime(t) {
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
            btn.dataset.date = toISO(d);
            btn.dataset.day  = dayName;
            btn.innerHTML = '<span>' + d.getDate() + '</span>';
            if (AVAIL[dayName]) btn.addEventListener('click', onDayClick);
            grid.appendChild(btn);
        }
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
            .then(function(res){ bookedOnDate = res.booked || []; buildSlotsGrid(dayName); })
            .catch(function(){ bookedOnDate = []; buildSlotsGrid(dayName); });
    }

    function buildSlotsGrid(dayName) {
        var info = AVAIL[dayName], grid = document.getElementById('slotsGrid');
        if (!info) return;
        grid.innerHTML = '';
        var cur = timeToMins(info.start), end = timeToMins(info.end), hasSlots = false;
        while (cur + DOCTOR.slot_mins <= end) {
            var start = minsToTime(cur), finish = minsToTime(cur + DOCTOR.slot_mins);
            var isBooked = bookedOnDate.indexOf(start) !== -1;
            var btn = document.createElement('button');
            btn.className = 'slot-btn' + (isBooked ? ' booked' : '');
            btn.disabled  = isBooked;
            btn.dataset.start = start; btn.dataset.end = finish;
            btn.textContent = formatTime(start);
            if (!isBooked) btn.addEventListener('click', onSlotClick);
            grid.appendChild(btn);
            cur += DOCTOR.slot_mins;
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

    document.getElementById('confirmBtn').addEventListener('click', function() {
        if (!selectedDate || !selectedStart) return;
        var btn = this; btn.disabled = true; btn.textContent = 'Booking\u2026';
        fetch(BASE_URL + '/api/appointments', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ doctor_id: DOCTOR.id, date: selectedDate, start_time: selectedStart, end_time: selectedEnd, visit_reason: '' })
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success && res.redirect) { window.location.replace(res.redirect); }
            else { showToast(res.message || 'Booking failed.', 'error'); btn.disabled = false; btn.textContent = 'Confirm booking'; }
        })
        .catch(function(){ showToast('Network error. Please try again.', 'error'); btn.disabled = false; btn.textContent = 'Confirm booking'; });
    });

    buildDateGrid();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';