@extends('layouts.admin')

@section('title', 'Tambah Artikel')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Tambah Artikel</h1>
    </div>

    <div class="form-card">
        @if($errors->any())
            <div class="alert alert-danger" style="color: #E10000; background: #FFEEEE; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('artikel.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="judul_artikel" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Judul Artikel</label>
                <input type="text" name="judul_artikel" id="judul_artikel" class="form-control" value="{{ old('judul_artikel') }}" required
                    style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="url" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">URL Artikel Lengkap</label>
                <input type="url" name="url" id="url" class="form-control" value="{{ old('url') }}" required
                    placeholder="https://..."
                    style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Thumbnail Gambar (Pilih salah satu)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label for="thumbnail_file" style="display: block; margin-bottom: 8px; font-size: 13px; color: #64748B;">Upload File Lokal</label>
                        <input type="file" name="thumbnail_file" id="thumbnail_file" class="form-control" accept="image/*"
                            style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
                    </div>
                    <div>
                        <label for="thumbnail_url" style="display: block; margin-bottom: 8px; font-size: 13px; color: #64748B;">Atau URL Link Gambar</label>
                        <input type="url" name="thumbnail_url" id="thumbnail_url" class="form-control" value="{{ old('thumbnail_url') }}"
                            placeholder="https://..."
                            style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
                    </div>
                </div>
            </div>

            <div class="form-actions modern">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('artikel.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
