@extends('layouts.admin')

@section('title', 'Mentor')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Mentor</h1>
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
            <form action="{{ route('mentor.index') }}" method="GET">
                <input type="text" name="search" id="search-input" placeholder="Cari mentor..."
                    value="{{ $search ?? '' }}">
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
        @if($mentors->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mentor</th>
                            <th>Email</th>
                            <th>Pendidikan</th>
                            <th>Awardee</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mentors as $index => $mentor)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $mentor->nama_mentor }}</td>
                                <td>{{ $mentor->email ?? '-' }}</td>
                                <td>{{ $mentor->pendidikan }}</td>
                                <td>
                                    @if(is_array($mentor->awardee))
                                        {{ count($mentor->awardee) }} Awardee
                                    @else
                                        {{ $mentor->awardee }}
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="action-btn view" title="Lihat Detail" onclick="openDetailModal({{ json_encode(array_merge($mentor->toArray(), ['id' => (string)$mentor->_id, 'rating' => $mentor->rating ?? 5.0])) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                                        </button>
                                        <button type="button" class="action-btn edit" title="Edit" onclick="openEditModal({{ json_encode(array_merge($mentor->toArray(), ['id' => (string)$mentor->_id])) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                                        </button>
                                        <button type="button" class="action-btn delete" title="Hapus"
                                            onclick="openDeleteModal('{{ $mentor->_id }}', '{{ addslashes($mentor->nama_mentor) }}')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /></svg>
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
                <p>Belum ada data mentor.</p>
                <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Mentor
                </button>
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <div class="modal-overlay" id="detail-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4);">
        <div class="modal-box" style="width: 100%; max-width: 600px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Detail Mentor</h3>
                <button type="button" onclick="closeDetailModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>
            <div style="text-align: center; margin-bottom: 24px;">
                <img id="detail-profile-pic" src="{{ asset('images/default-avatar.png') }}" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #E2E8F0;">
                <div id="detail-rating" style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 12px;"></div>
            </div>
            <div style="margin-bottom: 24px;">
                <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Nama Mentor</div>
                <div id="detail-nama" style="font-size: 18px; font-weight: 600; color: #0F172A;"></div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Username</div>
                    <div id="detail-username" style="font-size: 16px; color: #334155;"></div>
                </div>
                <div>
                    <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Email</div>
                    <div id="detail-email" style="font-size: 16px; color: #334155;"></div>
                </div>
            </div>
            <div style="margin-bottom: 24px;">
                <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Pendidikan</div>
                <div id="detail-pendidikan" style="font-size: 16px; color: #334155;"></div>
            </div>
            <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; margin-bottom: 16px;">
                <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 12px;">Awardee</div>
                <ul id="detail-awardee" style="padding-left: 20px; margin: 0; color: #334155; font-weight: 500; line-height: 1.6;"></ul>
            </div>
            <div style="background: #F0F9FF; padding: 20px; border-radius: 12px; border: 1px solid #BAE6FD;">
                <div style="color: #0369A1; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 12px;">Beasiswa Diampu</div>
                <ul id="detail-beasiswa" style="padding-left: 20px; margin: 0; color: #0C4A6E; font-weight: 500; line-height: 1.6;"></ul>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal-overlay" id="create-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4); justify-content: center; align-items: center; overflow-y: auto;">
        <div class="modal-box" style="width: 100%; max-width: 600px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin: 40px auto; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Tambah Mentor</h3>
                <button type="button" onclick="closeCreateModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>
            <form action="{{ route('mentor.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- Profile Picture --}}
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="position: relative; display: inline-block;">
                        <img id="create-pic-preview" src="{{ asset('images/default-avatar.png') }}" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #E2E8F0; cursor: pointer;" onclick="document.getElementById('create_profile_picture').click()">
                        <div onclick="document.getElementById('create_profile_picture').click()" style="position: absolute; bottom: 0; right: 0; background: #2563EB; color: #fff; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; border: 2px solid #fff;">+</div>
                    </div>
                    <input type="file" name="profile_picture" id="create_profile_picture" accept="image/*" style="display:none;" onchange="previewImage(this, 'create-pic-preview')">
                    <div style="color: #94A3B8; font-size: 12px; margin-top: 6px;">Klik untuk upload foto</div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nama_mentor">Nama Mentor</label>
                    <input type="text" name="nama_mentor" id="nama_mentor" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                {{-- Kredensial Login --}}
                <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; margin-bottom: 20px;">
                    <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 12px;">Kredensial Login</div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="create_username">Username</label>
                        <input type="text" name="username" id="create_username" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="create_email">Email</label>
                        <input type="email" name="email" id="create_email" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="create_password">Password</label>
                        <input type="password" name="password" id="create_password" class="form-control" required minlength="8" style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                        <div style="color: #94A3B8; font-size: 12px; margin-top: 4px;">Minimal 8 karakter</div>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="pendidikan">Pendidikan</label>
                    <input type="text" name="pendidikan" id="pendidikan" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Awardee</label>
                    <div id="create-awardee-container">
                        <div class="awardee-input-group" style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="text" name="awardee[]" class="form-control" placeholder="Beasiswa A" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                            <button type="button" onclick="removeCreateAwardee(this)" style="padding: 10px 15px; background: #FFEEEE; color: #E10000; border: none; border-radius: 8px; cursor: pointer; display: none;">Hapus</button>
                        </div>
                    </div>
                    <button type="button" onclick="addCreateAwardee()" class="btn btn-secondary" style="font-size: 13px; padding: 6px 12px; margin-top: 8px;">+ Tambah Awardee</button>
                </div>
                {{-- Beasiswa Diampu --}}
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Beasiswa yang Diampu</label>
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; max-height: 160px; overflow-y: auto;">
                        @forelse($paketBeasiswa as $pb)
                            <label style="display: flex; align-items: center; gap: 8px; padding: 6px 0; cursor: pointer; font-weight: 400;">
                                <input type="checkbox" name="beasiswa_diampu[]" value="{{ $pb->nama_beasiswa }}" style="width: 16px; height: 16px; accent-color: #2563EB;">
                                {{ $pb->nama_beasiswa }}
                            </label>
                        @empty
                            <p style="color:#94A3B8; margin:0; font-size: 13px;">Belum ada data paket beasiswa.</p>
                        @endforelse
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal-overlay" id="edit-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4); justify-content: center; align-items: center; overflow-y: auto;">
        <div class="modal-box" style="width: 100%; max-width: 600px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin: 40px auto; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Edit Mentor</h3>
                <button type="button" onclick="closeEditModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>
            <form id="edit-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                {{-- Profile Picture --}}
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="position: relative; display: inline-block;">
                        <img id="edit-pic-preview" src="{{ asset('images/default-avatar.png') }}" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #E2E8F0; cursor: pointer;" onclick="document.getElementById('edit_profile_picture').click()">
                        <div onclick="document.getElementById('edit_profile_picture').click()" style="position: absolute; bottom: 0; right: 0; background: #2563EB; color: #fff; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; border: 2px solid #fff;">✎</div>
                    </div>
                    <input type="file" name="profile_picture" id="edit_profile_picture" accept="image/*" style="display:none;" onchange="previewImage(this, 'edit-pic-preview')">
                    <div style="color: #94A3B8; font-size: 12px; margin-top: 6px;">Klik untuk ganti foto</div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit_nama_mentor">Nama Mentor</label>
                    <input type="text" name="nama_mentor" id="edit_nama_mentor" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                {{-- Kredensial Login --}}
                <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; margin-bottom: 20px;">
                    <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 12px;">Kredensial Login</div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="edit_username">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="edit_email">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_password">Password <span style="color:#94A3B8; font-weight:400;">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" id="edit_password" class="form-control" minlength="8" style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit_pendidikan">Pendidikan</label>
                    <input type="text" name="pendidikan" id="edit_pendidikan" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Awardee</label>
                    <div id="edit-awardee-container"></div>
                    <button type="button" onclick="addEditAwardee()" class="btn btn-secondary" style="font-size: 13px; padding: 6px 12px; margin-top: 8px;">+ Tambah Awardee</button>
                </div>
                {{-- Beasiswa Diampu --}}
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Beasiswa yang Diampu</label>
                    <div id="edit-beasiswa-container" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; max-height: 160px; overflow-y: auto;">
                        @forelse($paketBeasiswa as $pb)
                            <label class="edit-beasiswa-item" style="display: flex; align-items: center; gap: 8px; padding: 6px 0; cursor: pointer; font-weight: 400;">
                                <input type="checkbox" name="beasiswa_diampu[]" value="{{ $pb->nama_beasiswa }}" style="width: 16px; height: 16px; accent-color: #2563EB;">
                                {{ $pb->nama_beasiswa }}
                            </label>
                        @empty
                            <p style="color:#94A3B8; margin:0; font-size: 13px;">Belum ada data paket beasiswa.</p>
                        @endforelse
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal-overlay" id="delete-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4);">
        <div class="modal-box" style="width: 100%; max-width: 400px;">
            <div class="modal-icon" style="font-size: 32px; margin-bottom: 16px;">⚠️</div>
            <h3>Hapus Mentor?</h3>
            <p>Apakah Anda yakin ingin menghapus <strong id="delete-name"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-actions" style="margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                <form id="delete-form" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const defaultAvatar = '{{ asset("images/default-avatar.png") }}';
        const storageBase   = '{{ asset("storage") }}/';

        // Image Preview
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById(previewId).src = e.target.result;
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Resolve profile picture URL safely
        function resolvePhoto(path) {
            if (!path) return defaultAvatar;
            if (path.startsWith('http')) return path;
            return storageBase + path;
        }

        // ── Delete Modal ────────────────────────────────────────────────
        function openDeleteModal(id, name) {
            document.getElementById('delete-name').textContent = name;
            document.getElementById('delete-form').action = '/mentor/' + id;
            document.getElementById('delete-modal').classList.add('active');
        }
        function closeDeleteModal() { document.getElementById('delete-modal').classList.remove('active'); }

        // ── Detail Modal ────────────────────────────────────────────────
        function openDetailModal(data) {
            document.getElementById('detail-profile-pic').src = resolvePhoto(data.profile_picture);

            // Rating stars
            const ratingEl = document.getElementById('detail-rating');
            const rating = parseFloat(data.rating) || 5.0;
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                starsHtml += `<svg width="18" height="18" viewBox="0 0 24 24" fill="${i <= Math.round(rating) ? '#F2BC45' : 'none'}" stroke="#F2BC45" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
            }
            starsHtml += `<span style="font-weight:700;color:#0F172A;font-size:16px;margin-left:4px;">${rating.toFixed(1)}</span>`;
            ratingEl.innerHTML = starsHtml;

            document.getElementById('detail-nama').textContent      = data.nama_mentor  || '-';
            document.getElementById('detail-username').textContent  = data.username     || '-';
            document.getElementById('detail-email').textContent     = data.email        || '-';
            document.getElementById('detail-pendidikan').textContent = data.pendidikan  || '-';

            const awardeeEl = document.getElementById('detail-awardee');
            awardeeEl.innerHTML = '';
            (Array.isArray(data.awardee) ? data.awardee : [data.awardee]).forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                awardeeEl.appendChild(li);
            });

            const beasiswaEl = document.getElementById('detail-beasiswa');
            beasiswaEl.innerHTML = '';
            const bw = Array.isArray(data.beasiswa_diampu) ? data.beasiswa_diampu
                       : (data.beasiswa_diampu ? [data.beasiswa_diampu] : []);
            if (bw.length === 0) {
                beasiswaEl.innerHTML = '<li style="color:#94A3B8;">Belum ada beasiswa</li>';
            } else {
                bw.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    beasiswaEl.appendChild(li);
                });
            }

            document.getElementById('detail-modal').classList.add('active');
        }
        function closeDetailModal() { document.getElementById('detail-modal').classList.remove('active'); }

        // ── Create Modal ────────────────────────────────────────────────
        function openCreateModal() { document.getElementById('create-modal').style.display = 'flex'; }
        function closeCreateModal() { document.getElementById('create-modal').style.display = 'none'; }

        // ── Edit Modal ──────────────────────────────────────────────────
        function openEditModal(data) {
            // Use explicit string id injected by PHP (data.id), NOT data._id which is an object
            document.getElementById('edit-form').action = '/mentor/' + data.id;

            document.getElementById('edit_nama_mentor').value = data.nama_mentor  || '';
            document.getElementById('edit_pendidikan').value  = data.pendidikan   || '';
            document.getElementById('edit_username').value    = data.username     || '';
            document.getElementById('edit_email').value       = data.email        || '';
            document.getElementById('edit_password').value    = '';

            // Profile picture
            document.getElementById('edit-pic-preview').src = resolvePhoto(data.profile_picture);

            // Awardee dynamic inputs
            const cont = document.getElementById('edit-awardee-container');
            cont.innerHTML = '';
            (Array.isArray(data.awardee) ? data.awardee : [data.awardee]).forEach(item => addEditAwardee(item));

            // Beasiswa diampu — tick matching checkboxes
            const bw = Array.isArray(data.beasiswa_diampu) ? data.beasiswa_diampu
                       : (data.beasiswa_diampu ? [data.beasiswa_diampu] : []);
            document.querySelectorAll('#edit-beasiswa-container input[type="checkbox"]').forEach(cb => {
                cb.checked = bw.includes(cb.value);
            });

            document.getElementById('edit-modal').style.display = 'flex';
        }
        function closeEditModal() { document.getElementById('edit-modal').style.display = 'none'; }

        // ── Create Awardee ──────────────────────────────────────────────
        function addCreateAwardee() {
            const container = document.getElementById('create-awardee-container');
            const clone = container.children[0].cloneNode(true);
            clone.querySelector('input').value = '';
            clone.querySelector('button').style.display = 'block';
            container.appendChild(clone);
            updateCreateAwardeeButtons();
        }
        function removeCreateAwardee(btn) {
            const container = document.getElementById('create-awardee-container');
            if (container.children.length > 1) { btn.parentElement.remove(); updateCreateAwardeeButtons(); }
        }
        function updateCreateAwardeeButtons() {
            const container = document.getElementById('create-awardee-container');
            container.querySelectorAll('button').forEach(b =>
                b.style.display = container.children.length > 1 ? 'block' : 'none');
        }

        // ── Edit Awardee ────────────────────────────────────────────────
        function addEditAwardee(value = '') {
            const container = document.getElementById('edit-awardee-container');
            const div = document.createElement('div');
            div.style.cssText = 'display:flex;gap:10px;margin-bottom:10px;';

            const inpt = document.createElement('input');
            inpt.type = 'text'; inpt.name = 'awardee[]'; inpt.className = 'form-control';
            inpt.value = value;
            inpt.style.cssText = 'width:100%;box-sizing:border-box;padding:12px;border:1px solid rgba(0,0,0,0.1);border-radius:8px;';

            const btn = document.createElement('button');
            btn.type = 'button'; btn.textContent = 'Hapus';
            btn.onclick = () => removeEditAwardee(btn);
            btn.style.cssText = 'padding:10px 15px;background:#FFEEEE;color:#E10000;border:none;border-radius:8px;cursor:pointer;';

            div.appendChild(inpt); div.appendChild(btn);
            container.appendChild(div);
            updateEditAwardeeButtons();
        }
        function removeEditAwardee(btn) {
            const c = document.getElementById('edit-awardee-container');
            if (c.children.length > 1) { btn.parentElement.remove(); updateEditAwardeeButtons(); }
        }
        function updateEditAwardeeButtons() {
            const c = document.getElementById('edit-awardee-container');
            c.querySelectorAll('button').forEach(b => b.style.display = c.children.length > 1 ? 'block' : 'none');
        }

        // ── Close on backdrop click / Esc ───────────────────────────────
        window.addEventListener('click', function(e) {
            ['delete-modal', 'detail-modal'].forEach(id => {
                const el = document.getElementById(id);
                if (e.target === el) el.classList.remove('active');
            });
            ['create-modal', 'edit-modal'].forEach(id => {
                const el = document.getElementById(id);
                if (e.target === el) el.style.display = 'none';
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['delete-modal','detail-modal'].forEach(id => document.getElementById(id).classList.remove('active'));
                ['create-modal','edit-modal'].forEach(id => document.getElementById(id).style.display = 'none');
            }
        });
    </script>
@endsection

