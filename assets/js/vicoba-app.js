/**
 * VICOBA Application JavaScript Engine
 * Handles all form submissions, modals, and API calls
 * Version: 2.0
 */

(function ($) {
    'use strict';

    const API = vicobaData.root + 'vicoba/v1';
    const NONCE = vicobaData.nonce;

    /* ============================================================
       UTILITY FUNCTIONS
    ============================================================ */

    function showToast(message, type = 'success') {
        let toast = document.getElementById('vicobaToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'vicobaToast';
            toast.style.cssText = 'position:fixed;top:24px;right:24px;z-index:99999;min-width:280px;max-width:420px;border-radius:14px;padding:16px 20px;font-size:13px;font-weight:600;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;align-items:center;gap:12px;transition:all 0.3s ease;';
            document.body.appendChild(toast);
        }
        const colors = {
            success: { bg: '#ecfdf5', border: '#6ee7b7', text: '#065f46', icon: 'fa-circle-check' },
            error:   { bg: '#fef2f2', border: '#fca5a5', text: '#7f1d1d', icon: 'fa-circle-xmark' },
            info:    { bg: '#eff6ff', border: '#93c5fd', text: '#1e3a8a', icon: 'fa-circle-info' },
            warning: { bg: '#fffbeb', border: '#fcd34d', text: '#78350f', icon: 'fa-triangle-exclamation' },
        };
        const c = colors[type] || colors.success;
        toast.style.background = c.bg;
        toast.style.border = '1.5px solid ' + c.border;
        toast.style.color = c.text;
        toast.innerHTML = `<i class="fa-solid ${c.icon}" style="font-size:18px;flex-shrink:0"></i><span>${message}</span>`;
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(30px)';
        }, 4500);
    }

    function setLoading(btn, loading) {
        if (!btn) return;
        if (loading) {
            btn._originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Inatuma...';
            btn.disabled = true;
        } else {
            btn.innerHTML = btn._originalText || btn.innerHTML;
            btn.disabled = false;
        }
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) m.classList.add('hidden');
    }

    function openModal(id) {
        const m = document.getElementById(id);
        if (m) m.classList.remove('hidden');
    }

    function apiPost(endpoint, data, btn, onSuccess) {
        setLoading(btn, true);
        fetch(API + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE,
            },
            body: JSON.stringify(data),
        })
        .then(r => r.json())
        .then(res => {
            setLoading(btn, false);
            if (res.success) {
                showToast(res.message || 'Imefanikiwa!', 'success');
                if (res.redirect_url) {
                    setTimeout(() => { window.location.href = res.redirect_url; }, 1200);
                } else {
                    if (typeof onSuccess === 'function') onSuccess(res);
                    else setTimeout(() => location.reload(), 1500);
                }
            } else {
                showToast(res.message || 'Hitilafu imetokea!', 'error');
            }
        })
        .catch(err => {
            setLoading(btn, false);
            showToast('Hitilafu ya mtandao: ' + err.message, 'error');
        });
    }

    function getFormData(form) {
        const data = {};
        new FormData(form).forEach((v, k) => { data[k] = v; });
        return data;
    }

    /* ============================================================
       LOGIN FORM
    ============================================================ */

    $(document).on('submit', '#vicobaLoginForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#loginBtn');
        const data = getFormData(this);
        apiPost('/auth/login', data, btn, function (res) {
            if (res.redirect_url) window.location.href = res.redirect_url;
        });
    });

    /* ============================================================
       REGISTER GROUP FORM
    ============================================================ */

    $(document).on('submit', '#vicobaRegisterForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#registerBtn');
        const data = getFormData(this);
        apiPost('/auth/register-group', {
            group: {
                name:         data.group_name,
                description:  data.group_description,
                share_price:  data.share_price,
                currency:     data.currency || 'TZS',
            },
            admin: {
                full_name: data.admin_full_name,
                phone:     data.admin_phone,
                email:     data.admin_email,
                username:  data.admin_username,
                password:  data.admin_password,
            }
        }, btn, function (res) {
            if (res.redirect_url) window.location.href = res.redirect_url;
        });
    });

    /* ============================================================
       ADD MEMBER FORM
    ============================================================ */

    $(document).on('submit', '#addMemberForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveMemberBtn');
        const data = getFormData(this);
        apiPost('/members/add', data, btn, function () {
            closeModal('addMemberModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       BUY SHARES FORM
    ============================================================ */

    $(document).on('submit', '#buySharesForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveShareBtn');
        const data = getFormData(this);
        apiPost('/shares/record', data, btn, function () {
            closeModal('buySharesModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       APPLY LOAN FORM
    ============================================================ */

    $(document).on('submit', '#applyLoanForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveLoanBtn');
        const data = getFormData(this);
        // Collect guarantors (multiple checkboxes)
        const guarantors = [];
        this.querySelectorAll('input[name="guarantor_ids[]"]:checked').forEach(cb => guarantors.push(parseInt(cb.value)));
        data.guarantor_ids = guarantors;
        apiPost('/loans/apply', data, btn, function () {
            closeModal('applyLoanModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       LOAN REPAY FORM
    ============================================================ */

    $(document).on('submit', '#repayLoanForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveRepayBtn');
        const data = getFormData(this);
        apiPost('/loans/repay', data, btn, function () {
            closeModal('repayLoanModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       LOAN DISBURSE
    ============================================================ */

    $(document).on('click', '.disburse-loan-btn', function () {
        if (!confirm('Je, uko tayari kutoa mkopo huu? Hatua hii haiwezi kubatilishwa.')) return;
        const loanId = this.dataset.loanId;
        const method = this.dataset.method || 'cash';
        apiPost('/loans/disburse', { loan_id: loanId, payment_method: method }, this);
    });

    /* ============================================================
       GUARANTOR RESPOND
    ============================================================ */

    $(document).on('click', '.guarantor-accept-btn, .guarantor-reject-btn', function () {
        const gId = this.dataset.guarantorId;
        const status = this.classList.contains('guarantor-accept-btn') ? 'accepted' : 'rejected';
        apiPost('/loans/guarantor-respond', { guarantor_id: gId, status: status }, this);
    });

    /* ============================================================
       ISSUE FINE FORM
    ============================================================ */

    $(document).on('submit', '#issueFineForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveFineBtn');
        const data = getFormData(this);
        apiPost('/fines/issue', data, btn, function () {
            closeModal('issueFineModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       PAY FINE
    ============================================================ */

    $(document).on('click', '.pay-fine-btn', function () {
        if (!confirm('Thibitisha malipo ya faini hii?')) return;
        const fineId = this.dataset.fineId;
        apiPost('/fines/pay', { fine_id: fineId, payment_method: 'cash' }, this);
    });

    /* ============================================================
       CREATE MEETING FORM
    ============================================================ */

    $(document).on('submit', '#createMeetingForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveMeetingBtn');
        const data = getFormData(this);
        apiPost('/meetings/create', data, btn, function (res) {
            closeModal('createMeetingModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       RECORD ATTENDANCE FORM
    ============================================================ */

    $(document).on('submit', '#recordAttendanceForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveAttendanceBtn');
        const meetingId = this.querySelector('[name="meeting_id"]').value;
        const attendance = [];
        this.querySelectorAll('.attendance-row').forEach(row => {
            attendance.push({
                member_id: row.dataset.memberId,
                status:    row.querySelector('select').value,
            });
        });
        apiPost('/meetings/attendance', { meeting_id: meetingId, attendance: attendance, auto_fine: true }, btn, function () {
            closeModal('attendanceModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       SOCIAL FUND REQUEST FORM
    ============================================================ */

    $(document).on('submit', '#socialRequestForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveSocialRequestBtn');
        const data = getFormData(this);
        apiPost('/social-fund/request', data, btn, function () {
            closeModal('socialRequestModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       SOCIAL FUND CONTRIBUTE FORM
    ============================================================ */

    $(document).on('submit', '#socialContributeForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveSocialContributeBtn');
        const data = getFormData(this);
        apiPost('/social-fund/contribute', data, btn, function () {
            closeModal('socialContributeModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       SOCIAL FUND APPROVE
    ============================================================ */

    $(document).on('click', '.approve-social-btn', function () {
        if (!confirm('Thibitisha kutoa msaada huu?')) return;
        const reqId = this.dataset.requestId;
        apiPost('/social-fund/approve', { request_id: reqId, payment_method: 'cash' }, this);
    });

    /* ============================================================
       ADD EXPENSE (LEDGER) FORM
    ============================================================ */

    $(document).on('submit', '#addExpenseForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveExpenseBtn');
        const data = getFormData(this);
        apiPost('/ledger/expense', data, btn, function () {
            closeModal('addExpenseModal');
            setTimeout(() => location.reload(), 1500);
        });
    });

    /* ============================================================
       SETTINGS FORM
    ============================================================ */

    $(document).on('submit', '#groupSettingsForm', function (e) {
        e.preventDefault();
        const btn = this.querySelector('#saveSettingsBtn');
        const data = getFormData(this);
        apiPost('/settings/update', data, btn);
    });

    /* ============================================================
       SHAREOUT FINALIZE
    ============================================================ */

    $(document).on('click', '#finalizeShareoutBtn', function () {
        if (!confirm('TAHADHARI: Je, uko tayari kufunga mzunguko huu na kugawanya mapato? Hatua hii haiwezi kubatilishwa!')) return;
        apiPost('/shareout/finalize', {}, this);
    });

    /* ============================================================
       MOBILE NAV TOGGLE
    ============================================================ */

    $(document).on('click', '#mobileNavToggle', function () {
        const sidebar = document.getElementById('mobileSidebar');
        if (sidebar) sidebar.classList.toggle('hidden');
    });

    /* ============================================================
       CLOSE MODAL ON BACKDROP CLICK
    ============================================================ */

    $(document).on('click', '.vicoba-modal-backdrop', function (e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });

    /* ============================================================
       LOAN CALCULATOR (Real-time)
    ============================================================ */

    function updateLoanPreview() {
        const form = document.getElementById('applyLoanForm');
        if (!form) return;
        const principal = parseFloat(form.querySelector('[name="principal_amount"]')?.value || 0);
        const months    = parseInt(form.querySelector('[name="repayment_period_months"]')?.value || 1);
        const rate      = parseFloat(form.dataset.interestRate || 10);
        const type      = form.dataset.interestType || 'flat';

        let total = principal;
        if (type === 'flat') {
            total = principal + (principal * rate / 100 * months);
        } else {
            // Reducing balance approximation
            total = principal * (1 + rate / 100) ** months;
        }
        const monthly = months > 0 ? total / months : total;

        const previewEl = document.getElementById('loanPreviewTotal');
        const monthlyEl = document.getElementById('loanPreviewMonthly');
        if (previewEl) previewEl.textContent = 'TZS ' + Math.round(total).toLocaleString();
        if (monthlyEl) monthlyEl.textContent = 'TZS ' + Math.round(monthly).toLocaleString() + '/mwezi';
    }

    $(document).on('input', '#applyLoanForm [name="principal_amount"], #applyLoanForm [name="repayment_period_months"]', updateLoanPreview);

    /* ============================================================
       REPAY MODAL OPEN
    ============================================================ */

    $(document).on('click', '.open-repay-btn', function () {
        const loanId = this.dataset.loanId;
        const outstanding = this.dataset.outstanding;
        const form = document.getElementById('repayLoanForm');
        if (form) {
            form.querySelector('[name="loan_id"]').value = loanId;
            const amtInput = form.querySelector('[name="amount"]');
            if (amtInput) amtInput.placeholder = 'Hadi TZS ' + parseFloat(outstanding).toLocaleString();
        }
        openModal('repayLoanModal');
    });

    /* ============================================================
       SUPER ADMIN: Load Groups via API
    ============================================================ */

    function loadSuperAdminGroups() {
        const container = document.getElementById('saasGroupsTable');
        if (!container) return;

        fetch(API + '/superadmin/groups', {
            headers: { 'X-WP-Nonce': NONCE }
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.groups.length) {
                container.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-slate-400">Hakuna vikundi vilivyosajiliwa bado.</td></tr>';
                return;
            }
            container.innerHTML = res.groups.map(g => `
                <tr class="hover:bg-slate-50/80 transition text-xs font-medium text-slate-700">
                    <td class="py-3.5 px-4 font-bold text-vicoba-800">#${g.id}</td>
                    <td class="py-3.5 px-4 font-semibold text-slate-800">${escHtml(g.name)}</td>
                    <td class="py-3.5 px-4">${g.member_count} wanachama</td>
                    <td class="py-3.5 px-4">TZS ${parseFloat(g.share_price).toLocaleString()}</td>
                    <td class="py-3.5 px-4">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase ${g.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'}">
                            ${g.status}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <button class="toggle-group-status-btn px-2.5 py-1 rounded-lg ${g.status === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'} font-bold text-xs transition"
                            data-group-id="${g.id}" data-current-status="${g.status}">
                            ${g.status === 'active' ? 'Simamisha' : 'Amilisha'}
                        </button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(() => {
            container.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-rose-400">Hitilafu ya kupakua data.</td></tr>';
        });
    }

    $(document).on('click', '.toggle-group-status-btn', function () {
        const groupId = this.dataset.groupId;
        const newStatus = this.dataset.currentStatus === 'active' ? 'suspended' : 'active';
        apiPost('/superadmin/group-status', { group_id: groupId, status: newStatus }, this, function () {
            loadSuperAdminGroups();
        });
    });

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ============================================================
       INIT
    ============================================================ */

    $(document).ready(function () {
        // Load super admin groups if on that page
        loadSuperAdminGroups();

        // Set backdrop class on all modals for click-outside to close
        document.querySelectorAll('[id$="Modal"]').forEach(m => {
            m.classList.add('vicoba-modal-backdrop');
        });

        // Report export button (print page)
        $(document).on('click', '#printReportBtn', function () {
            window.print();
        });
    });

})(jQuery);
