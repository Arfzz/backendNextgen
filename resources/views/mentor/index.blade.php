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
                                        <button type="button" class="action-btn view" title="Lihat Detail" onclick="openDetailModal({{ json_encode($mentor) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                                        </button>
                                        <button type="button" class="action-btn edit" title="Edit" onclick="openEditModal({{ json_encode($mentor) }})">
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
        <div class="modal-box" style="width: 100%; max-width: 600px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Detail Mentor</h3>
                <button type="button" onclick="closeDetailModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>
            <div style="margin-bottom: 24px;">
                <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Nama Mentor</div>
                <div id="detail-nama" style="font-size: 18px; font-weight: 600; color: #0F172A;"></div>
            </div>
            <div style="margin-bottom: 24px;">
                <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Pendidikan</div>
                <div id="detail-pendidikan" style="font-size: 16px; color: #334155;"></div>
            </div>
            <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0;">
                <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 12px;">Awardee</div>
                <ul id="detail-awardee" style="padding-left: 20px; margin: 0; color: #334155; font-weight: 500; line-height: 1.6;"></ul>
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
            <form action="{{ route('mentor.store') }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nama_mentor">Nama Mentor</label>
                    <input type="text" name="nama_mentor" id="nama_mentor" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
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
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit_nama_mentor">Nama Mentor</label>
                    <input type="text" name="nama_mentor" id="edit_nama_mentor" class="form-control" required style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;">
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
        // Modal Logic Delete & Detail
        function openDeleteModal(id, name) {
            document.getElementById('delete-name').textContent = name;
            document.getElementById('delete-form').action = '/mentor/' + id;
            document.getElementById('delete-modal').classList.add('active');
        }
        function closeDeleteModal() { document.getElementById('delete-modal').classList.remove('active'); }

        function openDetailModal(data) {
            document.getElementById('detail-nama').textContent = data.nama_mentor;
            document.getElementById('detail-pendidikan').textContent = data.pendidikan;
            
            const awardeeEl = document.getElementById('detail-awardee');
            awardeeEl.innerHTML = '';
            let aw = Array.isArray(data.awardee) ? data.awardee : [data.awardee];
            aw.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                awardeeEl.appendChild(li);
            });
            document.getElementById('detail-modal').classList.add('active');
        }
        function closeDetailModal() { document.getElementById('detail-modal').classList.remove('active'); }

        // Modal Logic Create & Edit
        function openCreateModal() { document.getElementById('create-modal').style.display = 'flex'; }
        function closeCreateModal() { document.getElementById('create-modal').style.display = 'none'; }
        
        function openEditModal(data) {
            document.getElementById('edit-form').action = '/mentor/' + data._id;
            document.getElementById('edit_nama_mentor').value = data.nama_mentor;
            document.getElementById('edit_pendidikan').value = data.pendidikan;
            
            const cont = document.getElementById('edit-awardee-container');
            cont.innerHTML = '';
            let aw = Array.isArray(data.awardee) ? data.awardee : [data.awardee];
            aw.forEach(item => addEditAwardee(item));
            
            document.getElementById('edit-modal').style.display = 'flex';
        }
        function closeEditModal() { document.getElementById('edit-modal').style.display = 'none'; }

        // Arrays input Logic Create
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
            if (container.children.length > 1) {
                btn.parentElement.remove();
                updateCreateAwardeeButtons();
            }
        }
        function updateCreateAwardeeButtons() {
            const container = document.getElementById('create-awardee-container');
            const btns = container.querySelectorAll('button');
            btns.forEach(b => b.style.display = container.children.length > 1 ? 'block' : 'none');
        }

        // Arrays Input Logic Edit
        function addEditAwardee(value = '') {
            const container = document.getElementById('edit-awardee-container');
            const div = document.createElement('div');
            div.className = 'awardee-input-group';
            div.style.display = 'flex';
            div.style.gap = '10px';
            div.style.marginBottom = '10px';
            
            const inpt = document.createElement('input');
            inpt.type = 'text';
            inpt.name = 'awardee[]';
            inpt.className = 'form-control';
            inpt.required = true;
            inpt.value = value;
            inpt.style.width = '100%';
            inpt.style.boxSizing = 'border-box';
            inpt.style.padding = '12px';
            inpt.style.border = '1px solid rgba(0,0,0,0.1)';
            inpt.style.borderRadius = '8px';
            
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = 'Hapus';
            btn.onclick = () => removeEditAwardee(btn);
            btn.style.padding = '10px 15px';
            btn.style.background = '#FFEEEE';
            btn.style.color = '#E10000';
            btn.style.border = 'none';
            btn.style.borderRadius = '8px';
            btn.style.cursor = 'pointer';
            
            div.appendChild(inpt);
            div.appendChild(btn);
            container.appendChild(div);
            updateEditAwardeeButtons();
        }
        function removeEditAwardee(btn) {
            const container = document.getElementById('edit-awardee-container');
            if (container.children.length > 1) {
                btn.parentElement.remove();
                updateEditAwardeeButtons();
            }
        }
        function updateEditAwardeeButtons() {
            const container = document.getElementById('edit-awardee-container');
            const btns = container.querySelectorAll('button');
            btns.forEach(b => b.style.display = container.children.length > 1 ? 'block' : 'none');
        }

        // Esc & Click Outside to Close active modals
        window.addEventListener('click', function(e) {
            ['delete-modal', 'detail-modal'].forEach(id => {
                const el = document.getElementById(id);
                if(e.target === el) el.classList.remove('active');
            });
            ['create-modal', 'edit-modal'].forEach(id => {
                const el = document.getElementById(id);
                if(e.target === el) el.style.display = 'none';
            });
        });
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                ['delete-modal', 'detail-modal'].forEach(id => document.getElementById(id).classList.remove('active'));
                ['create-modal', 'edit-modal'].forEach(id => document.getElementById(id).style.display = 'none');
            }
        });
    </script>
@endsection
