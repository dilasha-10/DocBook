<div class="view-container">

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-container">

            <div class="profile-photo">
                <i class="fas fa-user"></i>
            </div>

            <div class="profile-info">
                <h1 class="profile-name" id="doctor-name">Dr. Sarah Lim</h1>
                <p class="profile-specialty">General Practitioner</p>

                <div class="profile-stats">
                    <div class="profile-stat">
                        <i class="fas fa-star"></i>
                        <span>4.8 (124 reviews)</span>
                    </div>

                    <div class="profile-stat">
                        <i class="fas fa-briefcase"></i>
                        <span>8 years exp.</span>
                    </div>
                </div>

                <div class="profile-fee">
                    $<span id="doctor-fee">75</span> per consultation
                </div>

                <p class="profile-bio truncated" id="doctor-bio">
                    Experienced general practitioner with a focus on preventive care and patient education.
                    Specializes in managing chronic conditions and preventive health screenings.
                </p>

                <button class="btn-read-more" onclick="toggleBio()">Read More</button>
            </div>
        </div>
    </div>

    <!-- Booking Section -->
    <div class="booking-section">

        <div class="date-picker-container">
            <label>Select Date</label>
            <input type="date" class="date-picker" id="date-picker" onchange="loadSlots()">
        </div>

        <div class="slots-container">
            <h3>Available Time Slots</h3>
            <div class="slots-grid" id="slots-grid"></div>
        </div>

        <div class="booking-actions">
            <p class="selected-slots-info" id="selected-info">Select a time slot</p>
            <button class="btn-book" id="btn-book" disabled onclick="bookAppointment()">
                Book Appointment
            </button>
        </div>

    </div>

</div>

<!-- Toast -->
<div class="toast-container" id="toast-container"></div>