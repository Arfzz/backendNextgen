<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Testimoni - NextGen Community</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#132440 0%,#016178 60%,#0891B2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 20px}
        .wrap{width:100%;max-width:560px}
        .card{background:white;border-radius:24px;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);overflow:hidden;animation:up .5s ease}
        @keyframes up{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .header{background:linear-gradient(135deg,#132440,#016178);padding:36px 32px;text-align:center;color:white}
        .header-icon{width:60px;height:60px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px}
        .header h1{font-size:24px;font-weight:700;margin-bottom:6px}
        .header p{font-size:13px;opacity:.8;line-height:1.6}
        .body{padding:32px}
        .step{display:none}.step.active{display:block}
        .form-group{margin-bottom:20px}
        label{display:block;font-weight:600;font-size:13px;color:#464646;margin-bottom:6px}
        label span{color:#E10000}
        .fc{width:100%;padding:12px 16px;border:2px solid #E2E8F0;border-radius:12px;font-family:'Poppins',sans-serif;font-size:14px;color:#464646;outline:none;background:#FAFBFF;transition:.2s}
        .fc:focus{border-color:#016178;box-shadow:0 0 0 4px rgba(1,97,120,.1);background:white}
        textarea.fc{resize:vertical;min-height:110px;line-height:1.6}
        .alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;display:none}
        .alert-error{background:#FFEEEE;color:#E10000}
        .alert-info{background:#E7F9FF;color:#0369A1}
        .btn{width:100%;height:52px;border:none;border-radius:14px;background:linear-gradient(135deg,#132440,#016178);color:white;font-family:'Poppins',sans-serif;font-size:15px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.3s}
        .btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(1,97,120,.4)}
        .btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
        /* Mentor card */
        .mentor-info{background:#F0F9FF;border:1px solid #BAE6FD;border-radius:14px;padding:18px;margin-bottom:20px;display:flex;align-items:center;gap:14px}
        .mentor-avatar{width:50px;height:50px;background:linear-gradient(135deg,#132440,#016178);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:20px;flex-shrink:0}
        .mentor-name{font-weight:700;color:#0C4A6E;font-size:16px}
        .mentor-class{font-size:12px;color:#0369A1;margin-top:2px}
        /* Class selector */
        .class-list{display:flex;flex-direction:column;gap:10px;margin-bottom:20px}
        .class-item{padding:14px 18px;border:2px solid #E2E8F0;border-radius:12px;cursor:pointer;transition:.2s}
        .class-item:hover{border-color:#016178;background:#F0F9FF}
        .class-item.selected{border-color:#016178;background:#E7F9FF}
        .class-item-name{font-weight:600;color:#0F172A;font-size:14px}
        .class-item-mentor{font-size:12px;color:#64748B;margin-top:2px}
        /* Star rating */
        .stars-wrap{background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:20px;text-align:center;margin-bottom:20px}
        .stars-wrap .lbl{font-weight:600;font-size:14px;color:#464646;margin-bottom:14px}
        .star-row{display:flex;flex-direction:row-reverse;justify-content:center;gap:6px}
        .star-row input{display:none}
        .star-row label{cursor:pointer;font-size:0}
        .star-row label svg{width:44px;height:44px;fill:#E2E8F0;stroke:#CBD5E1;stroke-width:1;transition:.2s}
        .star-row label:hover svg,.star-row label:hover~label svg{fill:#F2BC45;stroke:#D4A017;transform:scale(1.15)}
        .star-row input:checked~label svg{fill:#F2BC45;stroke:#D4A017}
        .star-val{margin-top:10px;font-size:22px;font-weight:700;color:#132440}
        .star-txt{font-size:12px;color:#94A3B8;margin-top:3px}
        /* User badge */
        .user-badge{background:#E7FFF0;border:1px solid #BBF7D0;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#0F8B42;font-weight:500}
        /* Char count */
        .char-count{text-align:right;font-size:11px;color:#94A3B8;margin-top:4px}
        /* Success */
        .success-overlay{display:none;position:fixed;inset:0;background:rgba(19,36,64,.6);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center}
        .success-overlay.active{display:flex}
        .success-box{background:white;border-radius:24px;padding:44px 36px;text-align:center;max-width:380px;width:90%;animation:up .4s ease}
        .success-icon{width:72px;height:72px;background:linear-gradient(135deg,#E7FFF0,#DCFCE7);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:32px}
        .success-box h2{font-size:20px;color:#0F172A;margin-bottom:10px}
        .success-box p{font-size:13px;color:#64748B;line-height:1.6;margin-bottom:20px}
        .btn-sm{padding:12px 28px;border:none;border-radius:12px;background:linear-gradient(135deg,#132440,#016178);color:white;font-family:'Poppins',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:.2s}
        .btn-sm:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(1,97,120,.3)}
        .back-link{display:inline-flex;align-items:center;gap:8px;color:rgba(255,255,255,.7);font-size:13px;text-decoration:none;margin-bottom:18px;transition:.2s}
        .back-link:hover{color:white}
        @keyframes spin{to{transform:rotate(360deg)}}
        .spinner{display:inline-block;width:18px;height:18px;border:2px solid white;border-top-color:transparent;border-radius:50%;animation:spin .6s linear infinite}
        @media(max-width:480px){.body{padding:24px}.star-row label svg{width:36px;height:36px}}
    </style>
</head>
<body>
<div class="wrap">
    <a href="/" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali ke Beranda
    </a>

    <div class="card">
        <div class="header">
            <div class="header-icon" id="header-icon">🔐</div>
            <h1 id="header-title">Login Peserta</h1>
            <p id="header-desc">Masukkan email dan password akun NextGen Anda<br>untuk melanjutkan pengisian testimoni.</p>
        </div>
        <div class="body">

            {{-- STEP 1: Login --}}
            <div class="step active" id="step-login">
                <div class="alert alert-error" id="err-login"></div>
                <div class="form-group">
                    <label for="email">Email <span>*</span></label>
                    <input type="email" id="email" class="fc" placeholder="email@contoh.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password <span>*</span></label>
                    <input type="password" id="password" class="fc" placeholder="Masukkan password" required>
                </div>
                <button class="btn" id="btn-login" onclick="doLogin()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Masuk
                </button>
            </div>

            {{-- STEP 2: Pilih Kelas (hanya jika > 1 kelas) --}}
            <div class="step" id="step-class">
                <div class="alert alert-info" id="info-class">Anda terdaftar di beberapa kelas. Pilih mentor yang ingin Anda beri testimoni:</div>
                <div class="class-list" id="class-list"></div>
                <button class="btn" onclick="goToForm()">Lanjutkan</button>
            </div>

            {{-- STEP 3: Isi Testimoni --}}
            <div class="step" id="step-form">
                <div class="user-badge" id="user-badge"></div>
                <div class="mentor-info" id="mentor-info">
                    <div class="mentor-avatar">👨‍🏫</div>
                    <div>
                        <div class="mentor-name" id="mentor-name"></div>
                        <div class="mentor-class" id="mentor-class-name"></div>
                    </div>
                </div>
                <div class="alert alert-error" id="err-form"></div>

                <div class="stars-wrap">
                    <div class="lbl">Beri Rating untuk Mentor</div>
                    <div class="star-row" id="star-row">
                        <input type="radio" name="rating" id="s5" value="5"><label for="s5"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></label>
                        <input type="radio" name="rating" id="s4" value="4"><label for="s4"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></label>
                        <input type="radio" name="rating" id="s3" value="3"><label for="s3"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></label>
                        <input type="radio" name="rating" id="s2" value="2"><label for="s2"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></label>
                        <input type="radio" name="rating" id="s1" value="1"><label for="s1"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></label>
                    </div>
                    <div class="star-val" id="star-val">0 / 5</div>
                    <div class="star-txt" id="star-txt">Klik bintang untuk memberi rating</div>
                </div>

                <div class="form-group">
                    <label for="content">Isi Testimoni <span>*</span></label>
                    <textarea id="content" class="fc" placeholder="Ceritakan pengalaman Anda bersama mentor..." minlength="10" maxlength="1000"></textarea>
                    <div class="char-count"><span id="char-count">0</span> / 1000</div>
                </div>

                <button class="btn" id="btn-submit" onclick="doSubmit()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Kirim Testimoni
                </button>
            </div>

        </div>
    </div>
</div>

<div class="success-overlay" id="success-overlay">
    <div class="success-box">
        <div class="success-icon">✅</div>
        <h2>Terima Kasih!</h2>
        <p>Testimoni Anda berhasil dikirim dan sedang menunggu review dari admin. Terima kasih atas kontribusi Anda!</p>
        <button class="btn-sm" onclick="location.reload()">Kembali</button>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
let userData   = null;
let classList  = [];
let selected   = null; // { class_id, mentor_id, mentor_name, class_name }

const ratingTexts = {1:'😞 Kurang Baik',2:'😐 Cukup',3:'🙂 Baik',4:'😊 Sangat Baik',5:'🤩 Luar Biasa!'};

// ─── Star rating ───────────────────────────────────────────────
document.querySelectorAll('.star-row input').forEach(inp => {
    inp.addEventListener('change', () => {
        const v = parseInt(inp.value);
        document.getElementById('star-val').textContent = v + ' / 5';
        document.getElementById('star-txt').textContent = ratingTexts[v];
    });
});

// ─── Char count ────────────────────────────────────────────────
document.getElementById('content').addEventListener('input', function() {
    document.getElementById('char-count').textContent = this.value.length;
});

// ─── Helpers ───────────────────────────────────────────────────
function setHeader(icon, title, desc) {
    document.getElementById('header-icon').textContent  = icon;
    document.getElementById('header-title').textContent = title;
    document.getElementById('header-desc').textContent  = desc;
}
function showStep(id) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}
function showErr(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.style.display = 'block';
}
function hideErr(id) { document.getElementById(id).style.display = 'none'; }
function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    btn.disabled = loading;
    if (loading) {
        btn.dataset.orig = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Memproses...';
    } else {
        btn.innerHTML = btn.dataset.orig;
    }
}

// ─── Step 1: Login ─────────────────────────────────────────────
async function doLogin() {
    hideErr('err-login');
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    if (!email || !password) { showErr('err-login', 'Email dan password wajib diisi.'); return; }

    setLoading('btn-login', true);
    try {
        const res  = await fetch('/testimonial/login', {
            method: 'POST',
            headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({email, password}),
        });
        const data = await res.json();
        if (!data.success) { showErr('err-login', data.message); return; }

        userData  = data.user;
        classList = data.classes;

        if (classList.length === 1) {
            // Only one class → skip selection step
            selected = classList[0];
            showFormStep();
        } else {
            // Multiple classes → show selection
            buildClassList();
            setHeader('📚', 'Pilih Kelas', 'Pilih kelas yang ingin Anda beri testimoni.');
            showStep('step-class');
        }
    } catch {
        showErr('err-login', 'Gagal terhubung ke server. Coba lagi.');
    } finally {
        setLoading('btn-login', false);
    }
}

// ─── Step 2: Pick class ────────────────────────────────────────
function buildClassList() {
    const list = document.getElementById('class-list');
    list.innerHTML = '';
    classList.forEach((c, i) => {
        const el = document.createElement('div');
        el.className = 'class-item' + (i === 0 ? ' selected' : '');
        el.dataset.index = i;
        el.innerHTML = `<div class="class-item-name">${c.class_name}</div>
                        <div class="class-item-mentor">👨‍🏫 ${c.mentor_name}</div>`;
        el.addEventListener('click', () => {
            document.querySelectorAll('.class-item').forEach(x => x.classList.remove('selected'));
            el.classList.add('selected');
            selected = classList[i];
        });
        list.appendChild(el);
    });
    selected = classList[0];
}

function goToForm() {
    if (!selected) { showErr('err-login', 'Pilih kelas terlebih dahulu.'); return; }
    showFormStep();
}

// ─── Step 3: Form ──────────────────────────────────────────────
function showFormStep() {
    document.getElementById('user-badge').textContent = `👋 Halo, ${userData.name}! Anda sedang memberi testimoni sebagai peserta.`;
    document.getElementById('mentor-name').textContent      = selected.mentor_name;
    document.getElementById('mentor-class-name').textContent = '📚 ' + selected.class_name;
    setHeader('⭐', 'Isi Testimoni', `Berikan penilaian untuk ${selected.mentor_name}.`);
    showStep('step-form');
}

// ─── Step 3: Submit ────────────────────────────────────────────
async function doSubmit() {
    hideErr('err-form');
    const ratingEl = document.querySelector('.star-row input:checked');
    const content  = document.getElementById('content').value.trim();

    if (!ratingEl) { showErr('err-form', 'Silakan berikan rating terlebih dahulu.'); return; }
    if (content.length < 10) { showErr('err-form', 'Testimoni minimal 10 karakter.'); return; }

    setLoading('btn-submit', true);
    try {
        const res  = await fetch('/testimonial/submit', {
            method: 'POST',
            headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({
                user_id:   userData.id,
                mentor_id: selected.mentor_id,
                rating:    parseInt(ratingEl.value),
                content:   content,
            }),
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('success-overlay').classList.add('active');
        } else {
            showErr('err-form', data.message);
        }
    } catch {
        showErr('err-form', 'Gagal mengirim testimoni. Coba lagi.');
    } finally {
        setLoading('btn-submit', false);
    }
}

// Allow Enter key on login fields
document.getElementById('password').addEventListener('keydown', e => {
    if (e.key === 'Enter') doLogin();
});
</script>
</body>
</html>
