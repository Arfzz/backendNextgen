@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Pengguna</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="color: #E10000; background: #FFEEEE; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="toolbar">
        <div class="search-box">
            <span class="search-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </span>
            <form action="{{ route('users.index') }}" method="GET">
                <input type="text" name="search" id="search-input" placeholder="Cari pengguna..." value="{{ request('search') }}">
            </form>
        </div>
        <button type="button" class="btn btn-primary" id="btn-tambah" onclick="openCreateModal()">
            Tambah
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
        </button>
    </div>

    <div class="data-card">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Universitas</th>
                            <th>Beasiswa Diikuti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                            @php
                                $roleVal = $user->role->value ?? $user->role;
                                $beasiswaDiampu = is_array($user->beasiswa_diampu) ? $user->beasiswa_diampu : [];
                                $userData = array_merge($user->toArray(), [
                                    'id'              => (string) $user->_id,
                                    'role'            => $roleVal,
                                    'beasiswa_diampu' => $beasiswaDiampu,
                                ]);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span style="background: #F0F9FF; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #0284C7; font-weight: 500;">
                                        {{ ucfirst($roleVal) }}
                                    </span>
                                </td>
                                <td>{{ $user->university ?? '-' }}</td>
                                <td>
                                    @if($roleVal === 'student' && count($beasiswaDiampu) > 0)
                                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                            @foreach($beasiswaDiampu as $b)
                                                <span style="background:#EFF6FF;color:#1D4ED8;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">{{ $b }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color:#94A3B8;font-size:13px;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="action-btn edit" title="Edit"
                                            onclick="openEditModal({{ json_encode($userData) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn delete" title="Hapus"
                                            onclick="openDeleteModal('{{ $user->_id }}', '{{ addslashes($user->name) }}')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                <line x1="14" y1="11" x2="14" y2="17" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <p>Belum ada data pengguna.</p>
                <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Pengguna
                </button>
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div class="modal-overlay" id="create-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4); justify-content: center; align-items: flex-start; overflow-y: auto;">
        <div class="modal-box" style="width: 100%; max-width: 600px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin: 40px auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Tambah Pengguna</h3>
                <button type="button" onclick="closeCreateModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8" style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Role</label>
                    <select name="role" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                        <option value="mentor">Mentor</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Universitas</label>
                    <input type="text" name="university" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal-overlay" id="edit-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4); justify-content: center; align-items: flex-start; overflow-y: auto;">
        <div class="modal-box" style="width: 100%; max-width: 600px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin: 40px auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Edit Pengguna</h3>
                <button type="button" onclick="closeEditModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required
                        style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required
                        style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Password <span style="color:#94A3B8; font-weight:400; font-size:12px;">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" class="form-control" minlength="8"
                        style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Role</label>
                    <select name="role" id="edit_role" class="form-control" required
                        onchange="toggleBeasiswaField(this.value)"
                        style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                        <option value="mentor">Mentor</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Universitas</label>
                    <input type="text" name="university" id="edit_university" class="form-control"
                        style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>

                {{-- Beasiswa Diampu — only shown for student role --}}
                <div id="beasiswa-field" class="form-group" style="margin-bottom: 20px; display: none;">
                    <label style="display:flex; align-items:center; gap:6px; margin-bottom:8px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#1D4ED8;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        Beasiswa yang Diikuti
                    </label>
                    <div style="border: 1px solid rgba(0,0,0,0.1); border-radius: 10px; padding: 12px; background: #F8FAFC; max-height: 220px; overflow-y: auto;">
                        @foreach($pakets as $paket)
                            <label style="display:flex; align-items:center; gap:10px; padding:8px 6px; cursor:pointer; border-radius:6px; transition:background .15s;"
                                onmouseover="this.style.background='#EFF6FF'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox"
                                    name="beasiswa_diampu[]"
                                    value="{{ $paket->nama_beasiswa }}"
                                    class="beasiswa-check"
                                    style="width:16px; height:16px; accent-color:#2563EB; cursor:pointer; flex-shrink:0;">
                                <span style="font-size:13px; font-weight:500; color:#334155;">{{ $paket->nama_beasiswa }}</span>
                            </label>
                        @endforeach
                        @if($pakets->isEmpty())
                            <div style="color:#94A3B8; font-size:13px; text-align:center; padding:8px;">Belum ada paket beasiswa.</div>
                        @endif
                    </div>
                    <div style="margin-top: 6px; font-size:11px; color:#94A3B8;">Centang beasiswa yang sedang diikuti oleh student ini.</div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal-overlay" id="delete-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4);">
        <div class="modal-box" style="width: 100%; max-width: 400px; padding: 24px; border-radius: 16px; text-align: center;">
            <div class="modal-icon" style="font-size: 32px; margin-bottom: 16px;">⚠️</div>
            <h3>Hapus Pengguna?</h3>
            <p style="margin-bottom: 24px; color: #64748B;">Apakah Anda yakin ingin menghapus <strong id="delete-name" style="color: #0F172A;"></strong>?</p>
            <div class="modal-actions" style="display: flex; justify-content: center; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                <form id="delete-form" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="background: #E10000; color: white;">Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // ── Beasiswa field toggle ────────────────────────────────────────────────
    function toggleBeasiswaField(role) {
        const field = document.getElementById('beasiswa-field');
        if (role === 'student') {
            field.style.display = 'block';
        } else {
            field.style.display = 'none';
            // Uncheck all when hidden
            document.querySelectorAll('.beasiswa-check').forEach(cb => cb.checked = false);
        }
    }

    // ── Create Modal ─────────────────────────────────────────────────────────
    function openCreateModal() { document.getElementById('create-modal').style.display = 'flex'; }
    function closeCreateModal() { document.getElementById('create-modal').style.display = 'none'; }

    // ── Edit Modal ────────────────────────────────────────────────────────────
    function openEditModal(data) {
        document.getElementById('edit-form').action = '/users/' + data.id;
        document.getElementById('edit_name').value      = data.name      || '';
        document.getElementById('edit_email').value     = data.email     || '';
        document.getElementById('edit_role').value      = data.role      || 'student';
        document.getElementById('edit_university').value = data.university || '';

        // Handle beasiswa_diampu checkboxes
        const existing = Array.isArray(data.beasiswa_diampu) ? data.beasiswa_diampu : [];
        document.querySelectorAll('.beasiswa-check').forEach(function(cb) {
            cb.checked = existing.includes(cb.value);
        });

        // Show/hide beasiswa section based on role
        toggleBeasiswaField(data.role || 'student');

        document.getElementById('edit-modal').style.display = 'flex';
    }
    function closeEditModal() { document.getElementById('edit-modal').style.display = 'none'; }

    // ── Delete Modal ──────────────────────────────────────────────────────────
    function openDeleteModal(id, name) {
        document.getElementById('delete-name').textContent = name;
        document.getElementById('delete-form').action = '/users/' + id;
        document.getElementById('delete-modal').classList.add('active');
        document.getElementById('delete-modal').style.display = 'flex';
    }
    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.remove('active');
        document.getElementById('delete-modal').style.display = 'none';
    }

    // ── Close on backdrop click ───────────────────────────────────────────────
    window.addEventListener('click', function(e) {
        if (e.target.id === 'delete-modal') closeDeleteModal();
        if (e.target.id === 'create-modal') closeCreateModal();
        if (e.target.id === 'edit-modal')   closeEditModal();
    });

    // ── Close on Escape ───────────────────────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>
@endsection
