<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MakeupAI - @yield('title', 'Welcome')</title>

    {{-- Link your custom CSS file from the public folder --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- Add Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Add Google Fonts (Optional, but helps match the design) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- Placeholder for page-specific styles --}}
    @stack('styles')

    <style>
        /* Basic body styling to push footer down */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            /* Use Roboto font */
            background-color: #f8fafc;
            /* Light gray background */
            color: #1f2937;
            /* Default text color */
            margin: 0;
            /* Remove default body margin */
        }

        main {
            flex-grow: 1;
            /* Allow main content to grow */
            padding-top: 2rem;
            /* Add some padding */
            padding-bottom: 2rem;
        }

        /* Basic container */
        .container {
            width: 100%;
            max-width: 1152px;
            /* max-w-6xl */
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            /* px-4 */
            padding-right: 1rem;
        }

        @media (min-width: 640px) {
            .container {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        /* sm:px-6 */
        @media (min-width: 1024px) {
            .container {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }

        /* lg:px-8 */
    </style>
</head>

<body>

    {{-- Include the Navbar Component --}}
    {{-- We will put the navbar code in resources/views/components/navbar.blade.php --}}
    <x-navbar />

    <main>
        {{-- This is where the main content of each page (like welcome.blade.php) will be injected --}}
        @yield('content')
    </main>

    {{-- Include the Footer Component --}}
    {{-- We will put the footer code in resources/views/components/footer.blade.php --}}
    <x-footer />

    {{-- Link your custom JS file from the public folder --}}
    <script src="{{ asset('js/script.js') }}"></script>
    {{-- Placeholder for page-specific scripts --}}
    @stack('scripts')

    {{-- ... (your <x-footer />) ... --}}
    {{--
    <script src="{{ asset('js/script.js') }}"></script> --}}
    {{-- @stack('scripts') --}}

    {{-- --- ADD LOGOUT CONFIRMATION MODAL --- --}}
    <div id="logout-modal-overlay" class="modal-overlay" style="display: none;">
        <div id="logout-modal-content" class="modal-content modal-small">
            <button id="logout-modal-close" class="modal-close">&times;</button>
            <h2 class="modal-title">Confirm Logout</h2>
            <p class="modal-text">Are you sure you want to log out?</p>
            <div class="modal-buttons">
                <button id="logout-cancel-button" class="button button-grey">Cancel</button>
                {{-- This button will trigger the hidden form --}}
                <button id="logout-confirm-button" class="button button-pink">Logout</button>
            </div>
        </div>
    </div>
    {{-- --- END MODAL --- --}}

</body>

</html>

</body>

</html>
</body>

</html>