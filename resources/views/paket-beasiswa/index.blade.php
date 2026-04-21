@extends('layouts.admin')

@section('title', 'Paket Beasiswa')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Paket Beasiswa</h1>
    </div>


    <div class="toolbar">
        <div class="search-box">
            <span class="search-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </span>
            <form action="{{ route('paket-beasiswa.index') }}" method="GET">
                <input type="text" name="search" id="search-input" placeholder="Cari paket beasiswa..."
                    value="{{ $search ?? '' }}">
            </form>
        </div>
        <a href="{{ route('paket-beasiswa.create') }}" class="btn btn-primary" id="btn-tambah">
            Tambah
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
        </a>
    </div>

    <div class="data-card">
        @if($paketBeasiswas->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Beasiswa</th>
                            <th class="col-fase">Fase Checkpoint</th>
                            <th class="col-persyaratan">Persyaratan</th>
                            <th>Deadline</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paketBeasiswas as $index => $paket)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $paket->nama_beasiswa }}</td>
                                <td class="col-fase">
                                    {{ is_array($paket->fase_checkpoint) ? count($paket->fase_checkpoint) : ($paket->fase_checkpoint > 0 ? $paket->fase_checkpoint : 0) }} Fase
                                </td>
                                <td class="col-persyaratan">
                                    {{ is_array($paket->persyaratan) ? count($paket->persyaratan) : ($paket->persyaratan > 0 ? $paket->persyaratan : 0) }} Syarat
                                </td>
                                <td>{{ $paket->deadline ? $paket->deadline->format('d M Y') : '-' }}</td>
                                <td class="col-harga">Rp {{ number_format($paket->harga, 0, ',', '.') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="action-btn view" title="Lihat Detail" id="view-{{ $paket->_id }}" onclick="openDetailModal({{ json_encode($paket) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('paket-beasiswa.edit', $paket->_id) }}" class="action-btn edit"
                                            title="Edit" id="edit-{{ $paket->_id }}">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>
                                        <button type="button" class="action-btn delete" title="Hapus" id="delete-{{ $paket->_id }}"
                                            onclick="openDeleteModal('{{ $paket->_id }}', '{{ $paket->nama_beasiswa }}')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
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
                <div class="empty-icon">📋</div>
                <p>Belum ada data paket beasiswa.</p>
                <a href="{{ route('paket-beasiswa.create') }}" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Paket Beasiswa
                </a>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal-overlay" id="delete-modal">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <h3>Hapus Paket Beasiswa?</h3>
            <p>Apakah Anda yakin ingin menghapus <strong id="delete-name"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                <form id="delete-form" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
    {{-- Detail Modal --}}
    <div class="modal-overlay" id="detail-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4);">
        <div class="modal-box" style="width: 100%; max-width: 800px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 26px; color: #0F172A; font-weight: 700; letter-spacing: -0.5px;">Detail Beasiswa</h3>
                <button type="button" onclick="closeDetailModal()" style="background: #F1F5F9; border:none; width: 36px; height: 36px; border-radius: 50%; font-size: 20px; cursor: pointer; color: #64748B; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'">&times;</button>
            </div>
            
            <div style="background: linear-gradient(145deg, #F8FAFC, #F1F5F9); padding: 24px; border-radius: 12px; margin-bottom: 24px; display: grid; grid-template-columns: 1fr 1fr auto; gap: 24px; border: 1px solid #E2E8F0; align-items: center;">
                <div>
                    <div style="color: #64748B; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600;">Nama Beasiswa</div>
                    <div id="detail-nama" style="font-weight: 600; color: #0F172A; margin-bottom: 20px; font-size: 18px;"></div>
                    
                    <div style="color: #64748B; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600;">Deadline</div>
                    <div id="detail-deadline" style="font-weight: 600; color: #E11D48; font-size: 16px;"></div>
                </div>
                <div>
                    <div style="color: #64748B; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600;">Harga</div>
                    <div id="detail-harga" style="font-weight: 700; color: #059669; margin-bottom: 20px; font-size: 18px;"></div>
                    
                    <div style="color: #64748B; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600;">Situs Web</div>
                    <a id="detail-url" href="#" target="_blank" style="font-weight: 600; color: #2563EB; font-size: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                        <span id="detail-url-text"></span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
                <div>
                    <div id="detail-gambar-container" style="width: 140px; height: 140px; background: #FFFFFF; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #94A3B8; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid #E2E8F0;">
                        <img id="detail-gambar" src="" alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <div id="detail-no-gambar" style="text-align: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 8px; opacity: 0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <div style="font-size: 12px; font-weight: 500;">No Image</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div style="background: #FFFFFF; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <div style="background: #DBEAFE; color: #1D4ED8; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                        </div>
                        <div style="color: #0F172A; font-size: 16px; font-weight: 600;">Benefit</div>
                    </div>
                    <ul id="detail-benefit" style="padding-left: 24px; margin: 0; color: #334155; font-weight: 500; font-size: 14px; line-height: 1.6;"></ul>
                </div>
                <div style="background: #FFFFFF; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <div style="background: #FEF3C7; color: #D97706; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div style="color: #0F172A; font-size: 16px; font-weight: 600;">Fase Checkpoint</div>
                    </div>
                    <ul id="detail-fase" style="padding-left: 24px; margin: 0; color: #334155; font-weight: 500; font-size: 14px; line-height: 1.6;"></ul>
                </div>
            </div>

            <div style="background: #FFFFFF; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <div style="background: #FCE7F3; color: #BE185D; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div style="color: #0F172A; font-size: 16px; font-weight: 600;">Persyaratan</div>
                </div>
                <ul id="detail-persyaratan" style="padding-left: 24px; margin: 0; color: #334155; font-weight: 500; font-size: 14px; line-height: 1.6;"></ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openDeleteModal(id, name) {
            document.getElementById('delete-name').textContent = name;
            document.getElementById('delete-form').action = '/paket-beasiswa/' + id;
            document.getElementById('delete-modal').classList.add('active');
        }

        function openDetailModal(data) {
            document.getElementById('detail-nama').textContent = data.nama_beasiswa || '-';
            
            // Format deadline
            let deadlineText = '-';
            if(data.deadline) {
                const d = new Date(data.deadline);
                deadlineText = d.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
            }
            document.getElementById('detail-deadline').textContent = deadlineText;
            
            // Format currency
            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
            document.getElementById('detail-harga').textContent = data.harga ? formatter.format(data.harga) : 'Rp 0';
            
            // Format URL
            const urlEl = document.getElementById('detail-url');
            const urlText = document.getElementById('detail-url-text');
            if(data.url) {
                urlEl.href = data.url;
                urlEl.style.display = 'inline-flex';
                urlText.textContent = data.url.replace(/^https?:\/\//, '').split('/')[0]; // clean hostname
            } else {
                urlEl.removeAttribute('href');
                urlEl.style.display = 'none';
                urlText.textContent = '-';
            }

            // Format Gambar
            const imgEl = document.getElementById('detail-gambar');
            const noImgEl = document.getElementById('detail-no-gambar');
            if(data.gambar) {
                imgEl.src = data.gambar;
                imgEl.style.display = 'block';
                noImgEl.style.display = 'none';
            } else {
                imgEl.src = '';
                imgEl.style.display = 'none';
                noImgEl.style.display = 'block';
            }

            // Populate lists
            const populateList = (elementId, arrayData) => {
                const el = document.getElementById(elementId);
                el.innerHTML = '';
                if(Array.isArray(arrayData) && arrayData.length > 0) {
                    arrayData.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        el.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.textContent = '-';
                    el.appendChild(li);
                }
            };

            populateList('detail-benefit', data.benefit);
            populateList('detail-fase', data.fase_checkpoint);
            populateList('detail-persyaratan', data.persyaratan);

            document.getElementById('detail-modal').classList.add('active');
        }

        function closeDetailModal() {
            document.getElementById('detail-modal').classList.remove('active');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.remove('active');
        }

        // Close modals on overlay click
        document.getElementById('delete-modal').addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });
        document.getElementById('detail-modal').addEventListener('click', function (e) {
            if (e.target === this) closeDetailModal();
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeDetailModal();
            }
        });
    </script>
@endsection