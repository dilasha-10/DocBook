<!-- Patient Detail Panel -->
    <div id="patient-panel" class="patient-panel hidden">
        <div class="panel-header">
            <h2>Patient Details</h2>
            <button class="btn-close" onclick="closePatientDetail()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="panel-body">
            <div class="patient-info">
                <div class="patient-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3 id="panel-patient-name">John Patient</h3>
                <p id="panel-visit-reason">General check-up</p>
                <span class="badge badge-confirmed" id="panel-status">Confirmed</span>
            </div>
            
            <div class="comment-section">
                <h4>Visit History</h4>
                <div class="comment-list" id="comment-list">
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author">Dr. Sarah Lim</span>
                            <span class="comment-date">Mar 28, 2026</span>
                        </div>
                        <p class="comment-text">Patient showed improvement after medication adjustment. Continue current treatment.</p>
                    </div>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author">Dr. Sarah Lim</span>
                            <span class="comment-date">Mar 21, 2026</span>
                        </div>
                        <p class="comment-text">Initial consultation. Prescribed blood work and scheduled follow-up.</p>
                    </div>
                </div>
            </div>
            
            <div class="add-comment-section">
                <h4>Add Comment</h4>
                <textarea id="comment-input" class="comment-input" placeholder="Enter your notes about this visit..."></textarea>
                <button class="btn-primary" onclick="saveComment()">Save Comment</button>
            </div>
        </div>
    </div>