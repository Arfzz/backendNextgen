@extends('layouts.admin')

@section('title', 'Detail Mentor')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $mentor->nama_mentor }}</h1>
    </div>

    <div class="detail-card">
        {{-- Highlight --}}
        <div class="highlight-grid">
            <div class="highlight-item">
                <span class="highlight-label">Pendidikan</span>
                <span class="highlight-value">
                    {{ $mentor->pendidikan }}
                </span>
            </div>

            <div class="highlight-item">
                <span class="highlight-label">Awardee</span>
                <span class="highlight-value">
                    {{ $mentor->awardee }}
                </span>
            </div>

            <div class="highlight-item">
                <span class="highlight-label">Rating</span>
                <span class="highlight-value" style="color: #F2BC45; font-weight: 700;">
                    ★ {{ $mentor->rating }} / 5.0
                </span>
            </div>
        </div>

        {{-- Divider --}}
        <div class="divider"></div>

        {{-- Detail --}}
        <div class="detail-grid">
            <div class="detail-box">
                <span class="detail-label">Dibuat</span>
                <span class="detail-value">
                    {{ $mentor->created_at ? $mentor->created_at->format('d M Y, H:i') : '-' }}
                </span>
            </div>

            <div class="detail-box">
                <span class="detail-label">Diperbarui</span>
                <span class="detail-value">
                    {{ $mentor->updated_at ? $mentor->updated_at->format('d M Y, H:i') : '-' }}
                </span>
            </div>
        </div>
    </div>

    <div class="form-actions modern">
        <a href="{{ route('mentor.edit', $mentor->_id) }}" class="btn btn-primary">
            Edit
        </a>
        <a href="{{ route('mentor.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>
@endsection
