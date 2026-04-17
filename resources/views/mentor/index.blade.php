@extends('layouts.admin')

@section('title', 'Mentor')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Mentor</h1>
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
            <form action="{{ route('mentor.index') }}" method="GET">
                <input type="text" name="search" id="search-input" placeholder="Cari mentor..."
                    value="{{ $search ?? '' }}">
            </form>
        </div>
        <a href="{{ route('mentor.create') }}" class="btn btn-primary" id="btn-tambah">
            Tambah
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
        </a>
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
                            <th>Rating</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mentors as $index => $mentor)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $mentor->nama_mentor }}</td>
                                <td>{{ $mentor->pendidikan }}</td>
                                <td>{{ $mentor->awardee }}</td>
                                <td>{{ $mentor->rating }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('mentor.show', $mentor->_id) }}" class="action-btn view"
                                            title="Lihat Detail">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('mentor.edit', $mentor->_id) }}" class="action-btn edit"
                                            title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>
                                        <button type="button" class="action-btn delete" title="Hapus"
                                            onclick="openDeleteModal('{{ $mentor->_id }}', '{{ addslashes($mentor->nama_mentor) }}')">
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
                <div class="empty-icon">👥</div>
                <p>Belum ada data mentor.</p>
                <a href="{{ route('mentor.create') }}" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Tambah Mentor
                </a>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal-overlay" id="delete-modal">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <h3>Hapus Mentor?</h3>
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
@endsection

@section('scripts')
    <script>
        function openDeleteModal(id, name) {
            document.getElementById('delete-name').textContent = name;
            document.getElementById('delete-form').action = '/mentor/' + id;
            document.getElementById('delete-modal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.remove('active');
        }

        // Close modal on overlay click
        document.getElementById('delete-modal').addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDeleteModal();
        });
    </script>
@endsection
