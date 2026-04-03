<?php require_once '../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Doctors • DocBook</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .category-scroll { overflow-x: auto; white-space: nowrap; padding: 10px 0; scrollbar-width: none; }
        .category-scroll::-webkit-scrollbar { display: none; }
        .cat-card { display: inline-block; margin-right: 12px; min-width: 120px; text-align: center; }
        .doctor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1 style="margin-bottom:20px;">Find Doctors</h1>

    <!-- Search Bar -->
    <input type="text" id="searchInput" class="input" placeholder="Search doctors by name..." style="margin-bottom:24px;">

    <!-- Categories -->
    <div id="categories" class="category-scroll"></div>

    <!-- Doctors -->
    <div id="doctors" class="doctor-grid"></div>
</div>

<script src="../assets/js/main.js"></script>
<script>
// Load categories and doctors
let currentCategory = 'all';

async function loadCategories() {
    try {
        const res = await fetch('../api/doctors.php?type=categories');
        const categories = await res.json();
        const container = document.getElementById('categories');
        container.innerHTML = `<div class="cat-card card" onclick="filterByCategory('all')" style="padding:12px 20px;">All</div>`;
        
        categories.forEach(cat => {
            const div = document.createElement('div');
            div.className = `cat-card card ${cat.slug === currentCategory ? 'selected' : ''}`;
            div.style.padding = '12px 20px';
            div.innerHTML = `<strong>${cat.name}</strong>`;
            div.onclick = () => filterByCategory(cat.slug);
            container.appendChild(div);
        });
    } catch(e) { console.error(e); }
}

async function loadDoctors(category = 'all', search = '') {
    const loadingHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--muted);">Loading doctors...</p>';
    document.getElementById('doctors').innerHTML = loadingHTML;

    try {
        let url = `../api/doctors.php?category=${category}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        
        const res = await fetch(url);
        const doctors = await res.json();

        const container = document.getElementById('doctors');
        container.innerHTML = '';

        if (doctors.length === 0) {
            container.innerHTML = `<p style="grid-column:1/-1;text-align:center;color:var(--muted);">No doctors found.</p>`;
            return;
        }

        doctors.forEach(doc => {
            const card = document.createElement('div');
            card.className = 'card';
            card.style.cursor = 'pointer';
            card.innerHTML = `
                <img src="${doc.avatar}" style="width:80px;height:80px;border-radius:50%;margin-bottom:12px;">
                <h3>${doc.name}</h3>
                <p style="color:var(--muted);">${doc.specialty}</p>
                <p>⭐ ${doc.rating} • ₹${doc.fee}</p>
                <button class="btn" style="margin-top:12px;width:auto;padding:10px 20px;" onclick="bookAppointment(${doc.id});event.stopImmediatePropagation();">Book Appointment</button>
            `;
            card.onclick = () => window.location.href = `doctor-profile.php?id=${doc.id}`; // you can create later
            container.appendChild(card);
        });
    } catch(e) {
        document.getElementById('doctors').innerHTML = '<p style="color:red;">Failed to load doctors.</p>';
    }
}

function filterByCategory(slug) {
    currentCategory = slug;
    loadDoctors(slug, document.getElementById('searchInput').value);
}

function bookAppointment(id) {
    alert(`Booking appointment for doctor ID: ${id}\n(This will open booking flow later)`);
}

// Search with 300ms debounce
const debouncedSearch = debounce((value) => {
    loadDoctors(currentCategory, value);
}, 300);

document.getElementById('searchInput').addEventListener('input', (e) => {
    debouncedSearch(e.target.value);
});

// Initial load
loadCategories();
loadDoctors();
</script>
</body>
</html>