<div class="view-container">
    <div class="view-header" style="justify-content: space-between;">
        <div class="greeting">
            <h1>My Schedule</h1>
            <p>View your schedule by date</p>
        </div>
        <div>
            <input type="date" id="schedule-date-picker" onchange="loadScheduleAppointments()" 
                   style="padding: 10px; border-radius: 8px; border: 1px solid #ccc;"
                   value="2026-03-31">
        </div>
    </div>

    <div class="schedule-section" style="margin-top: 20px;">
        <div class="appointment-list" id="schedule-appointment-list">
            <p style="color:var(--text-muted);">Loading schedule...</p>
        </div>
    </div>
</div>