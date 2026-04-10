<div class="view-container">
    <div class="view-header">
        <div class="greeting">
            <h1>My Patients</h1>
            <p>View your patient directory</p>
        </div>
    </div>
    
    <div id="patient-list-container" style="display:grid; gap: 1.5rem; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); margin-top:20px;">
        <!-- Filled by JS -->
    </div>
</div>

<!-- Patient Detail Panel -->
<div id="patient-detail-panel" class="patient-panel hidden">
    <div class="panel-header">
        <h2>Patient Details</h2>
        <button class="btn-close" onclick="closePatientDetailPanel()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="panel-body">
        <div class="patient-info">
            <div class="patient-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h3 id="detail-patient-name">Patient Name</h3>
            <p id="detail-patient-email" style="color: #7f8c8d; font-size: 14px;"></p>
            <p id="detail-patient-phone" style="color: #7f8c8d; font-size: 14px;"></p>
            <p id="detail-patient-dob" style="color: #7f8c8d; font-size: 14px;"></p>
        </div>
        
        <div class="comment-section">
            <h4>Visit History & Comments</h4>
            <div class="comment-list" id="detail-comment-list">
                <p style="color: var(--text-muted); font-size: 14px;">Loading comments...</p>
            </div>
        </div>
        
        <div class="add-comment-section">
            <h4>Add Comment</h4>
            <textarea id="detail-comment-input" class="comment-input" placeholder="Enter your notes about this patient..."></textarea>
            <button class="btn-primary" onclick="savePatientComment()">Save Comment</button>
        </div>
    </div>
</div>