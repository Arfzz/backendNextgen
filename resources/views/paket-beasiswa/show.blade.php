@extends('layouts.admin')

@section('title', 'Detail Paket Beasiswa')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $paketBeasiswa->nama_beasiswa }}</h1>
    </div>

    <div class="detail-card">

        {{-- Highlight --}}
        <div class="highlight-grid">
            <div class="highlight-item">
                <span class="highlight-label">Harga</span>
                <span class="highlight-value price">
                    Rp {{ number_format($paketBeasiswa->harga, 0, ',', '.') }}
                </span>
            </div>

            <div class="highlight-item">
                <span class="highlight-label">Deadline</span>
                <span class="highlight-value">
                    {{ $paketBeasiswa->deadline ? $paketBeasiswa->deadline->format('d M Y') : '-' }}
                </span>
            </div>

        </div>

        {{-- Divider --}}
        <div class="divider"></div>

        {{-- Dynamic Arrays Section --}}
        <div style="margin-bottom: 24px;">
            <h4 style="font-size: 16px; margin-bottom: 8px; color: var(--text-dark);">Fase Checkpoint</h4>
            <ul style="padding-left: 20px; color: var(--text-light); margin-bottom: 16px;">
                @if(is_array($paketBeasiswa->fase_checkpoint) && count($paketBeasiswa->fase_checkpoint) > 0)
                    @foreach($paketBeasiswa->fase_checkpoint as $fase)
                        <li>{{ $fase }}</li>
                    @endforeach
                @else
                    <li>{{ $paketBeasiswa->fase_checkpoint ?? '-' }}</li>
                @endif
            </ul>

            <h4 style="font-size: 16px; margin-bottom: 8px; color: var(--text-dark);">Persyaratan</h4>
            <ul style="padding-left: 20px; color: var(--text-light);">
                @if(is_array($paketBeasiswa->persyaratan) && count($paketBeasiswa->persyaratan) > 0)
                    @foreach($paketBeasiswa->persyaratan as $syarat)
                        <li>{{ $syarat }}</li>
                    @endforeach
                @else
                    <li>{{ $paketBeasiswa->persyaratan ?? '-' }}</li>
                @endif
            </ul>
        </div>

        {{-- Divider --}}
        <div class="divider"></div>

        {{-- Detail --}}
        <div class="detail-grid">
            <div class="detail-box">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">
                    {{ $paketBeasiswa->created_at ? $paketBeasiswa->created_at->format('d M Y, H:i') : '-' }}
                </span>
            </div>

            <div class="detail-box">
                <span class="detail-label">Diperbarui</span>
                <span class="detail-value">
                    {{ $paketBeasiswa->updated_at ? $paketBeasiswa->updated_at->format('d M Y, H:i') : '-' }}
                </span>
            </div>
        </div>

    </div>

    <div class="form-actions modern">
        <a href="{{ route('paket-beasiswa.edit', $paketBeasiswa->_id) }}" class="btn btn-primary">
            Edit
        </a>
        <a href="{{ route('paket-beasiswa.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>
@endsection