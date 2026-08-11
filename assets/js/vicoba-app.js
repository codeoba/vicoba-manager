/**
 * VICOBA App Core JavaScript
 * Handles REST API interactions, modal dynamic behaviors, and dynamic validations
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Mobile Sidebar Toggle
    const mobileToggle = document.getElementById('mobileNavToggle');
    const sidebar = document.querySelector('aside');
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-0');
            sidebar.classList.toggle('z-50');
        });
    }

    // 2. Buy Shares Form AJAX Submission
    const buySharesForm = document.getElementById('buySharesForm');
    if (buySharesForm) {
        buySharesForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveShareBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inahifadhi...';

            const payload = {
                group_id: buySharesForm.group_id.value,
                member_id: buySharesForm.member_id.value,
                share_count: buySharesForm.share_count.value,
                payment_method: buySharesForm.payment_method.value,
            };

            try {
                const res = await fetch(vicobaData.root + 'vicoba/v1/shares/record', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': vicobaData.nonce,
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Imefanikiwa!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Imeshindikana', data.message || 'Kosa la kuingiza hisa.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Hifadhi Malipo';
                }
            } catch(err) {
                Swal.fire('Hitilafu', 'Kuna tatizo la mtandao.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Hifadhi Malipo';
            }
        });
    }

    // 3. Apply Loan Form AJAX Submission
    const applyLoanForm = document.getElementById('applyLoanForm');
    if (applyLoanForm) {
        applyLoanForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitLoanBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inatuma...';

            const guarantorSelect = applyLoanForm.guarantor_ids;
            const selectedGuarantors = Array.from(guarantorSelect.selectedOptions).map(opt => opt.value);

            const payload = {
                group_id: applyLoanForm.group_id.value,
                member_id: applyLoanForm.member_id.value,
                principal_amount: applyLoanForm.principal_amount.value,
                repayment_period_months: applyLoanForm.repayment_period_months.value,
                purpose: applyLoanForm.purpose.value,
                guarantor_ids: selectedGuarantors,
            };

            try {
                const res = await fetch(vicobaData.root + 'vicoba/v1/loans/apply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': vicobaData.nonce,
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Ombi Limetumwa!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Imeshindikana', data.message || 'Kosa la kutuma ombi la mkopo.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Tuma Ombi la Mkopo';
                }
            } catch(err) {
                Swal.fire('Hitilafu', 'Kuna tatizo la mtandao.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Tuma Ombi la Mkopo';
            }
        });
    }

    // 4. Repay Loan Form AJAX Submission
    const repayLoanForm = document.getElementById('repayLoanForm');
    if (repayLoanForm) {
        repayLoanForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveRepayBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inahifadhi...';

            const payload = {
                loan_id: repayLoanForm.loan_id.value,
                amount: repayLoanForm.amount.value,
                payment_method: repayLoanForm.payment_method.value,
            };

            try {
                const res = await fetch(vicobaData.root + 'vicoba/v1/loans/repay', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': vicobaData.nonce,
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Rejesho Limeingizwa!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Imeshindikana', data.message || 'Kosa la kuingiza rejesho.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Hifadhi Rejesho';
                }
            } catch(err) {
                Swal.fire('Hitilafu', 'Kuna tatizo la mtandao.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Hifadhi Rejesho';
            }
        });
    }

    // 5. Add Member Form
    const addMemberForm = document.getElementById('addMemberForm');
    if (addMemberForm) {
        addMemberForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveMemberBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inahifadhi...';

            const payload = {
                full_name: addMemberForm.full_name.value,
                phone: addMemberForm.phone.value,
                email: addMemberForm.email.value,
                username: addMemberForm.username.value,
                password: addMemberForm.password.value,
                role: addMemberForm.role.value,
                nida_number: addMemberForm.nida_number.value,
            };

            try {
                const res = await fetch(vicobaData.root + 'vicoba/v1/members/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': vicobaData.nonce,
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Mwanachama Ameongezwa!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Imeshindikana', data.message || 'Kosa la kuongeza mwanachama.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Hifadhi Mwanachama';
                }
            } catch(err) {
                Swal.fire('Hitilafu', 'Kuna tatizo la mtandao.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Hifadhi Mwanachama';
            }
        });
    }

    // 6. Issue Fine Form
    const issueFineForm = document.getElementById('issueFineForm');
    if (issueFineForm) {
        issueFineForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveFineBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inatoza...';

            const payload = {
                group_id: issueFineForm.group_id.value,
                member_id: issueFineForm.member_id.value,
                fine_type_id: issueFineForm.fine_type_id.value,
                amount: issueFineForm.amount.value,
                reason: issueFineForm.reason.value,
            };

            try {
                const res = await fetch(vicobaData.root + 'vicoba/v1/fines/issue', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': vicobaData.nonce,
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Faini Imewekwa!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Imeshindikana', data.message || 'Kosa la kutolaza faini.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Toza Faini';
                }
            } catch(err) {
                Swal.fire('Hitilafu', 'Kuna tatizo la mtandao.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Toza Faini';
            }
        });
    }

    // 7. Add Expense Form
    const addExpenseForm = document.getElementById('addExpenseForm');
    if (addExpenseForm) {
        addExpenseForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveExpenseBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Inahifadhi...';

            const payload = {
                group_id: addExpenseForm.group_id.value,
                amount: addExpenseForm.amount.value,
                description: addExpenseForm.description.value,
                payment_method: addExpenseForm.payment_method.value,
            };

            try {
                const res = await fetch(vicobaData.root + 'vicoba/v1/ledger/expense', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': vicobaData.nonce,
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Gharama Imerekodiwa!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Imeshindikana', data.message || 'Kosa la kuingiza gharama.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Hifadhi Gharama';
                }
            } catch(err) {
                Swal.fire('Hitilafu', 'Kuna tatizo la mtandao.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Hifadhi Gharama';
            }
        });
    }
});
