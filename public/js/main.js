// ══════════════════════════════════════════
// DocBook — Shared JS utilities
// Pure vanilla JS, no frameworks
// ══════════════════════════════════════════

// ── Show toast notification
// Usage: showToast('Done', 'success')
// Types: success | error | info
function showToast(message, type = "info") {
  let toast = document.getElementById("docbook-toast");

  if (!toast) {
    toast = document.createElement("div");
    toast.id = "docbook-toast";
    toast.className = "toast";
    document.body.appendChild(toast);
  }

  toast.textContent = message;
  toast.className = "toast " + type;

  requestAnimationFrame(() => {
    toast.classList.add("show");
  });

  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
}

// ── Debounce
function debounce(fn, delay) {
  let timer;
  return function (...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// ── Format date
// '2025-03-25' → 'March 25, 2025'
function formatDate(str) {
  return new Date(str).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

// ── Format time
// '10:00:00' → '10:00 AM'
function formatTime(str) {
  const [h, m] = str.split(":");
  const hour = parseInt(h);
  const ampm = hour >= 12 ? "PM" : "AM";
  const h12 = hour % 12 || 12;
  return `${h12}:${m} ${ampm}`;
}

// ── API call helper
async function apiCall(url, method = "GET", body = null) {
  const opts = {
    method,
    headers: { "Content-Type": "application/json" },
  };

  if (body) opts.body = JSON.stringify(body);

  const res = await fetch(url, opts);
  const data = await res.json();

  if (!res.ok) {
    throw {
      status: res.status,
      message: data.message || "Something went wrong",
    };
  }

  return data;
}

// ══════════════════════════════════════════
// Categories page — AJAX search + filter
// ══════════════════════════════════════════
(function () {
  const grid = document.getElementById("doctors-grid");
  const searchInput = document.getElementById("doctor-search");
  const searchBtn = document.getElementById("search-btn");
  const categoryBtns = document.querySelectorAll("[data-category]");

  if (!grid || !searchInput) return; // not on categories page

  let activeCategory = "all";

  // ── Build a doctor card from API data
  function buildCard(doctor) {
    const initials = doctor.name.substring(4, 6).toUpperCase();
    const availBadge = doctor.next_available_date
      ? `<span class="badge badge-confirmed">Available</span>`
      : `<span class="badge badge-cancelled">Unavailable</span>`;

    const action = doctor.next_available_date
      ? `<a href="/doctors/${doctor.id}" class="btn-primary" style="padding:7px 18px;font-size:13px;">Book</a>`
      : `<button class="btn-secondary" disabled style="padding:7px 18px;font-size:13px;opacity:0.4;cursor:not-allowed;">Unavailable</button>`;

    return `
      <div class="card" style="display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;gap:14px;">
          <div class="avatar-circle" style="width:48px;height:48px;font-size:14px;flex-shrink:0;">
            ${initials}
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:15px;">${doctor.name}</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">${doctor.category_name}</div>
          </div>
          ${availBadge}
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid var(--border);">
          <div style="display:flex;gap:16px;">
            <span style="font-size:13px;color:var(--muted);"><i class="fa fa-star" style="color:var(--yellow);"></i> ${doctor.avg_rating ?? "—"}</span>
            <span style="font-size:13px;color:var(--muted);">NPR ${parseFloat(doctor.fee).toFixed(2)}</span>
          </div>
          ${action}
        </div>
      </div>`;
  }

  // ── Fetch doctors from API and re-render grid
  function fetchDoctors() {
    const params = new URLSearchParams();
    if (activeCategory && activeCategory !== "all")
      params.set("category", activeCategory);
    const q = searchInput.value.trim();
    if (q) params.set("search", q);

    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;color:var(--muted);padding:40px 0;">Loading…</div>`;

    fetch("/api/doctors?" + params.toString())
      .then((r) => r.json())
      .then((res) => {
        const doctors = res.data || [];
        if (doctors.length === 0) {
          grid.innerHTML = `
            <div class="empty-state" style="grid-column:1/-1;">
              <i class="fa fa-user-doctor"></i>
              <p>No doctors found.</p>
            </div>`;
          return;
        }
        grid.innerHTML = doctors.map(buildCard).join("");
      })
      .catch(() => {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;color:var(--muted);">Failed to load doctors.</div>`;
      });
  }

  // ── Category pill clicks
  categoryBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      activeCategory = btn.dataset.category;

      // Update active styles
      categoryBtns.forEach((b) => {
        const isActive = b === btn;
        b.style.borderColor = isActive ? "var(--blue)" : "var(--border2)";
        b.style.background = isActive ? "rgba(74,158,255,0.12)" : "transparent";
        b.style.color = isActive ? "var(--blue)" : "var(--muted)";
      });

      fetchDoctors();
    });
  });

  // ── Search input — debounced
  searchInput.addEventListener("input", debounce(fetchDoctors, 350));

  // ── Search button click
  if (searchBtn) searchBtn.addEventListener("click", fetchDoctors);

  // ── Initial load via AJAX
  fetchDoctors();
})();
