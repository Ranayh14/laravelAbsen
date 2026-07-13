<div id="page-members" class="hidden animate-fade-in-up">
    <!-- Sub-tab navigation -->
    <div class="flex items-center gap-2 mb-6">
        <button id="subtab-daftar" class="member-subtab active-subtab px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2">
            <i class="fi fi-sr-users"></i> Daftar Member
        </button>
        <button id="subtab-kelompok" class="member-subtab px-5 py-2.5 rounded-xl font-bold text-sm text-gray-500 hover:bg-gray-100 transition-all flex items-center gap-2">
            <i class="fi fi-sr-layers"></i> Kelompok Magang
        </button>
    </div>
    <style>
        .member-subtab { border: 2px solid transparent; }
        .member-subtab.active-subtab { background: #4f46e5; color: white; border-color: #4338ca; box-shadow: 0 4px 12px rgba(79,70,229,0.25); }
        .member-subtab:not(.active-subtab):hover { border-color: #e5e7eb; }
        .group-card { background: white; border-radius: 20px; border: 2px solid #f1f5f9; transition: all 0.2s; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .group-card:hover { border-color: #c7d2fe; box-shadow: 0 4px 16px rgba(79,70,229,0.1); }
        .group-card.archived { background: #fafafa; border-color: #e5e7eb; opacity: 0.9; }
        .badge-active { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-ended { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-archived { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .member-avatar-sm { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid white; margin-left: -8px; box-shadow: 0 1px 3px rgba(0,0,0,0.15); flex-shrink: 0; }
        .member-avatar-sm:first-child { margin-left: 0; }
        .member-avatar-initials { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: white; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid white; margin-left: -8px; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
        .member-avatar-initials:first-child { margin-left: 0; }
        #section-kelompok-magang .section-archived { border-top: 2px dashed #e2e8f0; margin-top: 24px; padding-top: 20px; }
    </style>

    <!-- ===== SECTION DAFTAR MEMBER ===== -->
    <div id="section-daftar-member">
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight flex items-center gap-3">
                    <i class="fi fi-sr-users-alt text-indigo-600"></i>
                    Daftar Member
                </h2>
                <button id="btn-add-member" class="bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fi fi-sr-user-add"></i> Tambah Member
                </button>
            </div>
            
            <div class="relative mb-6">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fi fi-sr-search text-gray-400"></i>
                </div>
                <input type="text" id="search-member" placeholder="Cari member berdasarkan nama atau NIM..." class="w-full pl-10 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
            </div>
            
            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full min-w-max text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                        <tr>
                            <th class="py-4 px-6 font-bold text-gray-800">Foto</th>
                            <th class="py-4 px-6 font-bold text-gray-800">NIM</th>
                            <th class="py-4 px-6 font-bold text-gray-800">Nama</th>
                            <th class="py-4 px-6 font-bold text-gray-800">Program Studi</th>
                            <th class="py-4 px-6 font-bold text-gray-800">Nama Startup</th>
                            <th class="py-4 px-6 font-bold text-gray-800">QR Code GA</th>
                            <th class="py-4 px-6 font-bold text-gray-800 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-members-body" class="bg-white divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== SECTION KELOMPOK MAGANG ===== -->
    <div id="section-kelompok-magang" class="hidden">
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight flex items-center gap-3">
                        <i class="fi fi-sr-layers text-indigo-600"></i>
                        Kelompok Magang
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Kelompokkan pegawai berdasarkan periode magang. Kelompok yang sudah selesai bisa di-archive.</p>
                </div>
                <button id="btn-buat-kelompok" class="bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2 whitespace-nowrap">
                    <i class="fi fi-sr-plus"></i> Buat Kelompok
                </button>
            </div>

            <!-- Kelompok Aktif -->
            <div id="list-kelompok-aktif" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Diisi JS -->
            </div>
            <div id="empty-kelompok" class="hidden text-center py-12 text-gray-400">
                <i class="fi fi-sr-layers text-4xl mb-3 block opacity-40"></i>
                <p class="font-semibold text-gray-500">Belum ada kelompok magang</p>
                <p class="text-sm mt-1">Klik "Buat Kelompok" untuk memulai</p>
            </div>

            <!-- Section Arsip -->
            <div id="section-arsip-wrapper" class="hidden section-archived">
                <button id="toggle-arsip" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 font-semibold text-sm mb-4 transition-colors group">
                    <i class="fi fi-sr-archive text-lg text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
                    <span id="toggle-arsip-label">Tampilkan Arsip</span>
                    <i id="toggle-arsip-icon" class="fi fi-sr-angle-small-down text-gray-400 transition-transform"></i>
                </button>
                <div id="list-kelompok-arsip" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Diisi JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// Sub-tab switching Kelola Member
// ============================================================
(function() {
    function initMemberSubtabs() {
        const btnDaftar = document.getElementById('subtab-daftar');
        const btnKelompok = document.getElementById('subtab-kelompok');
        const secDaftar = document.getElementById('section-daftar-member');
        const secKelompok = document.getElementById('section-kelompok-magang');
        if (!btnDaftar || !btnKelompok) return;

        function activate(tab) {
            [btnDaftar, btnKelompok].forEach(b => b.classList.remove('active-subtab'));
            [secDaftar, secKelompok].forEach(s => s.classList.add('hidden'));
            if (tab === 'daftar') {
                btnDaftar.classList.add('active-subtab');
                secDaftar.classList.remove('hidden');
            } else {
                btnKelompok.classList.add('active-subtab');
                secKelompok.classList.remove('hidden');
                loadInternGroups();
            }
        }

        btnDaftar.addEventListener('click', () => activate('daftar'));
        btnKelompok.addEventListener('click', () => activate('kelompok'));
    }
    document.addEventListener('DOMContentLoaded', initMemberSubtabs);
    // Also call if already loaded
    if (document.readyState !== 'loading') initMemberSubtabs();
})();

// ============================================================
// Intern Groups — State & Helpers
// ============================================================
window._internGroups = [];
window._allMembersForGroup = [];

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
}

function getGroupStatus(group) {
    const today = new Date(); today.setHours(0,0,0,0);
    const end = new Date(group.tanggal_selesai + 'T00:00:00');
    const start = new Date(group.tanggal_mulai + 'T00:00:00');
    if (group.is_archived == 1) return {label:'Archived', cls:'badge-archived', icon:'fi-sr-archive'};
    if (end < today) return {label:'Selesai', cls:'badge-ended', icon:'fi-sr-check-circle'};
    if (start > today) return {label:'Belum Mulai', cls:'badge-ended', icon:'fi-sr-clock'};
    return {label:'Aktif', cls:'badge-active', icon:'fi-sr-play'};
}

function buildAvatarStack(members, maxShow) {
    maxShow = maxShow || 5;
    if (!members || members.length === 0) return '<span class="text-xs text-gray-400">Belum ada anggota</span>';
    let html = '<div class="flex items-center">';
    const show = members.slice(0, maxShow);
    show.forEach(function(m) {
        const initials = (m.nama || '?').split(' ').map(w=>w[0]||'').join('').substring(0,2).toUpperCase();
        if (m.foto_base64 && m.foto_base64.startsWith('data:')) {
            html += '<img src="'+m.foto_base64+'" class="member-avatar-sm" title="'+m.nama+'" alt="'+m.nama+'">';
        } else {
            html += '<div class="member-avatar-initials" title="'+m.nama+'">'+initials+'</div>';
        }
    });
    if (members.length > maxShow) {
        html += '<div class="member-avatar-initials" style="background:#94a3b8;font-size:10px;">+' + (members.length - maxShow) + '</div>';
    }
    html += '</div>';
    return html;
}

function renderGroupCard(group) {
    const status = getGroupStatus(group);
    const isArchived = group.is_archived == 1;
    const members = group.members || [];
    const mc = group.member_count || 0;

    let actionButtons = '';
    if (!isArchived) {
        actionButtons = `
            <button onclick="openEditGroup(${group.id})" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                <i class="fi fi-sr-edit"></i> Edit
            </button>
            <button onclick="openManageMembers(${group.id})" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                <i class="fi fi-sr-users"></i> Anggota
            </button>
            <button onclick="confirmArchiveGroup(${group.id}, '${group.nama.replace(/'/g,"\\'")}')" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                <i class="fi fi-sr-archive"></i> Archive
            </button>
            <button onclick="confirmDeleteGroup(${group.id}, '${group.nama.replace(/'/g,"\\'")}')" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-500 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                <i class="fi fi-sr-trash"></i>
            </button>`;
    } else {
        actionButtons = `
            <a href="/export/kpi-group?group_id=${group.id}" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <i class="fi fi-sr-file-spreadsheet"></i> Download KPI
            </a>
            <button onclick="downloadGroupDatabase(${group.id})" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <i class="fi fi-sr-database"></i> Download DB
            </button>
            <button onclick="confirmUnarchiveGroup(${group.id}, '${group.nama.replace(/'/g,"\\'")}')" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                <i class="fi fi-sr-undo"></i> Unarchive
            </button>`;
    }

    return `
    <div class="group-card p-5 ${isArchived ? 'archived' : ''}" id="group-card-${group.id}">
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full ${status.cls}">
                        <i class="fi ${status.icon}"></i> ${status.label}
                    </span>
                </div>
                <h3 class="text-lg font-bold text-gray-800 leading-tight truncate">${group.nama}</h3>
                <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5">
                    <i class="fi fi-sr-calendar-lines text-gray-400"></i>
                    ${formatDate(group.tanggal_mulai)} – ${formatDate(group.tanggal_selesai)}
                </p>
                ${isArchived && group.archived_at ? '<p class="text-xs text-gray-400 mt-0.5">Diarsipkan: ' + formatDate(group.archived_at.split(' ')[0]) + '</p>' : ''}
            </div>
            <div class="text-right ml-3">
                <div class="text-2xl font-extrabold text-indigo-600">${mc}</div>
                <div class="text-xs text-gray-400 font-medium">Anggota</div>
            </div>
        </div>
        
        <!-- Avatar stack -->
        <div class="mb-4 py-3 border-y border-gray-50">
            ${buildAvatarStack(members, 6)}
            ${mc > 0 ? '<p class="text-xs text-gray-400 mt-1.5">' + members.slice(0,3).map(m=>m.nama.split(' ')[0]).join(', ') + (mc > 3 ? ' dan '+(mc-3)+' lainnya' : '') + '</p>' : ''}
        </div>

        <!-- Action buttons -->
        <div class="flex flex-wrap gap-2">
            ${actionButtons}
        </div>
    </div>`;
}

async function loadInternGroups(silent) {
    const listAktif = document.getElementById('list-kelompok-aktif');
    const listArsip = document.getElementById('list-kelompok-arsip');
    const emptyEl = document.getElementById('empty-kelompok');
    const arsipWrapper = document.getElementById('section-arsip-wrapper');
    if (!listAktif) return;
    if (!silent) {
        listAktif.innerHTML = '<div class="col-span-2 text-center py-8 text-gray-400"><i class="fi fi-sr-spinner animate-spin text-2xl block mb-2"></i>Memuat kelompok...</div>';
    }

    try {
        // Cache-busting: tambah timestamp agar browser tidak cache response
        const res = await fetch('?ajax=get_intern_groups&_t=' + Date.now());
        const data = await res.json();
        if (!data.ok) throw new Error(data.message);
        window._internGroups = data.data || [];
        
        const aktif = window._internGroups.filter(g => !g.is_archived || g.is_archived == 0);
        const arsip = window._internGroups.filter(g => g.is_archived == 1);

        if (aktif.length === 0 && arsip.length === 0) {
            listAktif.innerHTML = '';
            emptyEl && emptyEl.classList.remove('hidden');
        } else {
            emptyEl && emptyEl.classList.add('hidden');
            listAktif.innerHTML = aktif.map(g => renderGroupCard(g)).join('') || '<div class="col-span-2 text-center py-6 text-gray-400 text-sm">Semua kelompok sudah di-archive</div>';
        }

        if (arsip.length > 0) {
            arsipWrapper && arsipWrapper.classList.remove('hidden');
            if (listArsip) listArsip.innerHTML = arsip.map(g => renderGroupCard(g)).join('');
        } else {
            arsipWrapper && arsipWrapper.classList.add('hidden');
        }
    } catch(e) {
        if (!silent) {
            listAktif.innerHTML = '<div class="col-span-2 text-center py-8 text-red-400">Gagal memuat: ' + e.message + '</div>';
        }
    }
}

// Toggle arsip
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('toggle-arsip');
    const list = document.getElementById('list-kelompok-arsip');
    const label = document.getElementById('toggle-arsip-label');
    const icon = document.getElementById('toggle-arsip-icon');
    if (!btn) return;
    btn.addEventListener('click', function() {
        const isHidden = list.classList.contains('hidden');
        list.classList.toggle('hidden', !isHidden);
        label.textContent = isHidden ? 'Sembunyikan Arsip' : 'Tampilkan Arsip';
        icon.style.transform = isHidden ? 'rotate(180deg)' : '';
        // Show as grid when visible
        if (!isHidden) { list.classList.remove('grid'); } else { list.classList.add('grid'); }
    });
});

// Buat kelompok btn
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btn-buat-kelompok');
    if (btn) btn.addEventListener('click', () => openCreateGroup());
});

// ============================================================
// Modal: Buat / Edit Kelompok
// ============================================================
function openCreateGroup() {
    openGroupModal(null);
}
function openEditGroup(groupId) {
    const grp = window._internGroups.find(g => g.id == groupId);
    if (!grp) return;
    openGroupModal(grp);
}
function openGroupModal(group) {
    const modal = document.getElementById('modal-kelompok-form');
    if (!modal) return;
    const isEdit = !!group;
    modal.querySelector('#modal-kelompok-title').textContent = isEdit ? 'Edit Kelompok' : 'Buat Kelompok Magang';
    modal.querySelector('#kf-id').value = isEdit ? group.id : '';
    modal.querySelector('#kf-nama').value = isEdit ? group.nama : '';
    modal.querySelector('#kf-mulai').value = isEdit ? group.tanggal_mulai : '';
    modal.querySelector('#kf-selesai').value = isEdit ? group.tanggal_selesai : '';
    modal.querySelector('#kf-error').textContent = '';
    modal.classList.remove('hidden');
    modal.querySelector('#kf-nama').focus();
}
function closeGroupModal() {
    const modal = document.getElementById('modal-kelompok-form');
    if (modal) modal.classList.add('hidden');
}
async function submitGroupForm() {
    const modal = document.getElementById('modal-kelompok-form');
    const errEl = modal.querySelector('#kf-error');
    const btnSimpan = modal.querySelector('button[onclick="submitGroupForm()"]');
    const id = modal.querySelector('#kf-id').value;
    const nama = modal.querySelector('#kf-nama').value.trim();
    const mulai = modal.querySelector('#kf-mulai').value;
    const selesai = modal.querySelector('#kf-selesai').value;
    errEl.textContent = '';
    if (!nama || !mulai || !selesai) { errEl.textContent = 'Semua field wajib diisi.'; return; }
    // Loading state
    if (btnSimpan) { btnSimpan.disabled = true; btnSimpan.textContent = 'Menyimpan...'; }
    const fd = new FormData();
    fd.append('id', id);
    fd.append('nama', nama);
    fd.append('tanggal_mulai', mulai);
    fd.append('tanggal_selesai', selesai);
    try {
        const res = await fetch('?ajax=save_intern_group&_t=' + Date.now(), {method:'POST', body: fd});
        const data = await res.json();
        if (!data.ok) { errEl.textContent = data.message; return; }
        closeGroupModal();
        // Langsung reload data real-time
        await loadInternGroups();
        showToast(data.message, 'success');
    } catch(e) {
        errEl.textContent = 'Terjadi kesalahan. Coba lagi.';
    } finally {
        if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan'; }
    }
}

// ============================================================
// Modal: Kelola Anggota
// ============================================================
let _manageGroupId = null;
let _selectedMemberIds = new Set();

async function openManageMembers(groupId) {
    _manageGroupId = groupId;
    _selectedMemberIds = new Set();
    const grp = window._internGroups.find(g => g.id == groupId);
    const modal = document.getElementById('modal-kelola-anggota');
    if (!modal) return;
    modal.querySelector('#ma-group-name').textContent = grp ? grp.nama : 'Kelompok';
    modal.querySelector('#ma-body').innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fi fi-sr-spinner animate-spin text-xl block mb-2"></i>Memuat...</div>';
    modal.classList.remove('hidden');

    try {
        // Load current members for this group (cache-busting)
        const ts = Date.now();
        const [membersRes, allRes] = await Promise.all([
            fetch('?ajax=get_group_members&group_id=' + groupId + '&_t=' + ts),
            fetch('?ajax=get_members&no_embeddings=1&light=1&_t=' + ts)
        ]);
        const membersData = await membersRes.json();
        const allData = await allRes.json();
        const currentIds = new Set((membersData.data || []).map(m => m.id));
        currentIds.forEach(id => _selectedMemberIds.add(id));
        const allMembers = (allData.data || []).filter(m => m.role === 'pegawai');
        window._allMembersForGroup = allMembers;
        renderManageMembersBody(allMembers, currentIds);
    } catch(e) {
        modal.querySelector('#ma-body').innerHTML = '<div class="text-red-400 p-4">Gagal memuat data: '+e.message+'</div>';
    }
}

function renderManageMembersBody(members, currentIds) {
    const body = document.getElementById('ma-body');
    if (!body) return;
    if (members.length === 0) { body.innerHTML = '<div class="text-center py-8 text-gray-400">Belum ada member terdaftar.</div>'; return; }
    body.innerHTML = members.map(function(m) {
        const checked = currentIds.has(m.id) ? 'checked' : '';
        const initials = (m.nama||'?').split(' ').map(w=>w[0]||'').join('').substring(0,2).toUpperCase();
        return `<label class="flex items-center gap-3 p-3 hover:bg-indigo-50 rounded-xl cursor-pointer transition-colors border border-transparent hover:border-indigo-100 member-check-row ${checked ? 'bg-indigo-50/50' : ''}">
            <input type="checkbox" class="member-checkbox w-4 h-4 rounded accent-indigo-600" value="${m.id}" ${checked} onchange="onMemberCheckChange(this)">
            <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center bg-gradient-to-br from-indigo-400 to-purple-500 text-white text-xs font-bold">
                ${m.foto_base64 && m.foto_base64.startsWith('data:') ? '<img src="'+m.foto_base64+'" class="w-full h-full object-cover">' : initials}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm truncate">${m.nama}</p>
                <p class="text-xs text-gray-500">${m.nim||''} ${m.prodi ? '· '+m.prodi : ''}</p>
            </div>
        </label>`;
    }).join('');
    updateManageMembersCount();
}

function onMemberCheckChange(cb) {
    if (cb.checked) _selectedMemberIds.add(parseInt(cb.value));
    else _selectedMemberIds.delete(parseInt(cb.value));
    updateManageMembersCount();
}

function updateManageMembersCount() {
    const cnt = document.getElementById('ma-count');
    if (cnt) cnt.textContent = _selectedMemberIds.size + ' dipilih';
}

function filterMemberCheckList(q) {
    const rows = document.querySelectorAll('.member-check-row');
    q = (q||'').toLowerCase();
    rows.forEach(function(row) {
        const txt = row.textContent.toLowerCase();
        row.style.display = (!q || txt.includes(q)) ? '' : 'none';
    });
}

function closeManageMembersModal() {
    const modal = document.getElementById('modal-kelola-anggota');
    if (modal) modal.classList.add('hidden');
    _manageGroupId = null;
}

async function submitManageMembers() {
    if (!_manageGroupId) return;
    const btnSimpan = document.querySelector('#modal-kelola-anggota button[onclick="submitManageMembers()"]');
    if (btnSimpan) { btnSimpan.disabled = true; btnSimpan.textContent = 'Menyimpan...'; }
    const fd = new FormData();
    fd.append('group_id', _manageGroupId);
    _selectedMemberIds.forEach(id => fd.append('user_ids[]', id));
    try {
        const res = await fetch('?ajax=assign_members_to_group&_t=' + Date.now(), {method:'POST', body: fd});
        const data = await res.json();
        if (!data.ok) { showToast(data.message, 'error'); return; }
        closeManageMembersModal();
        // Reload data real-time setelah simpan anggota
        await loadInternGroups();
        showToast(data.message, 'success');
    } catch(e) {
        showToast('Gagal menyimpan anggota.', 'error');
    } finally {
        if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan Anggota'; }
    }
}

// ============================================================
// Archive / Unarchive / Delete — pakai modal konfirmasi Blade
// ============================================================
function confirmArchiveGroup(id, nama) {
    showKonfirmasiModal({
        type: 'archive',
        title: 'Archive Kelompok?',
        desc: 'Kelompok "' + nama + '" akan diarsipkan.',
        badges: [
            'Pegawai tidak tampil di daftar aktif',
            'Tidak dihitung dalam KPI aktif',
            'Data tetap aman & bisa di-unarchive'
        ],
        btnLabel: 'Ya, Archive',
        callback: function() {
            postGroupAction('archive_intern_group', id).then(async function(data) {
                if (!data.ok) { showToast(data.message, 'error'); return; }
                showToast(data.message, 'success');
                await loadInternGroups();
            });
        }
    });
}

function confirmUnarchiveGroup(id, nama) {
    showKonfirmasiModal({
        type: 'unarchive',
        title: 'Unarchive Kelompok?',
        desc: 'Kelompok "' + nama + '" akan dikembalikan ke daftar aktif.',
        badges: [
            'Pegawai kembali tampil di daftar aktif',
            'Dihitung kembali dalam KPI'
        ],
        btnLabel: 'Ya, Unarchive',
        callback: function() {
            postGroupAction('unarchive_intern_group', id).then(async function(data) {
                if (!data.ok) { showToast(data.message, 'error'); return; }
                showToast(data.message, 'success');
                await loadInternGroups();
            });
        }
    });
}

function confirmDeleteGroup(id, nama) {
    showKonfirmasiModal({
        type: 'delete',
        title: 'Hapus Kelompok?',
        desc: 'Kelompok "' + nama + '" akan dihapus permanen.',
        badges: [
            'Data kelompok akan dihapus',
            'Data pegawai tidak akan ikut terhapus'
        ],
        btnLabel: 'Ya, Hapus',
        callback: function() {
            postGroupAction('delete_intern_group', id).then(async function(data) {
                if (!data.ok) { showToast(data.message, 'error'); return; }
                showToast(data.message, 'success');
                await loadInternGroups();
            });
        }
    });
}

async function postGroupAction(action, id) {
    try {
        const fd = new FormData();
        fd.append('id', id);
        const res = await fetch('?ajax=' + action + '&_t=' + Date.now(), {method:'POST', body: fd});
        if (!res.ok) {
            return { ok: false, message: 'Server error (Status: ' + res.status + ')' };
        }
        return await res.json();
    } catch (e) {
        return { ok: false, message: 'Gagal menghubungi server: ' + e.message };
    }
}

function downloadGroupDatabase(groupId) {
    window.open('?ajax=export_group_database&group_id=' + groupId, '_blank');
}

function showToast(msg, type) {
    // Gunakan sistem notifikasi yang ada, bukan alert browser
    if (typeof window.showNotification === 'function') {
        window.showNotification(msg, type);
    } else if (typeof window.toast === 'function') {
        window.toast(msg, type);
    } else {
        // Fallback: tampilkan toast sederhana tanpa alert()
        _showFallbackToast(msg, type);
    }
}

function _showFallbackToast(msg, type) {
    const existing = document.getElementById('_fallback-toast');
    if (existing) existing.remove();
    const colors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#6366f1' };
    const toast = document.createElement('div');
    toast.id = '_fallback-toast';
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + (colors[type]||colors.info) + ';color:white;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,0.2);animation:_toast-in 0.3s ease-out;max-width:320px';
    toast.textContent = msg;
    document.body.appendChild(toast);
    // Style
    if (!document.getElementById('_toast-style')) {
        const s = document.createElement('style');
        s.id = '_toast-style';
        s.textContent = '@keyframes _toast-in{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}';
        document.head.appendChild(s);
    }
    setTimeout(() => { if (toast.parentNode) toast.remove(); }, 3500);
}
</script>
