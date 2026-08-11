<?php
/**
 * Frontend Login Page Template
 */

get_header();
?>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-vicoba-900 via-vicoba-800 to-slate-900 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-vicoba-500/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-tr from-vicoba-600 to-emerald-400 text-white shadow-xl shadow-vicoba-900/50 mb-4">
                <i class="fa-solid fa-piggy-bank text-4xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">VICOBA System</h2>
            <p class="mt-2 text-sm text-vicoba-200">Mfumo wa Kujihudumia wa Kikundi Cha VICOBA</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="glass-card py-8 px-6 shadow-2xl rounded-2xl sm:px-10 border border-white/20">
                <form id="vicobaLoginForm" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700">Jina la Mtumiaji au Namba ya Simu</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input id="username" name="username" type="text" required class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl leading-5 bg-white/80 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-vicoba-500 focus:border-vicoba-500 text-sm" placeholder="Ingiza username">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Nenosiri (Password)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input id="password" name="password" type="password" required class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl leading-5 bg-white/80 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-vicoba-500 focus:border-vicoba-500 text-sm" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-vicoba-600 focus:ring-vicoba-500 border-slate-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-slate-600">Niyakumbuke</label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" id="loginBtn" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-vicoba-600 to-emerald-600 hover:from-vicoba-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vicoba-500 transition duration-150 transform active:scale-95">
                            <i class="fa-solid fa-right-to-bracket mr-2 self-center"></i> Ingia Kwenye Mfumo
                        </button>
                    </div>
                </form>

                <div class="mt-6 border-t border-slate-200/60 pt-6 text-center">
                    <p class="text-sm text-slate-600">
                        Kikundi chako bado hakijasajiliwa? 
                        <a href="<?php echo home_url('/register/'); ?>" class="font-bold text-vicoba-700 hover:text-vicoba-800 underline">Sajili Kikundi Kipya</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vicobaLoginForm');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Inaingia...';

        const username = form.username.value;
        const password = form.password.value;

        try {
            const res = await fetch(vicobaData.root + 'vicoba/v1/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Imefanikiwa!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirect_url;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Imeshindikana',
                    text: data.message || 'Jina la mtumiaji au nenosiri si sahihi.',
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-right-to-bracket mr-2"></i> Ingia Kwenye Mfumo';
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Hitilafu',
                text: 'Kuna tatizo la mtandao, jaribu tena.',
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-right-to-bracket mr-2"></i> Ingia Kwenye Mfumo';
        }
    });
});
</script>

<?php get_footer(); ?>
