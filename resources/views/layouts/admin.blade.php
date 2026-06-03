<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="NextGen Community Admin Dashboard">
    <title>@yield('title', 'Dashboard') - NextGen Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body>
    <div class="admin-layout">
        {{-- Sidebar --}}
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <img src="{{ asset('images/logoNextgen.png') }}" alt="Nextgen Logo" width="38" height="32"
                        style="object-fit: contain;">
                </div>
                <span class="brand-text">NEXTGEN</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('paket-beasiswa.index') }}"
                    class="nav-item {{ request()->is('paket-beasiswa*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                            <path d="M6 12v5c3 3 9 3 12 0v-5" />
                        </svg>
                    </span>
                    <span>Beasiswa</span>
                </a>
                <a href="{{ route('mentor.index') }}" class="nav-item {{ request()->is('mentor*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 00-3-3.87" />
                            <path d="M16 3.13a4 4 0 010 7.75" />
                        </svg>
                    </span>
                    <span>Mentor</span>
                </a>
                <a href="{{ route('artikel.index') }}" class="nav-item {{ request()->is('artikel*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                    </span>
                    <span>Artikel</span>
                </a>
                <a href="{{ route('testimonial.index') }}"
                    class="nav-item {{ request()->is('testimonial*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                        </svg>
                    </span>
                    <span>Testimoni</span>
                </a>
                <a href="{{ route('orders.index') }}"
                    class="nav-item {{ request()->is('orders*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <line x1="2" y1="10" x2="22" y2="10" />
                        </svg>
                    </span>
                    <span>Pembayaran</span>
                </a>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->is('users*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </span>
                    <span>Pengguna</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="#" class="nav-item"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                    </span>
                    <span>Keluar</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="main-content">
            {{-- Topbar --}}
            <header class="topbar">

                <div class="topbar-profile" style="gap:10px;">
                    <div class="profile-info">
                        <div class="profile-name" style="text-align: left; font-size: 15px; font-weight: 600;">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="profile-role" style="text-align: left; font-size: 12px; color: #64748B;">NextGen Community</div>
                    </div>
                    @php
                        $userProfile = Auth::user()?->profile_picture;
                    @endphp
                    @if($userProfile)
                        <img src="{{ Str::startsWith($userProfile, ['http://', 'https://']) ? $userProfile : Storage::url($userProfile) }}" alt="Admin" class="profile-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; outline: none;">
                    @else
                        <img src="{{ asset('images/avatar.png') }}" alt="Admin" class="profile-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; outline: none;">
                    @endif
                </div>
            </header>

            {{-- Content --}}
            <div class="content-area">
                @if(session('success'))
                    <div class="alert alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @yield('scripts')
    <!-- Alpine.js for realtime search -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>