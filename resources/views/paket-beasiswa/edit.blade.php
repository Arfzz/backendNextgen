@extends('layouts.admin')

@section('title', 'Edit Paket Beasiswa')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Paket Beasiswa</h1>
</div>

<div class="form-card">
    <form action="{{ route('paket-beasiswa.update', $paketBeasiswa->_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="nama_beasiswa">Nama Beasiswa</label>
            <input type="text" name="nama_beasiswa" id="nama_beasiswa" placeholder="Masukkan nama beasiswa" value="{{ old('nama_beasiswa', $paketBeasiswa->nama_beasiswa) }}" required>
            @error('nama_beasiswa')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" id="fase-container">
            <label>Fase Checkpoint</label>
            @php
                $faseOld = old('fase_checkpoint', is_array($paketBeasiswa->fase_checkpoint) ? $paketBeasiswa->fase_checkpoint : [$paketBeasiswa->fase_checkpoint ?? '']);
            @endphp
            @foreach($faseOld as $index => $val)
                <div class="dynamic-input-group" style="display: flex; gap: 8px; margin-bottom: 8px;">
                    <input type="text" name="fase_checkpoint[]" placeholder="Contoh: Seleksi Berkas" value="{{ $val }}" required style="flex: 1;">
                    @if($index == 0)
                        <button type="button" class="btn btn-secondary add-fase-btn" style="padding: 0 16px; font-size: 20px;">+</button>
                    @else
                        <button type="button" class="btn btn-danger remove-btn" style="padding: 0 16px; font-size: 20px; background: #FFEEEE; color: #E10000; border: none;">-</button>
                    @endif
                </div>
            @endforeach
            @error('fase_checkpoint')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" id="persyaratan-container">
            <label>Persyaratan</label>
            @php
                $syaratOld = old('persyaratan', is_array($paketBeasiswa->persyaratan) ? $paketBeasiswa->persyaratan : [$paketBeasiswa->persyaratan ?? '']);
            @endphp
            @foreach($syaratOld as $index => $val)
                <div class="dynamic-input-group" style="display: flex; gap: 8px; margin-bottom: 8px;">
                    <input type="text" name="persyaratan[]" placeholder="Contoh: Mahasiswa Aktif" value="{{ $val }}" required style="flex: 1;">
                    @if($index == 0)
                        <button type="button" class="btn btn-secondary add-syarat-btn" style="padding: 0 16px; font-size: 20px;">+</button>
                    @else
                        <button type="button" class="btn btn-danger remove-btn" style="padding: 0 16px; font-size: 20px; background: #FFEEEE; color: #E10000; border: none;">-</button>
                    @endif
                </div>
            @endforeach
            @error('persyaratan')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="deadline">Deadline</label>
            <input type="date" name="deadline" id="deadline" value="{{ old('deadline', $paketBeasiswa->deadline ? $paketBeasiswa->deadline->format('Y-m-d') : '') }}" required>
            @error('deadline')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="harga">Harga (Rp)</label>
            <input type="number" name="harga" id="harga" placeholder="Masukkan harga" value="{{ old('harga', $paketBeasiswa->harga) }}" min="0" required>
            @error('harga')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Perbarui
            </button>
            <a href="{{ route('paket-beasiswa.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function createRow(inputName, placeholderTxt) {
        const div = document.createElement('div');
        div.className = 'dynamic-input-group';
        div.style.display = 'flex';
        div.style.gap = '8px';
        div.style.marginBottom = '8px';
        
        div.innerHTML = `
            <input type="text" name="${inputName}[]" placeholder="${placeholderTxt}" required style="flex: 1; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
            <button type="button" class="btn btn-danger remove-btn" style="padding: 0 16px; font-size: 20px; background: #FFEEEE; color: #E10000; border: none;">-</button>
        `;
        return div;
    }

    // Add Fase
    document.querySelector('.add-fase-btn').addEventListener('click', function() {
        const container = document.getElementById('fase-container');
        container.insertBefore(createRow('fase_checkpoint', 'Contoh: Seleksi Wawancara'), container.lastElementChild);
    });

    // Add Persyaratan
    document.querySelector('.add-syarat-btn').addEventListener('click', function() {
        const container = document.getElementById('persyaratan-container');
        container.insertBefore(createRow('persyaratan', 'Contoh: IPK min 3.0'), container.lastElementChild);
    });

    // Remove Event Delegation
    document.body.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-btn')) {
            e.target.parentElement.remove();
        }
    });
});
</script>
@endsection
