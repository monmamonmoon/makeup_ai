{{-- resources/views/components/navbar.blade.php --}}
<nav class="navbar">
    <div class="container navbar-content">
        <a href="{{ route('welcome') }}" class="navbar-brand">
            <i class="fa-solid fa-wand-magic-sparkles"></i> <span>MakeupAI</span>
        </a>

        <div class="navbar-links">
            <a href="{{ route('analyze') }}" class="nav-link {{ request()->routeIs('analyze') ? 'active' : '' }}">Analyze</a>
            <a href="{{ route('saved-looks') }}" class="nav-link {{ request()->routeIs('saved-looks') ? 'active' : '' }}">Saved Looks</a>
            <a href="{{ route('tutorials') }}" class="nav-link {{ request()->routeIs('tutorials') ? 'active' : '' }}">Tutorials</a>

            {{-- Login/Logout Button Logic --}}
            @guest {{-- Show Login if user is a guest --}}
                <a href="{{ route('login') }}" class="nav-button login">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            @else {{-- Show Logout if logged in --}}
                 {{-- Basic Logout Form --}}
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-button logout">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </button>
                </form>
            @endguest
            {{-- TODO: Add mobile menu toggle button here --}}
        </div>
        {{-- TODO: Add mobile menu container here --}}
    </div>
</nav>

