let csrfToken = '';
let reservations = [];
let lastEditTrigger = null;
let currentUserRole = '';

function toggleModal(id, show) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = show ? 'flex' : 'none';
    if (id === 'editReservationModal' || id === 'createStaffModal') {
        modal.setAttribute('aria-hidden', String(!show));
    }
}

function openStaffLogin() {
    resetLoginPrompt();
    toggleModal('loginModal', true);
    setTimeout(() => document.getElementById('staffPass').focus(), 100);
}

function requestStaffCreation() {
    closeStaffLogin();
    openCreateStaff();
}

function resetLoginPrompt() {
    document.getElementById('login-title').textContent = 'Staff Authentication';
    document.getElementById('login-subtitle').textContent = 'Authorized Personnel Only';
    document.getElementById('login-submit').textContent = 'Access Dashboard';
    document.getElementById('login-error').textContent = '';
}

function closeStaffLogin() {
    resetLoginPrompt();
    toggleModal('loginModal', false);
}

async function apiRequest(action, payload = null) {
    const options = payload ? {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...payload, csrf_token: csrfToken })
    } : {};
    const response = await fetch(`api.php?action=${encodeURIComponent(action)}`, options);
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'The request failed.');
    return result;
}

async function handleBooking(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const data = Object.fromEntries(new FormData(form));
    try {
        await apiRequest('create', data);
        alert('Reservation confirmed! We look forward to seeing you.');
        toggleModal('bookingModal', false);
        form.reset();
    } catch (error) {
        alert(error.message);
    }
}

async function checkLogin(event) {
    if (event) event.preventDefault();
    const error = document.getElementById('login-error');
    error.textContent = '';
    try {
        const result = await apiRequest('login', {
            username: document.getElementById('staffUser').value,
            password: document.getElementById('staffPass').value
        });
        csrfToken = result.csrf_token;
        currentUserRole = result.role;
        document.getElementById('staffPass').value = '';
        resetLoginPrompt();
        toggleModal('loginModal', false);
        showDashboard();
        await renderAdminDashboard();
    } catch (requestError) {
        error.textContent = requestError.message;
    }
}

function showDashboard() {
    document.getElementById('public-site').style.display = 'none';
    document.getElementById('admin-page').style.display = 'block';
}

function openCreateStaff() {
    const form = document.getElementById('create-staff-form');
    form.reset();
    document.getElementById('create-staff-error').textContent = '';
    document.getElementById('create-staff-success').textContent = '';
    document.getElementById('create-staff-submit').disabled = false;
    toggleModal('createStaffModal', true);
    document.getElementById('new-staff-username').focus();
}

function closeCreateStaff() {
    toggleModal('createStaffModal', false);
}

async function createStaff(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const errorMessage = document.getElementById('create-staff-error');
    const successMessage = document.getElementById('create-staff-success');
    const submitButton = document.getElementById('create-staff-submit');
    errorMessage.textContent = '';
    successMessage.textContent = '';

    const data = Object.fromEntries(new FormData(form));
    if (data.password !== data.password_confirm) {
        errorMessage.textContent = 'The passwords do not match.';
        return;
    }

    submitButton.disabled = true;
    try {
        if (!csrfToken) {
            const status = await apiRequest('status');
            csrfToken = status.csrf_token || '';
        }
        if (!csrfToken) {
            throw new Error('Unable to verify your session. Refresh the page and try again.');
        }
        const result = await apiRequest('create-staff', data);
        form.reset();
        successMessage.textContent = `${result.message} You can now sign in.`;
    } catch (requestError) {
        errorMessage.textContent = requestError.message;
    } finally {
        submitButton.disabled = false;
    }
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[character]);
}

function renderAdminList(searchTerm = '') {
    const table = document.getElementById('admin-list');
    const query = searchTerm.trim().toLowerCase();
    const filtered = reservations.filter(reservation =>
        [reservation.name, reservation.email, reservation.phone, reservation.date, reservation.status]
            .some(value => String(value).toLowerCase().includes(query))
    );
    if (filtered.length === 0) {
        table.innerHTML = '<tr><td colspan="6" class="no-res">No matching reservations</td></tr>';
        return;
    }

    table.innerHTML = filtered.map(reservation => `
        <tr>
            <td>${escapeHtml(reservation.name)}</td>
            <td>${escapeHtml(reservation.email)} / ${escapeHtml(reservation.phone)}</td>
            <td>${escapeHtml(reservation.date)} / ${escapeHtml(reservation.time)}</td>
            <td>${escapeHtml(reservation.guests)}</td>
            <td><span class="status-badge status-${escapeHtml(reservation.status)}">${escapeHtml(reservation.status)}</span></td>
            <td>
                <button type="button" onclick="editRes(${Number(reservation.id)})">Edit</button>
                ${reservation.status === 'confirmed'
                    ? `<button type="button" onclick="cancelRes(${Number(reservation.id)})">Cancel</button>`
                    : ''}
            </td>
        </tr>
    `).join('');
}

function renderHistory(history) {
    const container = document.getElementById('reservation-history');
    if (!history.length) {
        container.innerHTML = '<p class="no-res">No reservation activity yet.</p>';
        return;
    }
    container.innerHTML = history.map(event => {
        const snapshot = JSON.parse(event.snapshot);
        return `<article class="history-entry">
            <div><strong>${escapeHtml(event.action)}</strong> — ${escapeHtml(snapshot.name)}
                <span>Reservation #${Number(event.reservation_id)} · ${escapeHtml(snapshot.date)} ${escapeHtml(snapshot.time)} · by ${escapeHtml(event.actor)}</span>
            </div>
            <time>${escapeHtml(event.created_at)}</time>
        </article>`;
    }).join('');
}

async function renderAdminDashboard() {
    const error = document.getElementById('dashboard-error');
    error.textContent = '';
    try {
        const result = await apiRequest('dashboard');
        reservations = result.reservations;
        const total = reservations.length;
        const confirmed = reservations.filter(item => item.status === 'confirmed').length;
        const cancelled = total - confirmed;
        document.getElementById('dashboard-stats').innerHTML = `
            <article class="stat-card"><span>Total reservations</span><strong>${total}</strong></article>
            <article class="stat-card"><span>Confirmed</span><strong>${confirmed}</strong></article>
            <article class="stat-card"><span>Cancelled</span><strong>${cancelled}</strong></article>
        `;
        renderAdminList(document.getElementById('reservation-search').value);
        renderHistory(result.history);
    } catch (requestError) {
        error.textContent = requestError.message;
    }
}

async function editRes(id) {
    const reservation = reservations.find(item => Number(item.id) === Number(id));
    if (!reservation) return;
    lastEditTrigger = document.activeElement;
    const form = document.getElementById('edit-reservation-form');
    form.elements.name.value = reservation.name ?? '';
    form.elements.email.value = reservation.email ?? '';
    form.elements.phone.value = reservation.phone ?? '';
    form.elements.date.value = reservation.date ?? '';
    form.elements.time.value = reservation.time ?? '';
    form.elements.guests.value = reservation.guests ?? '';
    document.getElementById('edit-reservation-id').value = reservation.id;
    document.getElementById('edit-reservation-error').textContent = '';
    document.getElementById('edit-reservation-submit').disabled = false;
    toggleModal('editReservationModal', true);
    document.getElementById('edit-reservation-name').focus();
}

function closeEditReservation() {
    toggleModal('editReservationModal', false);
    if (lastEditTrigger && document.contains(lastEditTrigger)) {
        lastEditTrigger.focus();
    }
    lastEditTrigger = null;
}

async function saveReservationEdit(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const submitButton = document.getElementById('edit-reservation-submit');
    const errorMessage = document.getElementById('edit-reservation-error');
    errorMessage.textContent = '';
    submitButton.disabled = true;
    const payload = Object.fromEntries(new FormData(form));
    payload.id = Number(document.getElementById('edit-reservation-id').value);

    try {
        await apiRequest('update', payload);
        await renderAdminDashboard();
        if (!errorMessage.textContent) closeEditReservation();
    } catch (requestError) {
        errorMessage.textContent = requestError.message;
    } finally {
        submitButton.disabled = false;
    }
}

async function cancelRes(id) {
    if (!confirm('Cancel this reservation? It will remain visible in the history.')) return;
    try {
        await apiRequest('cancel', { id });
        await renderAdminDashboard();
    } catch (error) {
        document.getElementById('dashboard-error').textContent = error.message;
    }
}

async function logoutAdmin() {
    try {
        await apiRequest('logout', {});
        location.reload();
    } catch (error) {
        document.getElementById('dashboard-error').textContent = error.message;
    }
}

function renderMenu() {
    const container = document.getElementById('menu-display');
    if (!container || typeof MenuModule === 'undefined') return;
    const generateSection = (title, items) => `
        <h3>${title}</h3>
        ${items.map(item => `
            <div class="menu-item">
                <img src="${escapeHtml(item.img)}" alt="${escapeHtml(item.name)}">
                <div style="flex:1">
                    <div class="item-top"><span>${escapeHtml(item.name)}</span><span>${escapeHtml(item.price)}</span></div>
                    <p>${escapeHtml(item.desc)}</p>
                </div>
            </div>
        `).join('')}`;
    container.innerHTML = generateSection('Appetizers', MenuModule.starters)
        + generateSection('Main Courses', MenuModule.mains)
        + generateSection('Desserts', MenuModule.desserts);
}

document.addEventListener('DOMContentLoaded', async () => {
    renderMenu();
    const editModal = document.getElementById('editReservationModal');
    editModal.addEventListener('click', event => {
        if (event.target === editModal) closeEditReservation();
    });
    const createStaffModal = document.getElementById('createStaffModal');
    createStaffModal.addEventListener('click', event => {
        if (event.target === createStaffModal) closeCreateStaff();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && editModal.style.display === 'flex') {
            closeEditReservation();
        }
        if (event.key === 'Escape' && createStaffModal.style.display === 'flex') {
            closeCreateStaff();
        }
    });
    const dateInput = document.getElementById('res-date');
    const localToday = new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
        .toISOString().slice(0, 10);
    dateInput.min = localToday;
    document.getElementById('reservation-search').addEventListener('input', event => {
        renderAdminList(event.target.value);
    });
    try {
        const result = await apiRequest('status');
        csrfToken = result.csrf_token || '';
        if (!result.authenticated) return;
        currentUserRole = result.role;
        showDashboard();
        await renderAdminDashboard();
    } catch (error) {
        if (error.message !== 'Please sign in as an administrator.') {
            document.getElementById('dashboard-error').textContent = error.message;
        }
    }
});
