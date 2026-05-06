<?php
$title = 'Availability';
ob_start();
?>

<div id="availability-view" class="view-container">
    <div class="view-header">
        <div class="greeting">
            <h1>Update availability</h1>
            <p>Set your working hours</p>
        </div>
    </div>

    <div class="availability-container availability-single-col">
        <div class="availability-left">
            <div class="days-config">
                <div class="menu-title">Working days</div>

                <?php
                $days = [
                    ['sunday',    '09:00', '17:00', true],
                    ['monday',    '09:00', '17:00', true],
                    ['tuesday',   '09:00', '17:00', true],
                    ['wednesday', '09:00', '17:00', true],
                    ['thursday',  '09:00', '17:00', true],
                    ['friday',    '09:00', '14:00', true],
                ];
                foreach ($days as [$day, $start, $end, $active]):
                ?>
                <div class="day-config <?= $active ? 'active' : '' ?>" data-day="<?= $day ?>">
                    <div class="day-toggle" onclick="toggleDay('<?= $day ?>')">
                        <span><?= ucfirst($day) ?></span>
                        <label class="switch">
                            <input type="checkbox" id="<?= $day ?>-toggle" <?= $active ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="day-settings <?= $active ? '' : 'hidden' ?>" id="<?= $day ?>-settings">
                        <div class="time-inputs">
                            <div class="time-input-group">
                                <label>Start Time</label>
                                <input type="time" id="<?= $day ?>-start" value="<?= $start ?>">
                            </div>
                            <div class="time-input-group">
                                <label>End Time</label>
                                <input type="time" id="<?= $day ?>-end" value="<?= $end ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button class="btn-save" onclick="saveAvailability()">Save Availability</button>
        </div>
    </div>
</div>

<?php
$extra_scripts = <<<'JS'
<script>
(function(){
    // Load saved availability from API and pre-fill form
    fetch(BASE_URL + '/doctor/api/availability')
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!data.success || !data.schedule) return;
            data.schedule.forEach(function(row){
                var day = row.day.toLowerCase();
                var cb  = document.getElementById(day + '-toggle');
                var startEl = document.getElementById(day + '-start');
                var endEl   = document.getElementById(day + '-end');
                var settingsEl = document.getElementById(day + '-settings');
                var rowEl = document.querySelector('[data-day="' + day + '"]');
                if (cb) {
                    cb.checked = true;
                    if (rowEl) rowEl.classList.add('active');
                    if (settingsEl) settingsEl.classList.remove('hidden');
                }
                if (startEl && row.start_time) startEl.value = row.start_time.substring(0,5);
                if (endEl   && row.end_time)   endEl.value   = row.end_time.substring(0,5);
            });
        }).catch(function(){});

    window.toggleDay = function(day) {
        var cb       = document.getElementById(day + '-toggle');
        var row      = document.querySelector('[data-day="' + day + '"]');
        var settings = document.getElementById(day + '-settings');
        cb.checked = !cb.checked;
        if (cb.checked) {
            row.classList.add('active');
            settings.classList.remove('hidden');
        } else {
            row.classList.remove('active');
            settings.classList.add('hidden');
        }
    };

    window.saveAvailability = async function() {
        var days = ['sunday','monday','tuesday','wednesday','thursday','friday'];

        var schedule = days.map(function(day) {
            var cb = document.getElementById(day + '-toggle');
            var startEl = document.getElementById(day + '-start');
            var endEl   = document.getElementById(day + '-end');
            return {
                day:        day,
                is_active:  cb ? cb.checked : false,
                start_time: startEl ? startEl.value : '09:00',
                end_time:   endEl   ? endEl.value   : '17:00'
            };
        });

        var btn = document.querySelector('.btn-save');
        btn.disabled = true;
        btn.textContent = 'Saving\u2026';
        try {
            var res  = await fetch(BASE_URL + '/doctor/api/availability', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ schedule: schedule })
            });
            var data = await res.json();
            if (data.success) { showToast('Availability saved!', 'success'); }
            else               { showToast(data.message || 'Failed to save.', 'error'); }
        } catch(e) {
            showToast('Network error.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save Availability';
        }
    };
})();
</script>
JS;

$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app-doctor.php';