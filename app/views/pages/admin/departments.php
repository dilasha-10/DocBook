<?php
$title = 'Manage Departments';
ob_start();
$extra_styles = <<<CSS
<style>
.dept-wrap { padding: 30px 24px 60px; max-width: 1100px; margin: 0 auto; }
.page-header { margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.page-title { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

.btn-primary { background: var(--blue); color: #fff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: opacity .15s; display: inline-flex; align-items: center; gap: 6px; }
.btn-primary:hover { opacity: .85; }
.btn-sm { padding: 6px 14px; font-size: 12px; border-radius: 8px; }
.btn-danger { background: #ef4444; color: #fff; border: none; border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-danger:hover { background: #dc2626; }
.btn-outline { background: transparent; color: var(--blue); border: 1.5px solid var(--blue); border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-outline:hover { background: color-mix(in srgb, var(--blue) 8%, transparent); }

.dept-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; }
.dept-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 22px; transition: border-color .15s, box-shadow .15s; }
.dept-card:hover { border-color: var(--blue); box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 8%, transparent); }
.dept-card-header { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
.dept-card-icon { width: 44px; height: 44px; border-radius: 12px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.dept-card-title { font-size: 16px; font-weight: 800; color: var(--text); }
.dept-card-slug { font-size: 11px; color: var(--muted); margin-top: 2px; }
.dept-card-desc { font-size: 13px; color: var(--muted); line-height: 1.5; margin-bottom: 14px; min-height: 20px; }
.dept-card-meta { display: flex; align-items: center; gap: 16px; font-size: 12px; color: var(--muted); margin-bottom: 14px; }
.dept-card-actions { display: flex; gap: 8px; }

.spec-list { margin-top: 12px; border-top: 1px solid var(--border); padding-top: 12px; }
.spec-list-title { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
.spec-tag { display: inline-flex; align-items: center; gap: 4px; background: var(--hover-bg, rgba(0,0,0,.04)); padding: 4px 10px; border-radius: 6px; font-size: 12px; color: var(--text); margin: 0 4px 4px 0; }
.spec-tag .remove-spec { background: none; border: none; cursor: pointer; color: var(--muted); font-size: 11px; padding: 0 2px; }
.spec-tag .remove-spec:hover { color: #ef4444; }
.add-spec-row { display: flex; gap: 6px; margin-top: 8px; }
.add-spec-row input { flex: 1; padding: 6px 10px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 12px; font-family: inherit; background: var(--surface); color: var(--text); }
.add-spec-row input:focus { outline: none; border-color: var(--blue); }
.badge-inactive { background: #fecaca; color: #991b1b; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 6px; }
.badge-active { background: #dcfce7; color: #166534; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 6px; }

/* Modal */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal-box { background: var(--surface); border-radius: 16px; padding: 28px; width: 90%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.modal-title { font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 18px; }
.modal-field { margin-bottom: 14px; }
.modal-field label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
.modal-field input, .modal-field textarea, .modal-field select { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; font-family: inherit; background: var(--surface); color: var(--text); }
.modal-field input:focus, .modal-field textarea:focus, .modal-field select:focus { outline: none; border-color: var(--blue); }
.modal-field textarea { min-height: 80px; resize: vertical; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.toast { position: fixed; bottom: 24px; right: 24px; background: #166534; color: #fff; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; z-index: 99999; display: none; box-shadow: 0 4px 20px rgba(0,0,0,.15); }
.toast.error { background: #991b1b; }
.toast.show { display: block; animation: slidein .3s ease; }
@keyframes slidein { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state i { font-size: 40px; margin-bottom: 12px; display: block; opacity: .3; }
.empty-state p { font-size: 14px; }
.loading-spinner { text-align: center; padding: 40px; color: var(--muted); }
</style>
CSS;
$content = ob_get_clean();
ob_start();
?>

<div class="dept-wrap">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fa fa-building" style="color:var(--blue);margin-right:8px;"></i>Departments & Specializations</h1>
            <p class="page-subtitle">Manage hospital departments and their specializations</p>
        </div>
        <button class="btn-primary" onclick="openCreateModal()"><i class="fa fa-plus"></i> Add Department</button>
    </div>

    <div id="deptLoading" class="loading-spinner"><i class="fa fa-spinner fa-spin"></i> Loading departments...</div>
    <div id="deptGrid" class="dept-grid" style="display:none;"></div>
    <div id="deptEmpty" class="empty-state" style="display:none;">
        <i class="fa fa-building"></i>
        <p>No departments yet. Click "Add Department" to create one.</p>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal-overlay" id="deptModal">
    <div class="modal-box">
        <h2 class="modal-title" id="modalTitle">Add Department</h2>
        <input type="hidden" id="editId">
        <div class="modal-field">
            <label for="deptName">Department Name</label>
            <input type="text" id="deptName" placeholder="e.g. Cardiology">
        </div>
        <div class="modal-field">
            <label for="deptIcon">Icon (FontAwesome class)</label>
            <input type="text" id="deptIcon" placeholder="e.g. fa-heart-pulse" value="fa-stethoscope">
        </div>
        <div class="modal-field">
            <label for="deptDesc">Description</label>
            <textarea id="deptDesc" placeholder="Brief description of the department..."></textarea>
        </div>
        <div class="modal-field" id="activeField" style="display:none;">
            <label>
                <input type="checkbox" id="deptActive" checked> Active
            </label>
        </div>
        <div class="modal-actions">
            <button class="btn-outline" onclick="closeModal()">Cancel</button>
            <button class="btn-primary" id="saveBtn" onclick="saveDepartment()">Save</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
var departments = [];

function toast(msg, isError) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (isError ? ' error' : '');
    setTimeout(function(){ t.className = 'toast'; }, 3000);
}

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function loadDepartments() {
    fetch(BASE_URL + '/admin/api/departments')
        .then(function(r){ return r.json(); })
        .then(function(d) {
            departments = d.departments || [];
            renderDepartments();
        })
        .catch(function(){ toast('Failed to load departments', true); });
}

function renderDepartments() {
    var grid = document.getElementById('deptGrid');
    var loading = document.getElementById('deptLoading');
    var empty = document.getElementById('deptEmpty');
    loading.style.display = 'none';

    if (!departments.length) {
        grid.style.display = 'none';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    grid.style.display = 'grid';
    grid.innerHTML = departments.map(function(dept) {
        var statusBadge = dept.is_active == 1
            ? '<span class="badge-active">Active</span>'
            : '<span class="badge-inactive">Inactive</span>';

        return '<div class="dept-card" id="dept-' + dept.id + '">' +
            '<div class="dept-card-header">' +
                '<div class="dept-card-icon"><i class="fa ' + escHtml(dept.icon || 'fa-stethoscope') + '"></i></div>' +
                '<div><div class="dept-card-title">' + escHtml(dept.name) + '</div>' +
                '<div class="dept-card-slug">' + escHtml(dept.slug) + '</div></div>' +
            '</div>' +
            '<div class="dept-card-desc">' + escHtml(dept.description || 'No description') + '</div>' +
            '<div class="dept-card-meta">' +
                statusBadge +
                '<span><i class="fa fa-tags"></i> ' + (dept.specialization_count || 0) + ' specializations</span>' +
            '</div>' +
            '<div class="dept-card-actions">' +
                '<button class="btn-outline btn-sm" onclick="openEditModal(' + dept.id + ')"><i class="fa fa-pen"></i> Edit</button>' +
                '<button class="btn-danger btn-sm" onclick="deleteDept(' + dept.id + ')"><i class="fa fa-trash"></i></button>' +
            '</div>' +
            '<div class="spec-list" id="specList-' + dept.id + '">' +
                '<div class="spec-list-title">Specializations</div>' +
                '<div id="specTags-' + dept.id + '"><i class="fa fa-spinner fa-spin"></i></div>' +
                '<div class="add-spec-row">' +
                    '<input type="text" id="specInput-' + dept.id + '" placeholder="Add specialization...">' +
                    '<button class="btn-primary btn-sm" onclick="addSpec(' + dept.id + ')"><i class="fa fa-plus"></i></button>' +
                '</div>' +
            '</div>' +
        '</div>';
    }).join('');

    // Load specializations for each department
    departments.forEach(function(dept) { loadSpecs(dept.id); });
}

function loadSpecs(deptId) {
    fetch(BASE_URL + '/admin/api/specializations?department_id=' + deptId)
        .then(function(r){ return r.json(); })
        .then(function(d) {
            var container = document.getElementById('specTags-' + deptId);
            var specs = d.specializations || [];
            if (!specs.length) {
                container.innerHTML = '<span style="font-size:12px;color:var(--muted);">No specializations yet</span>';
                return;
            }
            container.innerHTML = specs.map(function(s) {
                return '<span class="spec-tag">' + escHtml(s.name) +
                    ' <button class="remove-spec" onclick="deleteSpec(' + s.id + ',' + deptId + ')" title="Remove">×</button></span>';
            }).join('');
        });
}

function addSpec(deptId) {
    var input = document.getElementById('specInput-' + deptId);
    var name = input.value.trim();
    if (!name) return;

    fetch(BASE_URL + '/admin/api/specializations', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ category_id: deptId, name: name })
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success) {
            input.value = '';
            loadSpecs(deptId);
            toast('Specialization added');
        } else {
            toast(d.error || 'Failed', true);
        }
    });
}

function deleteSpec(specId, deptId) {
    if (!confirm('Remove this specialization?')) return;
    fetch(BASE_URL + '/admin/api/specializations/' + specId, { method: 'DELETE' })
        .then(function(r){ return r.json(); })
        .then(function(d) {
            if (d.success) { loadSpecs(deptId); toast('Specialization removed'); }
            else toast(d.error || 'Failed', true);
        });
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add Department';
    document.getElementById('editId').value = '';
    document.getElementById('deptName').value = '';
    document.getElementById('deptIcon').value = 'fa-stethoscope';
    document.getElementById('deptDesc').value = '';
    document.getElementById('activeField').style.display = 'none';
    document.getElementById('deptModal').classList.add('open');
}

function openEditModal(id) {
    var dept = departments.find(function(d){ return d.id == id; });
    if (!dept) return;
    document.getElementById('modalTitle').textContent = 'Edit Department';
    document.getElementById('editId').value = dept.id;
    document.getElementById('deptName').value = dept.name;
    document.getElementById('deptIcon').value = dept.icon || 'fa-stethoscope';
    document.getElementById('deptDesc').value = dept.description || '';
    document.getElementById('deptActive').checked = dept.is_active == 1;
    document.getElementById('activeField').style.display = 'block';
    document.getElementById('deptModal').classList.add('open');
}

function closeModal() { document.getElementById('deptModal').classList.remove('open'); }

function saveDepartment() {
    var id   = document.getElementById('editId').value;
    var name = document.getElementById('deptName').value.trim();
    var icon = document.getElementById('deptIcon').value.trim();
    var desc = document.getElementById('deptDesc').value.trim();
    var active = document.getElementById('deptActive').checked;

    if (!name) { toast('Name is required', true); return; }

    var url = id
        ? BASE_URL + '/admin/api/departments/' + id
        : BASE_URL + '/admin/api/departments';
    var method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ name: name, icon: icon, description: desc, is_active: active })
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success) {
            closeModal();
            loadDepartments();
            toast(id ? 'Department updated' : 'Department created');
        } else {
            toast(d.error || 'Failed to save', true);
        }
    });
}

function deleteDept(id) {
    if (!confirm('Delete this department? This cannot be undone.')) return;
    fetch(BASE_URL + '/admin/api/departments/' + id, { method: 'DELETE' })
        .then(function(r){ return r.json(); })
        .then(function(d) {
            if (d.success) { loadDepartments(); toast('Department deleted'); }
            else toast(d.error || 'Cannot delete', true);
        });
}

loadDepartments();
</script>

<?php
$content = ob_get_clean();
<<<<<<< HEAD
include BASE_PATH . '/app/views/layouts/app.php';
=======
include BASE_PATH . '/app/views/layouts/app.php';
>>>>>>> 5353f4c (Final complete work)
