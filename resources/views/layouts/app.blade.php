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


    {{-- ... (your existing modals: logout-modal, login-required-modal) ... --}}

    {{-- --- NEW: CUSTOMIZE TRY-ON MODAL --- --}}
    <div id="customize-tryon-modal-overlay" class="modal-overlay" style="display: none;">
        <div id="customize-tryon-modal-content" class="modal-content modal-large"> {{-- Larger modal size --}}
            <button id="customize-tryon-modal-close" class="modal-close">&times;</button>
            <h2 class="modal-title">Customize Your Makeup</h2>
            <p id="customize-tryon-modal-subtitle" class="modal-text"></p> {{-- Dynamic recommendation text here --}}

            <div class="customize-layout">
                {{-- Left side: Controls --}}
                <div class="customize-controls">
                    {{-- Color Selection --}}
                    <div class="form-group">
                        <label class="form-label">Makeup Color</label>
                        <div class="color-palette">
                            <input type="radio" name="makeup_color" id="color-red" value="red" checked>
                            <label for="color-red" class="color-swatch bg-red-500" title="Red"></label>
                            <input type="radio" name="makeup_color" id="color-pink" value="pink">
                            <label for="color-pink" class="color-swatch bg-pink-500" title="Pink"></label>
                            <input type="radio" name="makeup_color" id="color-nude" value="nude">
                            <label for="color-nude" class="color-swatch bg-yellow-700" title="Nude"></label> {{-- Using
                            yellow-700 for nude --}}
                            <input type="radio" name="makeup_color" id="color-brown" value="brown">
                            <label for="color-brown" class="color-swatch bg-amber-900" title="Brown"></label>
                            <input type="radio" name="makeup_color" id="color-black" value="black">
                            <label for="color-black" class="color-swatch bg-black" title="Black"></label>
                            <input type="radio" name="makeup_color" id="color-default" value="default"
                                style="display: none;">
                            <label for="color-default" class="color-swatch bg-gray-300" title="Default"
                                style="display: none;"></label> {{-- Hidden default option --}}
                        </div>
                    </div>

                    {{-- Intensity Adjustment --}}
                    <div class="form-group mt-4">
                        <label for="makeup-intensity" class="form-label">Intensity Adjustment</label>
                        <input type="range" id="makeup-intensity" min="0" max="100" value="50" class="slider">
                        <span id="intensity-value" class="ml-2 text-gray-700">50%</span>
                    </div>

                    {{-- Additional Instructions (Optional) --}}
                    <div class="form-group mt-4">
                        <label for="additional-instructions" class="form-label">Additional Instructions
                            (Optional)</label>
                        <textarea id="additional-instructions" class="form-textarea" rows="2"
                            placeholder="e.g., 'make it glossy', 'add shimmer'"></textarea>
                    </div>

                    <button id="generate-tryon-button" class="button button-pink mt-6 w-full">Generate Try-On</button>
                </div>

                {{-- Right side: Preview (will show original and generated) --}}
                <div class="customize-preview">
                    <div class="image-box">
                        <h3 class="box-title">Original Photo</h3>
                        <img id="customize-original-image-preview" src="#" alt="Original Photo"
                            class="image-preview-thumbnail">
                    </div>
                    <div class="image-box">
                        <h3 class="box-title">Makeup Preview</h3>
                        <div id="customize-generated-image-container"
                            class="image-preview-thumbnail flex items-center justify-center bg-gray-100">
                            <div id="customize-preview-spinner" class="spinner" style="display: none;"></div>
                            <img id="customize-generated-image" src="#" alt="Generated Makeup"
                                class="image-preview-thumbnail" style="display: none;">
                            <p id="customize-preview-placeholder" class="text-gray-500 text-sm">Your custom look will
                                appear here.</p>
                        </div>
                        <p id="customize-preview-error" class="form-error mt-2" style="display: none;"></p>
                    </div>
                </div>
            </div> {{-- End customize-layout --}}
        </div>
    </div>
    {{-- --- END NEW MODAL --- --}}

</body>

</html>

</body>

</html>
</body>

</html>