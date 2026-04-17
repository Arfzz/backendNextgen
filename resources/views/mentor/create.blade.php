@extends('layouts.admin')

@section('title', 'Tambah Mentor')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Tambah Mentor</h1>
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

        <form action="{{ route('mentor.store') }}" method="POST">
            @csrf
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="nama_mentor" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Nama Mentor</label>
                <input type="text" name="nama_mentor" id="nama_mentor" class="form-control" value="{{ old('nama_mentor') }}" required
                    style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="pendidikan" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Pendidikan (Cth: IPB University)</label>
                <input type="text" name="pendidikan" id="pendidikan" class="form-control" value="{{ old('pendidikan') }}" required
                    style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="awardee" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Awardee (Pilih Paket Beasiswa)</label>
                <select name="awardee" id="awardee" class="form-control" required
                    style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif; appearance: auto; background: white;">
                    <option value="" disabled {{ old('awardee') ? '' : 'selected' }}>-- Pilih Paket Beasiswa --</option>
                    @foreach($paketBeasiswas as $paket)
                        <option value="{{ $paket->nama_beasiswa }}" {{ old('awardee') == $paket->nama_beasiswa ? 'selected' : '' }}>
                            {{ $paket->nama_beasiswa }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="rating" style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;">Rating (0 - 5)</label>
                <input type="number" step="0.1" min="0" max="5" name="rating" id="rating" class="form-control" value="{{ old('rating') }}" required
                    style="width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: 'Poppins', sans-serif;">
            </div>

            <div class="form-actions modern">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('mentor.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
