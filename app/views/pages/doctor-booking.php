<?php
$title = 'Book Appointment';

// ── Precompute PHP-side data passed to JS ─────────────────────────────────────
$doc           = $doctor ?? [];
$doctorId      = (int) ($doc['id']               ?? 0);
$doctorName    = htmlspecialchars($doc['name']    ?? 'Doctor');
$specialty     = htmlspecialchars($doc['specialty']    ?? '');
$category      = htmlspecialchars($doc['category_name'] ?? '');
$experience    = (int) ($doc['experience_years']  ?? 0);
$fee           = number_format((float)($doc['fee'] ?? 0), 2);
$rating        = $doc['avg_rating'] ? number_format((float)$doc['avg_rating'], 1) : null;
$reviewCount   = (int) ($doc['review_count']      ?? 0);
$bio           = htmlspecialchars($doc['bio']      ?? '');
$slotMins      = (int) ($doc['avg_slot_minutes']   ?? 30);

// Avatar initials from name (strip "Dr." prefix)
$nameParts = explode(' ', preg_replace('/^Dr\.\s*/i', '', $doc['name'] ?? 'Doctor'));
$initials  = strtoupper(substr($nameParts[0] ?? 'D', 0, 1) . substr($nameParts[1] ?? '', 0, 1));

// Build availability map: { "Monday": {start, end, break}, ... }
$availMap = [];
foreach (($availability ?? []) as $row) {
    $availMap[$row['day_of_week']] = [
        'start'        => $row['start_time'],
        'end'          => $row['end_time'],
        'break_minutes'=> (int)$row['break_minutes'],
    ];
}

$extra_styles = <<<CSS
<style>
/* ── Layout ─────────────────────────────────────────────────────────────── */
.booking-wrap {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 0;
    min-height: calc(100vh - 60px);
    align-items: start;
}

/* ── Left: doctor profile ───────────────────────────────────────────────── */
.doctor-panel {
    padding: 36px 40px;
    border-right: 1px solid var(--border);
}

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

.doctor-stars {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    font-size: 13px;
    color: var(--muted);
}

.stars-row i { color: #ffd60a; font-size: 12px; }

/* About box */
.about-box {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 24px;
}

.about-box .about-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--hint);
    margin-bottom: 8px;
}

.about-box p {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.65;
}

/* Stats row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 0;
}

.stat-box {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px 18px;
    text-align: center;
}

.stat-box .stat-num {
    font-size: 26px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-box .stat-lbl {
    font-size: 11px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .06em;
}

/* ── Right: booking panel ───────────────────────────────────────────────── */
.booking-panel {
    padding: 28px 24px;
    position: sticky;
    top: 60px;
    background: var(--bg);
    min-height: calc(100vh - 60px);
    border-left: 1px solid var(--border);
}

.booking-panel h2 {
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 20px;
}

/* Date selector */
.date-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--hint);
    margin-bottom: 10px;
}

.week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 22px;
}

.day-hdr {
    text-align: center;
    font-size: 10px;
    font-weight: 600;
    color: var(--hint);
    letter-spacing: .05em;
    text-transform: uppercase;
    padding-bottom: 4px;
}

.day-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 7px 2px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--muted);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s;
    gap: 1px;
}

.day-btn:hover:not(:disabled) {
    background: var(--surface2);
    color: var(--text);
}

.day-btn.active {
    background: #4a9eff;
    color: #fff;
    border-color: #4a9eff;
    font-weight: 700;
}

.day-btn:disabled {
    opacity: .28;
    cursor: default;
}

/* Slots section */
.slots-heading {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 12px;
    font-weight: 500;
}

.slots-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 7px;
    margin-bottom: 22px;
    min-height: 80px;
}

.slot-btn {
    padding: 8px 4px;
    border-radius: 8px;
    border: 1px solid var(--border2);
    background: var(--surface);
    color: var(--text);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    transition: all .15s;
    white-space: nowrap;
}

.slot-btn:hover:not(:disabled):not(.booked) {
    border-color: #4a9eff;
    color: #4a9eff;
}

.slot-btn.selected {
    background: #4a9eff;
    border-color: #4a9eff;
    color: #fff;
    font-weight: 700;
}

.slot-btn.booked {
    text-decoration: line-through;
    opacity: .35;
    cursor: default;
}

.slot-btn:disabled {
    opacity: .25;
    cursor: default;
}

.no-slots {
    grid-column: 1 / -1;
    text-align: center;
    color: var(--hint);
    font-size: 13px;
    padding: 18px 0;
}

/* Selected summary */
.selected-summary {
    background: var(--surface2);
    border: 1px solid var(--border2);
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 13px;
    color: var(--muted);
    min-height: 44px;
    display: flex;
    align-items: center;
}

.selected-summary span {
    color: var(--text);
    font-weight: 500;
}

/* Confirm button */
.btn-confirm-booking {
    width: 100%;
    padding: 13px;
    background: #4a9eff;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background .18s, opacity .18s;
}

.btn-confirm-booking:hover:not(:disabled) { background: #3a8eef; }
.btn-confirm-booking:disabled { opacity: .4; cursor: default; }

/* Loading spinner inside slots */
.slots-loading {
    grid-column: 1/-1;
    text-align: center;
    color: var(--hint);
    font-size: 13px;
    padding: 18px 0;
}

/* ── Responsive ─────────────────────────────────────────────────────────── */
@media (max-width: 860px) {
    .booking-wrap {
        grid-template-columns: 1fr;
    }
    .doctor-panel {
        border-right: none;
        border-bottom: 1px solid var(--border);
        padding: 24px 20px;
    }
    .booking-panel {
        position: static;
        min-height: unset;
        padding: 24px 20px 36px;
        border-left: none;
    }
    .stats-row { grid-template-columns: repeat(3,1fr); }
}

@media (max-width: 480px) {
    .week-grid { gap: 3px; }
    .day-btn   { font-size: 11px; padding: 6px 1px; }
    .slots-grid { grid-template-columns: repeat(3,1fr); gap: 5px; }
    .slot-btn  { font-size: 11px; padding: 7px 2px; }
    .doctor-avatar-lg { width: 56px; height: 56px; font-size: 18px; }
    .doctor-info h1 { font-size: 18px; }
}
</style>
CSS;

ob_start();
?>

<div class="booking-wrap">

    <!-- ══ LEFT: Doctor profile ══ -->
    <div class="doctor-panel">

        <div class="doctor-header">
            <div class="doctor-avatar-lg"><?= $initials ?></div>
            <div class="doctor-info">
                <h1><?= $doctorName ?></h1>
                <div class="doctor-meta"><?= $category ?> &middot; <?= $experience ?> years experience</div>
                <?php if ($rating): ?>
                <div class="doctor-stars">
                    <?php
                    $full  = floor((float)$rating);
                    $half  = ((float)$rating - $full) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full)      echo '<i class="fa fa-star"></i>';
                        elseif ($i == $full+1 && $half) echo '<i class="fa fa-star-half-stroke"></i>';
                        else  echo '<i class="fa fa-star" style="opacity:.25"></i>';
                    }
                    ?>
                    <span><?= $rating ?> (<?= $reviewCount ?> reviews)</span>
                </div>
                <?php endif; ?>
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
                <div class="stat-num"><?= $reviewCount ?></div>
                <div class="stat-lbl">Reviews</div>
            </div>
            <div class="stat-box">
                <div class="stat-num"><?= $experience ?> yrs</div>
                <div class="stat-lbl">Experience</div>
            </div>
            <div class="stat-box">
                <div class="stat-num">500+</div>
                <div class="stat-lbl">Patients</div>
            </div>
        </div>

    </div><!-- /.doctor-panel -->

    <!-- ══ RIGHT: Booking panel ══ -->
    <div class="booking-panel">
        <h2>Book appointment</h2>

        <div class="date-label">Select date</div>

        <!-- Day-of-week headers -->
        <div class="week-grid" id="weekHeaders">
            <div class="day-hdr">Su</div>
            <div class="day-hdr">Mo</div>
            <div class="day-hdr">Tu</div>
            <div class="day-hdr">We</div>
            <div class="day-hdr">Th</div>
            <div class="day-hdr">Fr</div>
            <div class="day-hdr">Sa</div>
        </div>

        <!-- 7 date buttons rendered by JS -->
        <div class="week-grid" id="dateGrid"></div>

        <!-- Slots -->
        <div class="slots-heading" id="slotsHeading">Available slots</div>
        <div class="slots-grid"   id="slotsGrid">
            <div class="no-slots">Select a date to see available slots.</div>
        </div>

        <!-- Selected summary -->
        <div class="selected-summary" id="selectedSummary">
            No time selected
        </div>

        <!-- Confirm button -->
        <button class="btn-confirm-booking" id="confirmBtn" disabled>Confirm booking</button>
    </div><!-- /.booking-panel -->

</div><!-- /.booking-wrap -->

<?php
$content = ob_get_clean();

// JSON-encode availability data and pass to JS
$availJson  = json_encode($availMap,  JSON_HEX_TAG | JSON_HEX_APOS);
$doctorJson = json_encode([
    'id'        => $doctorId,
    'name'      => $doc['name']      ?? '',
    'specialty' => $doc['specialty'] ?? '',
    'fee'       => $fee,
    'slot_mins' => $slotMins,
], JSON_HEX_TAG | JSON_HEX_APOS);

$extra_scripts = <<<JS
<script>
(function () {
    // ── Data from PHP ──────────────────────────────────────────────────────
    var AVAIL     = {$availJson};
    var DOCTOR    = {$doctorJson};
    var BASE_URL  = '';

    // ── State ──────────────────────────────────────────────────────────────
    var selectedDate     = null;   // 'YYYY-MM-DD'
    var selectedStart    = null;   // 'HH:MM:00'
    var selectedEnd      = null;
    var bookedOnDate     = [];     // fetched from server

    var DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    // ── Build 7-day window starting today ──────────────────────────────────
    function buildDateGrid() {
        var grid = document.getElementById('dateGrid');
        grid.innerHTML = '';
        var today = new Date();
        today.setHours(0,0,0,0);

        for (var i = 0; i < 7; i++) {
            var d = new Date(today);
            d.setDate(today.getDate() + i);
            var dayName = DAY_NAMES[d.getDay()];
            var isAvail = !!AVAIL[dayName];

            var btn = document.createElement('button');
            btn.className = 'day-btn';
            btn.disabled  = !isAvail;
            btn.dataset.date = toISO(d);
            btn.dataset.day  = dayName;
            btn.innerHTML    = '<span>' + d.getDate() + '</span>';

            if (isAvail) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.day-btn').forEach(function(b){ b.classList.remove('active'); });
                    this.classList.add('active');
                    selectDate(this.dataset.date, this.dataset.day);
                });
            }
            grid.appendChild(btn);
        }
    }

    function toISO(d) {
        var mm = String(d.getMonth()+1).padStart(2,'0');
        var dd = String(d.getDate()).padStart(2,'0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

    // ── Select a date, fetch booked slots, build time grid ─────────────────
    function selectDate(dateStr, dayName) {
        selectedDate  = dateStr;
        selectedStart = null;
        selectedEnd   = null;
        updateSummary();
        document.getElementById('confirmBtn').disabled = true;

        var heading = document.getElementById('slotsHeading');
        var slotsFmt = new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric'});
        heading.textContent = 'Available slots \u2014 ' + slotsFmt;

        var grid = document.getElementById('slotsGrid');
        grid.innerHTML = '<div class="slots-loading"><i class="fa fa-spinner fa-spin"></i> Loading slots\u2026</div>';

        // Fetch booked slots for this date
        fetch('/api/slots?doctor_id=' + DOCTOR.id + '&date=' + dateStr)
            .then(function(r){ return r.json(); })
            .then(function(res){
                bookedOnDate = res.booked || [];
                buildSlotsGrid(dayName);
            })
            .catch(function(){
                bookedOnDate = [];
                buildSlotsGrid(dayName);
            });
    }

    // ── Generate time slots from availability window ────────────────────────
    function buildSlotsGrid(dayName) {
        var info = AVAIL[dayName];
        if (!info) return;

        var grid = document.getElementById('slotsGrid');
        grid.innerHTML = '';

        var slots = generateSlots(info.start, info.end, DOCTOR.slot_mins);
        if (slots.length === 0) {
            grid.innerHTML = '<div class="no-slots">No slots available.</div>';
            return;
        }

        slots.forEach(function(s) {
            var isBooked = bookedOnDate.indexOf(s.start) !== -1;
            var btn = document.createElement('button');
            btn.className  = 'slot-btn' + (isBooked ? ' booked' : '');
            btn.disabled   = isBooked;
            btn.dataset.start = s.start;
            btn.dataset.end   = s.end;
            btn.textContent = formatTime(s.start);

            if (!isBooked) {
                btn.addEventListener('click', function(){
                    document.querySelectorAll('.slot-btn').forEach(function(b){ b.classList.remove('selected'); });
                    this.classList.add('selected');
                    selectedStart = this.dataset.start;
                    selectedEnd   = this.dataset.end;
                    updateSummary();
                    document.getElementById('confirmBtn').disabled = false;
                });
            }
            grid.appendChild(btn);
        });
    }

    // Generate HH:MM:00 slot list
    function generateSlots(startStr, endStr, mins) {
        var slots = [];
        var cur  = timeToMins(startStr);
        var end  = timeToMins(endStr);
        while (cur + mins <= end) {
            slots.push({ start: minsToTime(cur), end: minsToTime(cur + mins) });
            cur += mins;
        }
        return slots;
    }

    function timeToMins(t) {
        var parts = t.split(':');
        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
    }

    function minsToTime(m) {
        var h = Math.floor(m / 60);
        var min = m % 60;
        return String(h).padStart(2,'0') + ':' + String(min).padStart(2,'0') + ':00';
    }

    function formatTime(t) {
        var h = parseInt(t.split(':')[0]);
        var m = t.split(':')[1];
        var ampm = h >= 12 ? 'PM' : 'AM';
        return (h % 12 || 12) + ':' + m + ' ' + ampm;
    }

    // ── Update summary line ─────────────────────────────────────────────────
    function updateSummary() {
        var el = document.getElementById('selectedSummary');
        if (selectedDate && selectedStart) {
            var dateFmt = new Date(selectedDate + 'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric'});
            el.innerHTML = '<span>' + DOCTOR.name + ' &middot; ' + dateFmt + ' &middot; ' + formatTime(selectedStart) + '</span>';
        } else {
            el.textContent = 'No time selected';
        }
    }

    // ── Confirm booking ─────────────────────────────────────────────────────
    document.getElementById('confirmBtn').addEventListener('click', function () {
        if (!selectedDate || !selectedStart) return;

        this.disabled    = true;
        this.textContent = 'Booking\u2026';

        fetch('/api/appointments', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                doctor_id:    DOCTOR.id,
                date:         selectedDate,
                start_time:   selectedStart,
                end_time:     selectedEnd,
                visit_reason: ''
            })
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success && res.redirect) {
                // Replace so back button skips this page
                window.location.replace(res.redirect);
            } else {
                showToast(res.message || 'Booking failed.', 'error');
                document.getElementById('confirmBtn').disabled = false;
                document.getElementById('confirmBtn').textContent = 'Confirm booking';
            }
        })
        .catch(function(){
            showToast('Network error. Please try again.', 'error');
            document.getElementById('confirmBtn').disabled = false;
            document.getElementById('confirmBtn').textContent = 'Confirm booking';
        });
    });

    // ── Init ────────────────────────────────────────────────────────────────
    buildDateGrid();
})();
</script>
JS;

include __DIR__ . '/../layouts/app.php';