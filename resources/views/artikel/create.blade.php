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

        <form action="{{ route('artikel.store') }}" method="POST">
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
                <label for="thumbnail" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">URL Gambar Thumbnail</label>
                <input type="url" name="thumbnail" id="thumbnail" class="form-control" value="{{ old('thumbnail') }}" required
                    placeholder="https://placehold.co/220x124"
                    style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
                <small style="color: #777; display: block; margin-top: 6px;">Masukkan link format JPG/PNG/WEBP.</small>
            </div>

            <div class="form-actions modern">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('artikel.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
