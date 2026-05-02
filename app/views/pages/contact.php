<?php
$title = 'Contact Us';
ob_start();
?>

<style>
/* Contact page - fills viewport, no scroll */
.contact-page-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 64px);
    overflow: hidden;
    padding: 0;
    /* Pull back main-wrap's padding so we own the full area */
    margin: -32px -36px;
}
.contact-inner {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 32px;
    overflow: hidden;
    gap: 20px;
    width: 100%;
    box-sizing: border-box;
}
.contact-heading {
    text-align: center;
    flex-shrink: 0;
}
.contact-heading h1 {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 800;
    color: var(--text);
    margin-bottom: 6px;
    text-align: center;
}
.contact-heading p {
    font-size: 14.5px;
    color: var(--muted);
    max-width: 480px;
    margin: 0 auto;
    line-height: 1.6;
}
.contact-body-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    width: 100%;
    align-items: start;
}
/* Compact the form card */
.contact-form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 24px 28px;
    box-shadow: var(--shadow-sm);
}
.contact-form-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 16px;
}
.contact-form-body { display: flex; flex-direction: column; gap: 12px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-label { font-size: 13px; font-weight: 600; color: var(--text); }
.required { color: var(--red); }
.form-input {
    padding: 9px 12px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    font-family: var(--font);
    font-size: 14px;
    color: var(--text);
    background: var(--bg);
    transition: border-color 0.18s, box-shadow 0.18s;
    outline: none;
    width: 100%;
}
.form-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(42,143,168,0.10); }
.form-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%238fa3b2' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 32px;
}
.form-textarea { resize: vertical; min-height: 90px; }
.contact-submit-btn {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 14px; padding: 9px 22px;
    align-self: flex-start;
}
.contact-alert {
    border-radius: var(--radius); padding: 10px 14px;
    font-size: 13px; font-weight: 600; margin-bottom: 4px;
    display: none;
}
.contact-alert--success { background: rgba(46,158,110,0.09); border: 1px solid rgba(46,158,110,0.22); color: #1a6644; display: block; }
.contact-alert--error   { background: rgba(201,64,64,0.07);  border: 1px solid rgba(201,64,64,0.22); color: #8c2222; display: block; }

/* Info sidebar */
.contact-info-col { display: flex; flex-direction: column; gap: 10px; }
.contact-info-card {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 12px 14px;
}
.contact-info-icon {
    width: 36px; height: 36px; border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.contact-info-title { font-weight: 700; font-size: 11px; color: var(--muted); margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px; }
.contact-info-value { font-size: 13.5px; color: var(--text); line-height: 1.5; }
.contact-info-value a { color: var(--blue); }
.contact-faq-hint {
    display: flex; gap: 10px; align-items: flex-start;
    background: var(--blue-light); border: 1px solid rgba(42,143,168,0.18);
    border-radius: var(--radius-lg); padding: 12px 14px;
    font-size: 13px; color: var(--text);
}
.contact-faq-hint > i { color: var(--blue); font-size: 18px; flex-shrink: 0; margin-top: 1px; }

@media (max-width: 820px) {
    .contact-page-wrap { height: auto; overflow: visible; margin: -20px -16px; }
    .contact-inner     { padding: 20px 16px; justify-content: flex-start; overflow-y: auto; }
    .contact-body-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="contact-page-wrap">
    <div class="contact-inner">

        <!-- Heading -->
        <div class="contact-heading">
            <h1>We'd Love to Hear From You</h1>
            <p>Whether you have a question, suggestion, or need help, our team is here to assist.</p>
        </div>

        <!-- Grid -->
        <div class="contact-body-grid">

            <!-- Form -->
            <div class="contact-form-card">
                <h2 class="contact-form-title">Send Us a Message</h2>
                <div id="contactAlert" class="contact-alert"></div>
                <div class="contact-form-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contactName">Full Name <span class="required">*</span></label>
                            <input class="form-input" type="text" id="contactName" placeholder="e.g. Ramesh Shrestha" />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contactEmail">Email Address <span class="required">*</span></label>
                            <input class="form-input" type="email" id="contactEmail" placeholder="you@example.com" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contactSubject">Subject <span class="required">*</span></label>
                        <select class="form-input form-select" id="contactSubject">
                            <option value="">Select a topic…</option>
                            <option value="account">Account / Login Issue</option>
                            <option value="booking">Booking Help</option>
                            <option value="doctor">Doctor Related Query</option>
                            <option value="billing">Billing / Payments</option>
                            <option value="feedback">Feedback / Suggestions</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contactMessage">Message <span class="required">*</span></label>
                        <textarea class="form-input form-textarea" id="contactMessage" rows="4" placeholder="Describe your issue or question in detail…"></textarea>
                    </div>
                    <button class="btn-primary contact-submit-btn" id="contactSubmitBtn" type="button">
                        <i class="fa fa-envelope-open-text"></i> Send Message
                    </button>
                </div>
            </div>

            <!-- Info sidebar -->
            <div class="contact-info-col">
                <div class="contact-info-card">
                    <div class="contact-info-icon" style="background:rgba(42,143,168,0.10);color:var(--blue);">
                        <i class="fa fa-location-dot"></i>
                    </div>
                    <div>
                        <div class="contact-info-title">Our Office</div>
                        <div class="contact-info-value">Kathmandu, Bagmati Province<br>Nepal</div>
                    </div>
                </div>
                <div class="contact-info-card">
                    <div class="contact-info-icon" style="background:rgba(46,158,110,0.10);color:var(--green);">
                        <i class="fa fa-envelope-open-text"></i>
                    </div>
                    <div>
                        <div class="contact-info-title">Email Us</div>
                        <div class="contact-info-value"><a href="mailto:support@docbook.app">support@docbook.app</a></div>
                    </div>
                </div>
                <div class="contact-info-card">
                    <div class="contact-info-icon" style="background:rgba(196,138,26,0.10);color:var(--yellow);">
                        <i class="fa fa-phone"></i>
                    </div>
                    <div>
                        <div class="contact-info-title">Call Us</div>
                        <div class="contact-info-value">+977-1-4XXXXXX<br>
                            <span style="font-size:12px;color:var(--muted);">Mon – Fri, 9 AM – 6 PM NST</span>
                        </div>
                    </div>
                </div>
                <div class="contact-info-card">
                    <div class="contact-info-icon" style="background:rgba(201,64,64,0.10);color:var(--red);">
                        <i class="fa fa-clock"></i>
                    </div>
                    <div>
                        <div class="contact-info-title">Response Time</div>
                        <div class="contact-info-value">Within 24 hours on business days</div>
                    </div>
                </div>
                <div class="contact-faq-hint">
                    <i class="fa fa-circle-question"></i>
                    <div>
                        <strong>Looking for quick answers?</strong><br>
                        <span style="color:var(--muted);font-size:12px;">Check out your dashboard for appointment management, or visit <a href="<?= BASE_URL ?>/categories">Find Doctors</a> to browse specialists.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var btn   = document.getElementById('contactSubmitBtn');
    var alertEl = document.getElementById('contactAlert');

    btn.addEventListener('click', function () {
        var name    = document.getElementById('contactName').value.trim();
        var email   = document.getElementById('contactEmail').value.trim();
        var subject = document.getElementById('contactSubject').value;
        var message = document.getElementById('contactMessage').value.trim();

        if (!name || !email || !subject || !message) {
            showAlert('Please fill in all required fields.', 'error'); return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showAlert('Please enter a valid email address.', 'error'); return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending…';
        setTimeout(function () {
            showAlert('Thank you! Your message has been sent. We\'ll get back to you within 24 hours.', 'success');
            document.getElementById('contactName').value    = '';
            document.getElementById('contactEmail').value   = '';
            document.getElementById('contactSubject').value = '';
            document.getElementById('contactMessage').value = '';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-envelope-open-text"></i> Send Message';
        }, 1200);
    });

    function showAlert(msg, type) {
        alertEl.textContent = msg;
        alertEl.className   = 'contact-alert contact-alert--' + type;
    }
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';