// View Switching Logic
function switchView(viewId, navElement) {
    // Hide all views
    document.querySelectorAll('.view-container').forEach(el => el.classList.add('hidden'));
    
    // Show selected view
    const targetView = document.getElementById(viewId + '-view');
    if(targetView) targetView.classList.remove('hidden');

    // Update Sidebar Active State
    if(navElement) {
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        navElement.classList.add('active');
    }
}

// Appointment Actions
async function handleAction(event, stringId, action) {
    event.stopPropagation(); // Prevent opening patient detail
    const el = document.getElementById(stringId);
    if(!el) return;
    
    const numericId = stringId.replace('appt-', '');
    const newStatus = action === 'accept' ? 'Confirmed' : 'Rejected';
    
    try {
        const response = await fetch('../app/controllers/update-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ appointment_id: numericId, status: newStatus })
        });
        const data = await response.json();
        
        if (data.success) {
            if (action === 'accept') {
                const actionsDiv = el.querySelector('.appt-actions');
                actionsDiv.innerHTML = '<span class="badge badge-confirmed">Confirmed</span>';
            } else if (action === 'reject') {
                el.style.opacity = '0.5';
                el.style.pointerEvents = 'none';
                const actionsDiv = el.querySelector('.appt-actions');
                actionsDiv.innerHTML = '<span class="badge" style="background:rgba(231, 76, 60, 0.2); color:#e74c3c">Rejected</span>';
            }
        } else {
            alert('Error updating status: ' + (data.message || 'Unknown error'));
        }
    } catch (e) {
        console.error('Error:', e);
        alert('Server error updating status.');
    }
}

// Real data will be fetched from API
let currentAppointmentId = null;

async function loadDashboardAppointments() {
    try {
        // Fetch appointments for the seeded date (or current date if you update the seed)
        // Hardcoded date here just to show the seeded data from your database:
        const response = await fetch('../app/controllers/appointments.php?date=2026-03-31');
        const data = await response.json();
        
        if (data.success) {
            renderDashboardAppointments(data.appointments);
        }
    } catch (e) {
        console.error("Failed to load appointments:", e);
    }
}

function renderDashboardAppointments(appointments) {
    const list = document.getElementById('appointment-list');
    if (!list) return;

    if (appointments.length === 0) {
        list.innerHTML = '<p>No appointments for today.</p>';
        return;
    }

    list.innerHTML = appointments.map(appt => {
        let badgeClass = 'badge-pending';
        if (appt.status === 'Confirmed') badgeClass = 'badge-confirmed';
        
        let actionButtons = '';
        if (appt.status === 'Pending') {
            actionButtons = `
                <button class="btn-sm btn-accept" onclick="handleAction(event, 'appt-${appt.id}', 'accept')">Accept</button>
                <button class="btn-sm btn-reject" onclick="handleAction(event, 'appt-${appt.id}', 'reject')">Reject</button>
            `;
        }
        
        return `
            <div class="appointment-item" id="appt-${appt.id}" data-appointment-id="${appt.id}" onclick="openAppointmentDetail(${appt.id}, this)">
                <div class="appt-time">${appt.time}</div>
                <div class="appt-details">
                    <div class="appt-icon"><i class="fas fa-user"></i></div>
                    <div class="appt-info">
                        <h4>${appt.patient_name}</h4>
                        <p>${appt.visit_reason} &middot; ${appt.duration_minutes} min</p>
                    </div>
                </div>
                <div class="appt-actions">
                    <span class="badge ${badgeClass}">${appt.status}</span>
                    ${actionButtons}
                </div>
            </div>
        `;
    }).join('');
}

async function openAppointmentDetail(appointmentId, element) {
    currentAppointmentId = appointmentId;
    
    try {
        const response = await fetch('../app/controllers/appointment-detail.php?id=' + appointmentId);
        const data = await response.json();
        
        if (data.success && data.appointment) {
            const appt = data.appointment;
            // The backend returns it in `data.appointment`
            // Update panel content - it might need a small adjustment if your backend doesn't return exactly patient name, 
            // but the dummy data had it. Let's look up the name from the DOM or we can fetch the patient details separately.
            // Wait, appointment-detail.php didn't return patient name, it returned doctor.
            // I'll grab the name from the clicked element for now!
            const patientName = element.querySelector('h4').textContent;
            
            document.getElementById('panel-patient-name').textContent = patientName;
            document.getElementById('panel-visit-reason').textContent = appt.visit_reason;
            document.getElementById('panel-status').textContent = appt.status;
            
            // Update status badge class
            const statusBadge = document.getElementById('panel-status');
            statusBadge.className = 'badge ' + (appt.status === 'Confirmed' ? 'badge-confirmed' : 'badge-pending');
            
            // Render comments. The API returned comments as array of {id, text, date}
            // Map text to text, and we can hardcode doctor author since it's the doctor's panel
            const mappedComments = appt.comments.map(c => ({
                author: appt.doctor.name,
                date: c.date,
                text: c.text
            }));
            
            renderComments(mappedComments);
            
            // Show panel
            const panel = document.getElementById('patient-panel');
            panel.classList.remove('hidden');
            
            // Highlight selected appointment
            document.querySelectorAll('.appointment-item').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
        }
    } catch (e) {
        console.error("Failed to fetch appointment detail:", e);
    }
}

function closePatientDetail() {
    const panel = document.getElementById('patient-panel');
    panel.classList.add('hidden');
    currentAppointmentId = null;
    
    // Remove selection highlight
    document.querySelectorAll('.appointment-item').forEach(el => el.classList.remove('selected'));
}

function renderComments(comments) {
    const commentList = document.getElementById('comment-list');
    
    if (comments.length === 0) {
        commentList.innerHTML = '<p style="color: var(--text-muted); font-size: 14px;">No comments yet.</p>';
        return;
    }
    
    commentList.innerHTML = comments.map(comment => `
        <div class="comment-item">
            <div class="comment-header">
                <span class="comment-author">${comment.author}</span>
                <span class="comment-date">${comment.date}</span>
            </div>
            <p class="comment-text">${comment.text}</p>
        </div>
    `).join('');
}

async function saveComment() {
    const input = document.getElementById('comment-input');
    const commentText = input.value.trim();
    
    if (!commentText) {
        alert('Please enter a comment');
        return;
    }
    
    if (!currentAppointmentId) return;
    
    try {
        const fd = new FormData();
        fd.append('appointment_id', currentAppointmentId);
        fd.append('comment_text', commentText);

        const response = await fetch('../app/controllers/comment.php', {
            method: 'POST',
            body: fd
        });

        const data = await response.json();
        if (data.success) {
            // Clear input and reload the current appointment panel to get updated comments
            input.value = '';
            const selectedElement = document.querySelector('.appointment-item.selected');
            if (selectedElement) {
                openAppointmentDetail(currentAppointmentId, selectedElement);
            }
        } else {
            alert('Error adding comment: ' + data.message);
        }
    } catch (e) {
        console.error("Failed to add comment:", e);
    }
}



// Availability UI - D2-03
let currentPreviewDay = 'monday';
let previewSlots = [];

async function initAvailability() {
    try {
        const response = await fetch('../api/availability.php');
        const data = await response.json();
        
        // Create a map of saved days for quick lookup
        const savedDays = {};
        if (data.success && data.schedule) {
            data.schedule.forEach(dayConfig => {
                savedDays[dayConfig.day] = dayConfig;
            });
        }
        
        // Process all 7 days
        ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
            const checkbox = document.getElementById(day + '-toggle');
            const startInput = document.getElementById(day + '-start');
            const endInput = document.getElementById(day + '-end');
            const configEl = document.querySelector(`[data-day="${day}"]`);
            const settingsEl = document.getElementById(day + '-settings');
            
            // Check if this day is in the saved schedule
            const isActive = savedDays.hasOwnProperty(day);
            
            if (checkbox) checkbox.checked = isActive;
            
            if (isActive && savedDays[day]) {
                // Day is saved - populate its times
                const times = savedDays[day].start_time.substring(0, 5);
                const timee = savedDays[day].end_time.substring(0, 5);
                if (startInput) startInput.value = times;
                if (endInput) endInput.value = timee;
                
                if (configEl) configEl.classList.add('active');
                if (settingsEl) settingsEl.classList.remove('hidden');
            } else {
                // Day is not saved - set defaults and hide
                if (startInput) startInput.value = '09:00';
                if (endInput) endInput.value = '17:00';
                
                if (configEl) configEl.classList.remove('active');
                if (settingsEl) settingsEl.classList.add('hidden');
            }
        });
    } catch(e) {
        console.error("Failed to fetch availability:", e);
    }
    updateSlots();
}

function toggleDay(day) {
    const checkbox = document.getElementById(day + '-toggle');
    const configEl = document.querySelector(`[data-day="${day}"]`);
    const settingsEl = document.getElementById(day + '-settings');
    
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        configEl.classList.add('active');
        settingsEl.classList.remove('hidden');
    } else {
        configEl.classList.remove('active');
        settingsEl.classList.add('hidden');
    }
    
    updateSlots();
}

function setPreviewDay(day) {
    currentPreviewDay = day;
    
    // Update button states
    document.querySelectorAll('.preview-day-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.previewDay === day) {
            btn.classList.add('active');
        }
    });
    
    renderPreviewSlots();
}

function getBreakDuration() {
    const selected = document.querySelector('input[name="break-duration"]:checked');
    return selected ? parseInt(selected.value) : 5;
}

function getDaySettings(day) {
    const isActive = document.getElementById(day + '-toggle')?.checked || false;
    const startTime = document.getElementById(day + '-start')?.value || '09:00';
    const endTime = document.getElementById(day + '-end')?.value || '17:00';
    
    return { isActive, startTime, endTime };
}

function generateSlots(day) {
    const settings = getDaySettings(day);
    const breakDuration = getBreakDuration();
    const slots = [];
    
    if (!settings.isActive) return slots;
    
    let currentTime = settings.startTime;
    const endTime = settings.endTime;

    // Numeric comparisons are more reliable than string comparisons
    const [endH, endM] = endTime.split(':').map(Number);
    const endTimeMinutes = endH * 60 + endM;

    while (true) {
        const [hours, minutes] = currentTime.split(':').map(Number);
        const timeInMinutes = hours * 60 + minutes;

        if (timeInMinutes >= endTimeMinutes) break;

        const slotEndMinutes = timeInMinutes + 30;
        if (slotEndMinutes > endTimeMinutes) break;

        // Add slot
        slots.push({
            time: formatTime(currentTime),
            isBreak: false
        });

        // Add break after slot if it fits
        const breakEndMinutes = slotEndMinutes + breakDuration;
        if (breakEndMinutes < endTimeMinutes) {
            const breakHours = Math.floor(breakEndMinutes / 60);
            const breakMins = breakEndMinutes % 60;
            const breakTime = `${String(breakHours).padStart(2, '0')}:${String(breakMins).padStart(2, '0')}`;

            slots.push({
                time: formatTime(breakTime),
                isBreak: true
            });
        }

        // Next slot start
        const nextSlotMinutes = slotEndMinutes + breakDuration;
        const nextHours = Math.floor(nextSlotMinutes / 60);
        const nextMins = nextSlotMinutes % 60;
        currentTime = `${String(nextHours).padStart(2, '0')}:${String(nextMins).padStart(2, '0')}`;
    }
    
    return slots;
}

function formatTime(time24) {
    const [hours, minutes] = time24.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

function updateSlots() {
    renderPreviewSlots();
}

function renderPreviewSlots() {
    const grid = document.getElementById('slots-preview-grid');
    const daySettings = getDaySettings(currentPreviewDay);
    const slots = generateSlots(currentPreviewDay);
    
    if (!daySettings.isActive) {
        grid.innerHTML = '<p style="color: var(--text-muted); grid-column: 1/-1; text-align: center; padding: 40px;">This day is not set as working day</p>';
        return;
    }
    
    if (slots.length === 0) {
        grid.innerHTML = '<p style="color: var(--text-muted); grid-column: 1/-1; text-align: center; padding: 40px;">No slots available for this day</p>';
        return;
    }
    
    grid.innerHTML = slots.map(slot => `
        <div class="preview-slot ${slot.isBreak ? 'break' : ''}">
            ${slot.time}${slot.isBreak ? ' (Break)' : ''}
        </div>
    `).join('');
}

async function saveAvailability() {
    // Collect all day settings
    const availability = [];
    ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
        const settings = getDaySettings(day);
        if (settings.isActive) {
            availability.push({
                day,
                is_active: true,
                start_time: settings.startTime,
                end_time: settings.endTime,
                break_minutes: getBreakDuration()
            });
        }
    });
    
    try {
        const response = await fetch('../api/availability.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ schedule: availability })
        });
        const data = await response.json();
        
        if(data.success) {
            showToast('Availability saved successfully!', 'success');
            // Reload after successful save to ensure UI matches saved data
            setTimeout(() => {
                initAvailability();
            }, 500);
        } else {
            showToast('Error saving availability: ' + data.message, 'danger');
        }
    } catch (e) {
        showToast('Error communicating with server', 'danger');
        console.error('Save error:', e);
    }
}

function showToast(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initAvailability();
    loadDashboardAppointments();

    if(document.getElementById('patient-list-container')) {
        loadPatientList();
    }
    if(document.getElementById('schedule-appointment-list')) {
        loadScheduleAppointments();
    }
});

// Load Patient Directory
let currentPatientId = null;

async function loadPatientList() {
    const list = document.getElementById('patient-list-container');
    list.innerHTML = '<p>Loading patients...</p>';
    try {
        const response = await fetch('../app/controllers/patient.php');
        const data = await response.json();
        if (data.success) {
            if (data.patients.length === 0) {
                list.innerHTML = '<p>No patients found.</p>';
            } else {
                list.innerHTML = data.patients.map(p => `
                    <div class="patient-card" data-patient-id="${p.id}" onclick="openPatientDetail(${p.id}, this)" style="background:white; padding: 20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.05); display:flex; flex-direction:column; gap:10px; cursor:pointer; transition:all 0.3s ease;">
                        <h3 style="color:#2c3e50; margin:0;">${p.name}</h3>
                        <div style="font-size:14px; color:#7f8c8d;"><i class="fas fa-envelope"></i> ${p.email}</div>
                        <div style="font-size:14px; color:#7f8c8d;"><i class="fas fa-phone"></i> ${p.phone}</div>
                        <div style="font-size:14px; color:#7f8c8d;"><i class="fas fa-calendar-check"></i> Last Visit: ${p.last_visit ? p.last_visit : 'Never'}</div>
                    </div>
                `).join('');
            }
        }
    } catch (e) {
        list.innerHTML = '<p>Error loading patients.</p>';
    }
}

async function openPatientDetail(patientId, element) {
    currentPatientId = patientId;
    
    try {
        const response = await fetch('../app/controllers/patient.php?id=' + patientId);
        const data = await response.json();
        
        if (data.success && data.patient) {
            const patient = data.patient;
            
            // Update panel content
            document.getElementById('detail-patient-name').textContent = patient.name;
            document.getElementById('detail-patient-email').textContent = '📧 ' + patient.email;
            document.getElementById('detail-patient-phone').textContent = '📞 ' + patient.phone;
            document.getElementById('detail-patient-dob').textContent = '📅 DOB: ' + patient.date_of_birth;
            
            // Render comments from comment_history
            renderPatientComments(data.comment_history);
            
            // Show panel
            const panel = document.getElementById('patient-detail-panel');
            panel.classList.remove('hidden');
            
            // Highlight selected patient card
            document.querySelectorAll('.patient-card').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
        }
    } catch (e) {
        console.error("Failed to fetch patient detail:", e);
        alert('Error loading patient details');
    }
}

function closePatientDetailPanel() {
    const panel = document.getElementById('patient-detail-panel');
    panel.classList.add('hidden');
    currentPatientId = null;
    
    // Remove selection highlight
    document.querySelectorAll('.patient-card').forEach(el => el.classList.remove('selected'));
}

function renderPatientComments(comments) {
    const commentList = document.getElementById('detail-comment-list');
    
    if (comments.length === 0) {
        commentList.innerHTML = '<p style="color: var(--text-muted); font-size: 14px;">No comments yet.</p>';
        return;
    }
    
    commentList.innerHTML = comments.map(comment => `
        <div class="comment-item">
            <div class="comment-header">
                <span class="comment-author">${comment.author}</span>
                <span class="comment-date">${comment.date}</span>
            </div>
            <p class="comment-text"><strong>${comment.visit_reason}</strong> - ${comment.appointment_date} at ${comment.appointment_time}</p>
            <p class="comment-text">${comment.text}</p>
        </div>
    `).join('');
}

async function savePatientComment() {
    const input = document.getElementById('detail-comment-input');
    const commentText = input.value.trim();
    
    if (!commentText) {
        alert('Please enter a comment');
        return;
    }
    
    if (!currentPatientId) return;
    
    // For patient comments, we need the most recent appointment for this patient
    try {
        // First fetch patient appointments to get the latest one
        const response = await fetch('../app/controllers/patient.php?id=' + currentPatientId);
        const patientData = await response.json();
        
        if (patientData.success && patientData.appointments && patientData.appointments.length > 0) {
            // Use the first appointment (most recent, since API returns sorted DESC)
            const latestAppt = patientData.appointments[0];
            
            const fd = new FormData();
            fd.append('appointment_id', latestAppt.id);
            fd.append('comment_text', commentText);

            const commentResponse = await fetch('../app/controllers/comment.php', {
                method: 'POST',
                body: fd
            });

            const commentData = await commentResponse.json();
            if (commentData.success) {
                // Clear input and reload the current patient panel to get updated comments
                input.value = '';
                openPatientDetail(currentPatientId, document.querySelector(`[data-patient-id="${currentPatientId}"]`));
            } else {
                alert('Error adding comment: ' + commentData.message);
            }
        } else {
            alert('No appointments found for this patient.');
        }
    } catch (e) {
        console.error("Failed to add comment:", e);
        alert('Error adding comment');
    }
}

// Load Schedule by Date
async function loadScheduleAppointments() {
    const list = document.getElementById('schedule-appointment-list');
    const dateInput = document.getElementById('schedule-date-picker').value;
    list.innerHTML = '<p>Loading...</p>';
    
    try {
        const response = await fetch('../app/controllers/appointments.php?date=' + dateInput);
        const data = await response.json();
        
        if (data.success) {
            if (data.appointments.length === 0) {
                list.innerHTML = '<p>No appointments for this date.</p>';
                return;
            }

            list.innerHTML = data.appointments.map(appt => {
                let badgeClass = 'badge-pending';
                if (appt.status === 'Confirmed') badgeClass = 'badge-confirmed';
                return `
                    <div class="appointment-item" style="cursor:default">
                        <div class="appt-time">${appt.time}</div>
                        <div class="appt-details">
                            <div class="appt-icon"><i class="fas fa-user"></i></div>
                            <div class="appt-info">
                                <h4>${appt.patient_name}</h4>
                                <p>${appt.visit_reason} &middot; ${appt.duration_minutes} min</p>
                            </div>
                        </div>
                        <div class="appt-actions">
                            <span class="badge ${badgeClass}">${appt.status}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }
    } catch (e) {
        list.innerHTML = '<p>Error loading schedule.</p>';
    }
}

const DOCTOR_ID = 1;
let selectedSlots = [];
let availableSlots = [];

document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('date-picker');
    if (!dateInput) return;

    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    dateInput.value = today;

    loadSlots();
});

function toggleBio() {
    const bio = document.getElementById('doctor-bio');
    const btn = document.querySelector('.btn-read-more');

    if (!bio || !btn) return;

    bio.classList.toggle('truncated');
    btn.textContent = bio.classList.contains('truncated') ? 'Read More' : 'Show Less';
}

// Profile Editing Functions
async function loadProfileData() {
    try {
        const response = await fetch('../app/controllers/profile.php');
        const data = await response.json();
        
        if (data.success) {
            const doctor = data.doctor;
            
            // Populate view mode
            document.getElementById('doctor-name').textContent = 'Dr. ' + doctor.name;
            document.getElementById('view-specialty').textContent = doctor.specialty;
            document.getElementById('view-experience').textContent = doctor.years_experience + ' years exp.';
            document.getElementById('view-rating').textContent = doctor.rating + ' (' + doctor.review_count + ' reviews)';
            document.getElementById('doctor-fee').textContent = doctor.fee;
            document.getElementById('doctor-bio').textContent = doctor.bio || 'No bio provided';
            
            // Update photo in view mode
            if (doctor.photo_url) {
                const photoDiv = document.getElementById('view-profile-photo');
                photoDiv.innerHTML = '<img src="' + doctor.photo_url + '" alt="Profile">';
            }
        }
    } catch (e) {
        console.error('Failed to load profile data:', e);
    }
}

function enterEditMode() {
    const viewMode = document.getElementById('profile-view-mode');
    const editMode = document.getElementById('profile-edit-mode');
    
    if (!viewMode || !editMode) return;
    
    // Get current values from view mode
    const name = document.getElementById('doctor-name').textContent.replace('Dr. ', '');
    const specialty = document.getElementById('view-specialty').textContent;
    const experience = document.getElementById('view-experience').textContent.split(' ')[0];
    const fee = document.getElementById('doctor-fee').textContent;
    const bio = document.getElementById('doctor-bio').textContent;
    
    // Populate form
    document.getElementById('name-input').value = name;
    document.getElementById('specialty-input').value = specialty;
    document.getElementById('experience-input').value = experience;
    document.getElementById('fee-input').value = fee;
    document.getElementById('bio-input').value = bio;
    
    // Hide view mode, show edit mode
    viewMode.classList.add('hidden');
    editMode.classList.remove('hidden');
}

function exitEditMode() {
    const viewMode = document.getElementById('profile-view-mode');
    const editMode = document.getElementById('profile-edit-mode');
    
    if (!viewMode || !editMode) return;
    
    viewMode.classList.remove('hidden');
    editMode.classList.add('hidden');
    
    // Reset form
    document.getElementById('profile-form').reset();
    document.getElementById('edit-photo-preview').innerHTML = '<i class="fas fa-user"></i>';
}

function previewPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('edit-photo-preview');
        preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
    };
    reader.readAsDataURL(file);
}

async function saveProfileChanges(event) {
    event.preventDefault();
    
    const form = document.getElementById('profile-form');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('../app/controllers/profile.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Profile updated successfully!', 'success');
            
            // Reload profile data and exit edit mode
            await loadProfileData();
            exitEditMode();
        } else {
            showToast('Error: ' + (data.message || 'Failed to update profile'), 'danger');
        }
    } catch (e) {
        console.error('Error saving profile:', e);
        showToast('Error saving profile', 'danger');
    }
}

// Initialize profile on page load
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        loadProfileData();
        profileForm.addEventListener('submit', saveProfileChanges);
    }
});

function loadSlots() {
    const grid = document.getElementById('slots-grid');
    if (!grid) return;

    selectedSlots = [];
    updateBookingButton();

    const slots = generateDemoSlots();
    renderSlots(slots);
}

function generateDemoSlots() {
    const times = [
        '9:00 AM','9:30 AM','10:00 AM','10:30 AM',
        '11:00 AM','11:30 AM','12:00 PM','12:30 PM',
        '1:00 PM','1:30 PM','2:00 PM','2:30 PM',
        '3:00 PM','3:30 PM','4:00 PM','4:30 PM'
    ];

    return times.map((time, i) => ({
        time,
        status: [2,5,11,12].includes(i) ? 'booked' : (i===6||i===7 ? 'break' : 'available')
    }));
}

function renderSlots(slots) {
    const grid = document.getElementById('slots-grid');
    availableSlots = slots;

    grid.innerHTML = slots.map((slot, i) => `
        <div class="slot ${slot.status}" 
             onclick="${slot.status==='available' ? `selectSlot(${i})` : ''}">
            ${slot.time}
        </div>
    `).join('');
}

function selectSlot(i) {
    if (availableSlots[i].status !== 'available') return;

    const index = selectedSlots.indexOf(i);
    index > -1 ? selectedSlots.splice(index,1) : selectedSlots.push(i);

    renderSlotsWithSelection();
    updateBookingButton();
}

function renderSlotsWithSelection() {
    const grid = document.getElementById('slots-grid');

    grid.innerHTML = availableSlots.map((slot,i)=>{
        let cls = 'slot ' + slot.status;
        if (selectedSlots.includes(i)) cls += ' selected';

        return `<div class="${cls}" onclick="${slot.status==='available' ? `selectSlot(${i})` : ''}">
                    ${slot.time}
                </div>`;
    }).join('');
}

function updateBookingButton() {
    const btn = document.getElementById('btn-book');
    const info = document.getElementById('selected-info');

    if (!btn || !info) return;

    if (selectedSlots.length === 0) {
        btn.disabled = true;
        info.textContent = 'Select a time slot';
    } else {
        btn.disabled = false;
        info.textContent = 'Selected: ' + selectedSlots.map(i => availableSlots[i].time).join(', ');
    }
}

async function bookAppointment() {
    const dateInput = document.getElementById('date-picker').value;
    if (selectedSlots.length === 0) return alert("Select a time slot first");
    
    // Parse slots from '9:00 AM' to '09:00:00'
    const parsedSlots = selectedSlots.map(i => {
        let timeStr = availableSlots[i].time;
        const [time, ampm] = timeStr.split(' ');
        let [hours, minutes] = time.split(':');
        hours = parseInt(hours, 10);
        if(ampm === 'PM' && hours < 12) hours += 12;
        if(ampm === 'AM' && hours === 12) hours = 0;
        return `${String(hours).padStart(2, '0')}:${minutes}:00`;
    });
    
    try {
        const response = await fetch('../app/controllers/book-appointment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                doctor_id: DOCTOR_ID,
                patient_id: 1, // Simulated logged-in patient
                date: dateInput,
                slots: parsedSlots
            })
        });
        const data = await response.json();
        if(data.success) {
            alert('Appointment booked successfully! Reference ID: ' + data.appointment.reference_id);
            selectedSlots = [];
            renderSlotsWithSelection();
            updateBookingButton();
        } else {
            alert('Error booking: ' + data.message);
        }
    } catch (e) {
        alert('Server error booking appointment.');
    }
}

// Theme Toggle Logic
