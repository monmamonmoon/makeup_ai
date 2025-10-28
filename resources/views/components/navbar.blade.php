{{-- resources/views/components/navbar.blade.php --}}
<nav class="navbar">
    <div class="container navbar-content">
        <a href="{{ route('welcome') }}" class="navbar-brand">
            <i class="fa-solid fa-wand-magic-sparkles"></i> <span>MakeupAI</span>
        </a>

        <div class="navbar-links">
            <a href="{{ route('analyze') }}"
                class="nav-link {{ request()->routeIs('analyze') ? 'active' : '' }}">Analyze</a>
            <a href="{{ route('saved-looks') }}"
                class="nav-link {{ request()->routeIs('saved-looks') ? 'active' : '' }}">Saved Looks</a>
            <a href="{{ route('tutorials') }}"
                class="nav-link {{ request()->routeIs('tutorials') ? 'active' : '' }}">Tutorials</a>

            {{-- Login/Logout Button Logic --}}
            @guest {{-- Show Login if user is a guest --}}
                <a href="{{ route('login') }}" class="nav-button login">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            @else {{-- Show User Dropdown/Logout if logged in --}}
                {{-- New "Logout" button that triggers the modal --}}
                <button id="logout-button" type="button" class="nav-button logout ml-4">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>

                {{-- Hidden form that JS will submit --}}
                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                    @csrf
                </form>
            @endguest
            {{-- TODO: Add mobile menu toggle button here --}}
        </div>
        {{-- TODO: Add mobile menu container here --}}
    </div>
</nav>