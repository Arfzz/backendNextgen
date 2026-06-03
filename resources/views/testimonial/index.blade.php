@extends('layouts.admin')

@section('title', 'Testimoni')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Testimoni</h1>
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
            <form action="{{ route('testimonial.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; width: 100%;" x-data="{ search: '{{ $search ?? '' }}' }">
                <input type="text" name="search" id="search-input" placeholder="Cari testimoni..."
                    x-model="search" @input.debounce.500ms="$el.form.submit()" style="flex: 1;">
                <select name="status" onchange="this.form.submit()"
                    style="height: 48px; padding: 10px 16px; border: 1px solid rgba(0,0,0,0.1); border-radius: 12px; font-family: 'Poppins', sans-serif; font-size: 14px; color: #464646; background: white; cursor: pointer; outline: none; min-width: 160px;">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ ($status ?? '') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="is_approved" {{ ($status ?? '') == 'is_approved' ? 'selected' : '' }}>✅ Approved</option>
                    <option value="rejected" {{ ($status ?? '') == 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                </select>
            </form>
        </div>
    </div>

    <div class="data-card">
        @if($testimonials->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peserta</th>
                            <th>Mentor</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Show</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testimonials as $index => $testimonial)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div style="font-weight: 500;">{{ optional($testimonial->user_data)->name ?? 'Anonymous' }}</div>
                                </td>
                                <td>{{ optional($testimonial->mentor_data)->nama_mentor ?? '-' }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= round($testimonial->rating) ? '#F2BC45' : 'none' }}" stroke="#F2BC45" stroke-width="2">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                            </svg>
                                        @endfor
                                        <span style="margin-left: 4px; font-weight: 600; color: #334155;">{{ number_format($testimonial->rating, 1) }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending'     => ['bg' => '#FFF8E7', 'color' => '#B8860B', 'label' => 'Pending'],
                                            'is_approved' => ['bg' => '#E7FFF0', 'color' => '#0F8B42', 'label' => 'Approved'],
                                            'rejected'    => ['bg' => '#FFEEEE', 'color' => '#E10000', 'label' => 'Rejected'],
                                        ];
                                        $s = $statusColors[$testimonial->status] ?? $statusColors['pending'];
                                    @endphp
                                    <span style="background: {{ $s['bg'] }}; color: {{ $s['color'] }}; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                        {{ $s['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px; font-size: 12px;">
                                        @if($testimonial->show_mobile)
                                            <span style="background: #E7F9FF; color: #0369A1; padding: 2px 8px; border-radius: 12px;">📱 Mobile</span>
                                        @endif
                                        @if($testimonial->show_web)
                                            <span style="background: #F0F0FF; color: #4338CA; padding: 2px 8px; border-radius: 12px;">🌐 Web</span>
                                        @endif
                                        @if(!$testimonial->show_mobile && !$testimonial->show_web)
                                            <span style="color: #94A3B8;">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="action-btn view" title="Lihat Detail"
                                            onclick="openDetailModal({{ json_encode([
                                                'id' => (string) $testimonial->_id,
                                                'user_name' => optional($testimonial->user_data)->name ?? 'Anonymous',
                                                'user_email' => optional($testimonial->user_data)->email ?? '-',
                                                'mentor_name' => optional($testimonial->mentor_data)->nama_mentor ?? '-',
                                                'content' => $testimonial->content,
                                                'rating' => $testimonial->rating,
                                                'status' => $testimonial->status,
                                                'show_mobile' => $testimonial->show_mobile,
                                                'show_web' => $testimonial->show_web,
                                                'created_at' => $testimonial->created_at ? $testimonial->created_at->format('d M Y H:i') : '-',
                                            ]) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
                                        </button>
                                        <button type="button" class="action-btn edit" title="Edit Status"
                                            onclick="openEditModal({{ json_encode([
                                                'id' => (string) $testimonial->_id,
                                                'status' => $testimonial->status,
                                                'show_mobile' => $testimonial->show_mobile ? true : false,
                                                'show_web' => $testimonial->show_web ? true : false,
                                                'content' => $testimonial->content,
                                                'user_name' => optional($testimonial->user_data)->name ?? 'Anonymous',
                                            ]) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                                        </button>
                                        <button type="button" class="action-btn delete" title="Hapus"
                                            onclick="openDeleteModal('{{ $testimonial->_id }}', '{{ addslashes(optional($testimonial->user_data)->name ?? 'Anonymous') }}')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($testimonials->hasPages())
            <div style="padding:16px 24px;border-top:1px solid #F1F5F9;">
                {{ $testimonials->withQueryString()->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon" style="font-size: 48px; margin-bottom: 16px;">💬</div>
                <p>Belum ada data testimoni.</p>
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <div class="modal-overlay" id="detail-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4);">
        <div class="modal-box" style="width: 100%; max-width: 600px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Detail Testimoni</h3>
                <button type="button" onclick="closeDetailModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>

            {{-- Rating Stars --}}
            <div style="text-align: center; margin-bottom: 24px;">
                <div id="detail-stars" style="display: flex; justify-content: center; gap: 4px; margin-bottom: 8px;"></div>
                <div id="detail-rating-value" style="font-size: 28px; font-weight: 700; color: #0F172A;"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Peserta</div>
                    <div id="detail-user" style="font-size: 16px; color: #334155; font-weight: 500;"></div>
                </div>
                <div>
                    <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Mentor</div>
                    <div id="detail-mentor" style="font-size: 16px; color: #334155; font-weight: 500;"></div>
                </div>
            </div>

            <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0; margin-bottom: 24px;">
                <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Isi Testimoni</div>
                <div id="detail-content" style="font-size: 15px; color: #334155; line-height: 1.7; font-style: italic;"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div style="background: #F8FAFC; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #E2E8F0;">
                    <div style="color: #64748B; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Status</div>
                    <div id="detail-status" style="font-weight: 600;"></div>
                </div>
                <div style="background: #F8FAFC; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #E2E8F0;">
                    <div style="color: #64748B; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Tampil</div>
                    <div id="detail-show" style="font-size: 13px;"></div>
                </div>
                <div style="background: #F8FAFC; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #E2E8F0;">
                    <div style="color: #64748B; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Tanggal</div>
                    <div id="detail-date" style="font-size: 13px; color: #334155;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal-overlay" id="edit-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4); justify-content: center; align-items: center; overflow-y: auto;">
        <div class="modal-box" style="width: 100%; max-width: 500px; padding: 32px; text-align: left; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin: 40px auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; border-bottom: 1px solid #E2E8F0; padding-bottom: 16px;">
                <h3 style="margin:0; font-size: 24px; color: #0F172A; font-weight: 700;">Edit Testimoni</h3>
                <button type="button" onclick="closeEditModal()" style="background: transparent; border:none; font-size: 24px; cursor: pointer; color: #64748B;">&times;</button>
            </div>

            <div style="background: #F0F9FF; padding: 16px; border-radius: 12px; border: 1px solid #BAE6FD; margin-bottom: 24px;">
                <div style="font-size: 13px; color: #0369A1; font-weight: 600; margin-bottom: 4px;">Testimoni dari:</div>
                <div id="edit-user-name" style="font-size: 16px; color: #0C4A6E; font-weight: 600;"></div>
                <div id="edit-content-preview" style="font-size: 13px; color: #0369A1; margin-top: 8px; font-style: italic; max-height: 60px; overflow: hidden;"></div>
            </div>

            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit_status">Status</label>
                    <select name="status" id="edit_status" class="form-control" onchange="toggleShowFields(this.value)"
                        style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-size: 15px; cursor: pointer;">
                        <option value="pending">⏳ Pending</option>
                        <option value="is_approved">✅ Approved</option>
                        <option value="rejected">❌ Rejected</option>
                    </select>
                    <div id="rejected-warning" style="display: none; color: #E10000; font-size: 12px; margin-top: 6px; background: #FFEEEE; padding: 8px 12px; border-radius: 6px;">
                        ⚠️ Status "Rejected" akan menghapus data testimoni ini secara permanen.
                    </div>
                </div>

                <div id="show-fields" style="display: none;">
                    <div style="color: #64748B; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 12px;">Tampilkan di Platform</div>
                    <div style="display: flex; gap: 24px; margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 400; padding: 12px 16px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; flex: 1;">
                            <input type="checkbox" name="show_mobile" id="edit_show_mobile" value="1"
                                style="width: 18px; height: 18px; accent-color: #2563EB;">
                            <span>📱 Mobile</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 400; padding: 12px 16px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; flex: 1;">
                            <input type="checkbox" name="show_web" id="edit_show_web" value="1"
                                style="width: 18px; height: 18px; accent-color: #2563EB;">
                            <span>🌐 Web</span>
                        </label>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal-overlay" id="delete-modal" style="backdrop-filter: blur(8px); background: rgba(19, 36, 64, 0.4);">
        <div class="modal-box" style="width: 100%; max-width: 400px;">
            <div class="modal-icon" style="font-size: 32px; margin-bottom: 16px;">⚠️</div>
            <h3>Hapus Testimoni?</h3>
            <p>Apakah Anda yakin ingin menghapus testimoni dari <strong id="delete-name"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
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
        // ── Detail Modal ────────────────────────────────────────────────
        function openDetailModal(data) {
            // Stars
            const starsEl = document.getElementById('detail-stars');
            starsEl.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                starsEl.innerHTML += `<svg width="24" height="24" viewBox="0 0 24 24" fill="${i <= Math.round(data.rating) ? '#F2BC45' : 'none'}" stroke="#F2BC45" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
            }

            document.getElementById('detail-rating-value').textContent = parseFloat(data.rating).toFixed(1) + ' / 5.0';
            document.getElementById('detail-user').textContent    = data.user_name;
            document.getElementById('detail-mentor').textContent  = data.mentor_name;
            document.getElementById('detail-content').textContent = data.content;
            document.getElementById('detail-date').textContent    = data.created_at;

            // Status badge
            const statusMap = {
                'pending':     { bg: '#FFF8E7', color: '#B8860B', label: 'Pending' },
                'is_approved': { bg: '#E7FFF0', color: '#0F8B42', label: 'Approved' },
                'rejected':    { bg: '#FFEEEE', color: '#E10000', label: 'Rejected' },
            };
            const s = statusMap[data.status] || statusMap['pending'];
            document.getElementById('detail-status').innerHTML = `<span style="background:${s.bg};color:${s.color};padding:2px 10px;border-radius:12px;font-size:12px;">${s.label}</span>`;

            // Show platforms
            let showParts = [];
            if (data.show_mobile) showParts.push('📱 Mobile');
            if (data.show_web) showParts.push('🌐 Web');
            document.getElementById('detail-show').textContent = showParts.length > 0 ? showParts.join(', ') : '—';

            document.getElementById('detail-modal').classList.add('active');
        }
        function closeDetailModal() { document.getElementById('detail-modal').classList.remove('active'); }

        // ── Edit Modal ──────────────────────────────────────────────────
        function openEditModal(data) {
            document.getElementById('edit-form').action = '/testimonial/' + data.id;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_show_mobile').checked = data.show_mobile;
            document.getElementById('edit_show_web').checked = data.show_web;
            document.getElementById('edit-user-name').textContent = data.user_name;
            document.getElementById('edit-content-preview').textContent = data.content;

            toggleShowFields(data.status);
            document.getElementById('edit-modal').style.display = 'flex';
        }
        function closeEditModal() { document.getElementById('edit-modal').style.display = 'none'; }

        function toggleShowFields(status) {
            const showFields = document.getElementById('show-fields');
            const rejectedWarning = document.getElementById('rejected-warning');

            if (status === 'is_approved') {
                showFields.style.display = 'block';
                rejectedWarning.style.display = 'none';
            } else {
                showFields.style.display = 'none';
                rejectedWarning.style.display = status === 'rejected' ? 'block' : 'none';
            }
        }

        // ── Delete Modal ────────────────────────────────────────────────
        function openDeleteModal(id, name) {
            document.getElementById('delete-name').textContent = name;
            document.getElementById('delete-form').action = '/testimonial/' + id;
            document.getElementById('delete-modal').classList.add('active');
        }
        function closeDeleteModal() { document.getElementById('delete-modal').classList.remove('active'); }

        // ── Close on backdrop click / Esc ───────────────────────────────
        window.addEventListener('click', function(e) {
            ['delete-modal', 'detail-modal'].forEach(id => {
                const el = document.getElementById(id);
                if (e.target === el) el.classList.remove('active');
            });
            ['edit-modal'].forEach(id => {
                const el = document.getElementById(id);
                if (e.target === el) el.style.display = 'none';
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['delete-modal','detail-modal'].forEach(id => document.getElementById(id).classList.remove('active'));
                ['edit-modal'].forEach(id => document.getElementById(id).style.display = 'none');
            }
        });
    </script>
@endsection
