@extends('layouts.admin')

@section('title', 'Detail Artikel')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $artikel->judul_artikel }}</h1>
    </div>

    <div class="detail-card">
        {{-- Custom Highlight Image --}}
        <div style="margin-bottom: 24px; text-align: center;">
            <img src="{{ Str::startsWith($artikel->thumbnail, ['http', '/']) ? $artikel->thumbnail : asset('storage/' . $artikel->thumbnail) }}" 
                 alt="{{ $artikel->judul_artikel }}" 
                 style="max-width: 100%; max-height: 300px; border-radius: 12px; box-shadow: 0px 4px 12px rgba(0,0,0,0.1);">
        </div>

        {{-- Divider --}}
        <div class="divider"></div>

        <div class="detail-row" style="margin-bottom: 16px;">
            <div class="detail-label" style="font-size: 12px; color: var(--text-light); margin-bottom: 4px;">URL/Link Sumber</div>
            <div class="detail-value">
                <a href="{{ $artikel->url }}" target="_blank" style="color: var(--primary-teal); text-decoration: none; font-weight: 500;">
                    {{ $artikel->url }} 
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px; vertical-align: middle;">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Detail Grid --}}
        <div class="detail-grid" style="margin-top: 16px;">
            <div class="detail-box">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">
                    {{ $artikel->created_at ? $artikel->created_at->format('d M Y, H:i') : '-' }}
                </span>
            </div>

            <div class="detail-box">
                <span class="detail-label">Diperbarui</span>
                <span class="detail-value">
                    {{ $artikel->updated_at ? $artikel->updated_at->format('d M Y, H:i') : '-' }}
                </span>
            </div>
        </div>
    </div>

    <div class="form-actions modern">
        <a href="{{ route('artikel.edit', $artikel->_id) }}" class="btn btn-primary">
            Edit
        </a>
        <a href="{{ route('artikel.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>
@endsection
