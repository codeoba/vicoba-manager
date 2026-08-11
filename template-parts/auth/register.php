<?php
/**
 * Frontend Group Registration Page Template
 */

get_header();
?>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-vicoba-900 via-vicoba-800 to-slate-900 relative overflow-hidden">
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-vicoba-500/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-2xl relative z-10">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-vicoba-600 to-emerald-400 text-white shadow-xl mb-3">
                <i class="fa-solid fa-users-rectangle text-3xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Usajili wa Kikundi Kipya cha VICOBA</h2>
            <p class="mt-2 text-sm text-vicoba-200">Jaza taarifa za kikundi na za Mwenyekiti/Msimamizi wa Kikundi</p>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="glass-card py-8 px-6 shadow-2xl rounded-2xl sm:px-10 border border-white/20">
                <form id="vicobaRegisterForm" class="space-y-6">
                    <h3 class="text-lg font-bold text-vicoba-800 border-b pb-2"><i class="fa-solid fa-building mr-2"></i> Taarifa za Kikundi</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Jina la Kikundi *</label>
                            <input name="group_name" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="mf. VICOBA Tumaini">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Namba ya Usajili (Ipo/Hiari)</label>
                            <input name="registration_number" type="text" class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="mf. REG/2024/001">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Mkoa *</label>
                            <input name="region" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="mf. Dar es Salaam">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Wilaya *</label>
                            <input name="district" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="mf. Ilala">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Bei ya Hisa Moja (TZS) *</label>
                            <input name="share_price" type="number" value="10000" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Riba ya Mikopo (%) *</label>
                            <input name="interest_rate" type="number" step="0.1" value="5" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80">
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-vicoba-800 border-b pb-2 pt-2"><i class="fa-solid fa-user-shield mr-2"></i> Taarifa za Mwenyekiti (Admin)</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Majina Kamili *</label>
                            <input name="full_name" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="mf. Juma Hamisi">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Namba ya Simu *</label>
                            <input name="phone" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="mf. 0712345678">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Barua Pepe (Email) *</label>
                            <input name="email" type="email" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="mwenyekiti@gmail.com">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Jina la Mtumiaji (Username) *</label>
                            <input name="username" type="text" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="juma_admin">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Nenosiri (Password) *</label>
                            <input name="password" type="password" required class="mt-1 block w-full px-3 py-2 border rounded-lg text-sm bg-white/80" placeholder="••••••••">
                        </div>
                    </div>

                    <div>
                        <button type="submit" id="regBtn" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:from-vicoba-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vicoba-500 transition duration-150 transform active:scale-95">
                            <i class="fa-solid fa-check-circle mr-2 self-center"></i> Sajili Kikundi & Anza Mfumo
                        </button>
                    </div>
                </form>

                <div class="mt-6 border-t border-slate-200/60 pt-6 text-center">
                    <p class="text-sm text-slate-600">
                        Unayo akaunti tayari? 
                        <a href="<?php echo home_url('/login/'); ?>" class="font-bold text-vicoba-700 hover:text-vicoba-800 underline">Ingia Hapa</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vicobaRegisterForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('regBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Inasajili...';

        const payload = {
            group: {
                name: form.group_name.value,
                registration_number: form.registration_number.value,
                region: form.region.value,
                district: form.district.value,
                share_price: form.share_price.value,
                interest_rate: form.interest_rate.value,
            },
            admin: {
                full_name: form.full_name.value,
                phone: form.phone.value,
                email: form.email.value,
                username: form.username.value,
                password: form.password.value,
            }
        };

        try {
            const res = await fetch(vicobaData.root + 'vicoba/v1/auth/register-group', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Usajili Umekamilika!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirect_url;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Imeshindikana',
                    text: data.message || 'Kuna tatizo kwenye usajili.',
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check-circle mr-2"></i> Sajili Kikundi & Anza Mfumo';
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Hitilafu',
                text: 'Kuna tatizo la mtandao, jaribu tena.',
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check-circle mr-2"></i> Sajili Kikundi & Anza Mfumo';
        }
    });
});
</script>

<?php get_footer(); ?>
