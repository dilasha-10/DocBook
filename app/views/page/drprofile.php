<div class="view-container">

    <!-- View Mode -->
    <div id="profile-view-mode" class="profile-header">
        <div class="profile-container">

            <div class="profile-photo" id="view-profile-photo">
                <i class="fas fa-user"></i>
            </div>

            <div class="profile-info">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h1 class="profile-name" id="doctor-name">Dr. Sarah Lim</h1>
                        <p class="profile-specialty" id="view-specialty">General Practitioner</p>

                        <div class="profile-stats">
                            <div class="profile-stat">
                                <i class="fas fa-star"></i>
                                <span id="view-rating">4.8 (124 reviews)</span>
                            </div>

                            <div class="profile-stat">
                                <i class="fas fa-briefcase"></i>
                                <span id="view-experience">8 years exp.</span>
                            </div>
                        </div>
                    </div>
                    <button class="btn-primary btn-small" onclick="enterEditMode()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>

                <p class="profile-bio truncated" id="doctor-bio">
                    Experienced general practitioner with a focus on preventive care and patient education.
                    Specializes in managing chronic conditions and preventive health screenings.
                </p>

                <button class="btn-read-more" onclick="toggleBio()">Read More</button>

                <!-- Contact info -->
                <div class="profile-stats" style="margin-top: 12px;">
                    <div class="profile-stat">
                        <i class="fas fa-envelope"></i>
                        <span id="view-email" style="font-size:13px;">—</span>
                    </div>
                    <div class="profile-stat">
                        <i class="fas fa-phone"></i>
                        <span id="view-phone" style="font-size:13px;">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Mode -->
    <div id="profile-edit-mode" class="profile-edit-container hidden">
        <div class="profile-form-section">
            <div class="form-header">
                <h2>Edit Profile</h2>
                <button class="btn-close" onclick="exitEditMode()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="profile-form" class="profile-form">
                <!-- Photo Upload -->
                <div class="form-group">
                    <label>Profile Photo</label>
                    <div class="photo-upload">
                        <div class="photo-preview" id="edit-photo-preview">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="file" id="photo-input" name="photo" accept="image/*" onchange="previewPhoto(event)">
                        <label for="photo-input" class="btn-secondary btn-small">
                            <i class="fas fa-camera"></i> Change Photo
                        </label>
                    </div>
                </div>

                <!-- Name -->
                <div class="form-group">
                    <label for="name-input">Full Name *</label>
                    <input type="text" id="name-input" name="name" required>
                </div>

                <!-- Specialty -->
                <div class="form-group">
                    <label for="specialty-input">Specialty *</label>
                    <input type="text" id="specialty-input" name="specialty" required>
                </div>

                <!-- Email & Phone -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email-input">Email</label>
                        <input type="email" id="email-input" name="email">
                    </div>
                    <div class="form-group">
                        <label for="phone-input">Phone</label>
                        <input type="tel" id="phone-input" name="phone">
                    </div>
                </div>

                <!-- Years of Experience -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="experience-input">Years of Experience</label>
                        <input type="number" id="experience-input" name="experience_years" min="0">
                    </div>
                </div>

                <!-- Bio -->
                <div class="form-group">
                    <label for="bio-input">Bio / About</label>
                    <textarea id="bio-input" name="bio" rows="5"></textarea>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="exitEditMode()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Toast -->
<div class="toast-container" id="toast-container"></div>