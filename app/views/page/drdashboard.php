<div id="dashboard-view" class="view-container">
            <div class="view-header">
                <div class="greeting">
                    <h1>Good morning, Dr. Lim</h1>
                    <p>You have 4 appointments today</p>
                </div>
                <button class="btn-primary" onclick="window.location.href='index.php?page=availability'">
                    Update availability
                </button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">4</div>
                    <div class="stat-label">Today's appointments</div>
                    <span class="stat-status status-active">Active</span>
                </div>
                <div class="stat-card">
                    <div class="stat-value">2</div>
                    <div class="stat-label">Pending approvals</div>
                    <span class="stat-status status-attention">Action needed</span>
                </div>
                <div class="stat-card">
                    <div class="stat-value">18</div>
                    <div class="stat-label">This week</div>
                    <span class="stat-status status-track">On track</span>
                </div>
                <div class="stat-card">
                    <div class="stat-value">124</div>
                    <div class="stat-label">Total patients</div>
                    <span class="stat-status status-all">All time</span>
                </div>
            </div>

            <div class="schedule-section">
                <h3>Today's schedule</h3>
                <div class="appointment-list" id="appointment-list">
                    <!-- Appointment 1 -->
                    <div class="appointment-item" id="appt-1" data-patient-id="1" onclick="openPatientDetail(1, this)">
                        <div class="appt-time">9:00 AM</div>
                        <div class="appt-details">
                            <div class="appt-icon"><i class="fas fa-user"></i></div>
                            <div class="appt-info">
                                <h4>John Patient</h4>
                                <p>General check-up · 30 min</p>
                            </div>
                        </div>
                        <div class="appt-actions">
                            <span class="badge badge-confirmed">Confirmed</span>
                        </div>
                    </div>

                    <!-- Appointment 2 -->
                    <div class="appointment-item" id="appt-2" data-patient-id="2" onclick="openPatientDetail(2, this)">
                        <div class="appt-time">9:30 AM</div>
                        <div class="appt-details">
                            <div class="appt-icon"><i class="fas fa-user"></i></div>
                            <div class="appt-info">
                                <h4>Emily Johnson</h4>
                                <p>Follow-up consultation · 15 min</p>
                            </div>
                        </div>
                        <div class="appt-actions">
                            <span class="badge badge-pending">Pending</span>
                            <button class="btn-sm btn-accept" onclick="handleAction(event, 'appt-2', 'accept')">Accept</button>
                            <button class="btn-sm btn-reject" onclick="handleAction(event, 'appt-2', 'reject')">Reject</button>
                        </div>
                    </div>

                    <!-- Appointment 3 -->
                    <div class="appointment-item" id="appt-3" data-patient-id="3" onclick="openPatientDetail(3, this)">
                        <div class="appt-time">10:30 AM</div>
                        <div class="appt-details">
                            <div class="appt-icon"><i class="fas fa-user"></i></div>
                            <div class="appt-info">
                                <h4>Mark Rivera</h4>
                                <p>New patient consultation · 30 min</p>
                            </div>
                        </div>
                        <div class="appt-actions">
                            <span class="badge badge-pending">Pending</span>
                            <button class="btn-sm btn-accept" onclick="handleAction(event, 'appt-3', 'accept')">Accept</button>
                            <button class="btn-sm btn-reject" onclick="handleAction(event, 'appt-3', 'reject')">Reject</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
    