<?php
$title = 'My Profile';
ob_start();
?>

<div class="view-container">

    <!-- View Mode -->
    <div id="profile-view-mode" class="profile-header">
        <div class="profile-container">
            <div class="profile-photo" id="view-profile-photo">
                <?php if (!empty($doctor['photo'])): ?>
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($doctor['photo']) ?>"
                         alt="Profile"
                         style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <h1 class="profile-name" id="doctor-name">
                            Dr. <?= htmlspecialchars($doctor['name']) ?>
                        </h1>
                        <p class="profile-specialty" id="view-specialty">
                            <?= htmlspecialchars($doctor['specialty'] ?? '') ?>
                        </p>
                        <div class="profile-stats">
                            <div class="profile-stat">
                                <i class="fas fa-briefcase"></i>
                                <span id="view-experience">
                                    <?= (int)($doctor['experience_years'] ?? 0) ?> years exp.
                                </span>
                            </div>
                        </div>
                    </div>
                    <button class="btn-primary btn-small" onclick="enterEditMode()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>

                <p class="profile-bio truncated" id="doctor-bio">
                    <?= htmlspecialchars($doctor['bio'] ?? '') ?>
                </p>
                <button class="btn-read-more" onclick="toggleBio()">Read More</button>

                <div class="profile-stats" style="margin-top:12px;">
                    <div class="profile-stat">
                        <i class="fas fa-envelope"></i>
                        <span id="view-email" style="font-size:13px;">
                            <?= htmlspecialchars($doctor['email'] ?? '—') ?>
                        </span>
                    </div>
                    <div class="profile-stat">
                        <i class="fas fa-phone"></i>
                        <span id="view-phone" style="font-size:13px;">
                            <?= htmlspecialchars($doctor['phone'] ?? '—') ?>
                        </span>
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
                <div class="form-group">
                    <label>Profile Photo</label>
                    <div class="photo-upload">
                        <div class="photo-preview" id="edit-photo-preview">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="file" id="photo-input" name="photo" accept="image/*"
                               onchange="previewPhoto(event)">
                        <label for="photo-input" class="btn-secondary btn-small">
                            <i class="fas fa-camera"></i> Change Photo
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name-input">Full Name *</label>
                    <input type="text" id="name-input" name="name" required
                           value="<?= htmlspecialchars($doctor['name']) ?>">
                </div>

                <div class="form-group">
                    <label for="specialty-input">Specialty *</label>
                    <input type="text" id="specialty-input" name="specialty" required
                           value="<?= htmlspecialchars($doctor['specialty'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email-input">Email</label>
                        <input type="email" id="email-input" name="email"
                               value="<?= htmlspecialchars($doctor['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone-input">Phone</label>
                        <input type="tel" id="phone-input" name="phone"
                               value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="experience-input">Years of Experience</label>
                        <input type="number" id="experience-input" name="experience_years" min="0"
                               value="<?= (int)($doctor['experience_years'] ?? 0) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio-input">Bio / About</label>
                    <textarea id="bio-input" name="bio" rows="5"><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea>
                </div>

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

<div class="toast-container" id="toast-container"></div>

<?php
$extra_scripts = <<<'JS'
<script>
(function(){

    /* ── Enter / Exit edit mode ── */
    window.enterEditMode = function() {
        document.getElementById('profile-view-mode').style.display = 'none';
        document.getElementById('profile-edit-mode').classList.remove('hidden');

        // Pre-fill photo preview if there is one
        var viewImg = document.querySelector('#view-profile-photo img');
        var preview = document.getElementById('edit-photo-preview');
        if (viewImg && preview) {
            preview.innerHTML = '<img src="' + viewImg.src + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
        }
    };

    window.exitEditMode = function() {
        document.getElementById('profile-edit-mode').classList.add('hidden');
        document.getElementById('profile-view-mode').style.display = '';
    };

    /* ── Live photo preview ── */
    window.previewPhoto = function(event) {
        var file = event.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('edit-photo-preview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
        };
        reader.readAsDataURL(file);
    };

    /* ── Bio toggle ── */
    window.toggleBio = function() {
        var bio = document.getElementById('doctor-bio');
        var btn = document.querySelector('.btn-read-more');
        if (!bio) return;
        if (bio.classList.contains('truncated')) {
            bio.classList.remove('truncated');
            if (btn) btn.textContent = 'Show Less';
        } else {
            bio.classList.add('truncated');
            if (btn) btn.textContent = 'Read More';
        }
    };

    /* ── Profile form submit ── */
    var form = document.getElementById('profile-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            var saveBtn = form.querySelector('[type="submit"]');
            saveBtn.disabled = true;
            var origText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

            var formData = new FormData(form);

            try {
                var res  = await fetch(BASE_URL + '/doctor/api/profile', {
                    method: 'POST',
                    body: formData
                });
                var data = await res.json();

                if (data.success) {
                    var doc = data.doctor;

                    /* Update view mode fields */
                    var nameEl = document.getElementById('doctor-name');
                    if (nameEl) nameEl.textContent = 'Dr. ' + doc.name;

                    var specEl = document.getElementById('view-specialty');
                    if (specEl) specEl.textContent = doc.specialty || '';

                    var expEl = document.getElementById('view-experience');
                    if (expEl) expEl.textContent = (doc.experience_years || 0) + ' years exp.';

                    var emailEl = document.getElementById('view-email');
                    if (emailEl) emailEl.textContent = doc.email || '—';

                    var phoneEl = document.getElementById('view-phone');
                    if (phoneEl) phoneEl.textContent = doc.phone || '—';

                    var bioEl = document.getElementById('doctor-bio');
                    if (bioEl) bioEl.textContent = doc.bio || '';

                    /* Update photo in view mode if uploaded */
                    if (doc.photo) {
                        var photoBox = document.getElementById('view-profile-photo');
                        if (photoBox) {
                            photoBox.innerHTML = '<img src="' + BASE_URL + '/' + doc.photo + '" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                        }
                    }

                    showToast('Profile updated!', 'success');
                    exitEditMode();
                } else {
                    showToast(data.message || 'Failed to update profile.', 'error');
                }
            } catch(err) {
                showToast('Network error. Please try again.', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = origText;
            }
        });
    }

})();
</script>
JS;

$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app-doctor.php';