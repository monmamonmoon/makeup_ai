@extends('layouts.app')
@section('title', 'Analyze Features')

@section('content')
<div class="container analysis-page"> {{-- Use container class and center --}}
    <h1 class="analysis-title">Personalized Makeup Journey</h1>
    <p class="analysis-subtitle">
        Tell us your style, upload a photo, and get AI-powered recommendations!
    </p>

    {{-- Analysis Form --}}
    <form id="analysis-form" class="analysis-form" onsubmit="return false;">
        {{-- No CSRF needed here when submitting via JS Fetch to a public API route --}}

        {{-- Step 1 Card --}}
        <div class="step-card">
            <h2 class="step-title">Step 1: Your Preferences</h2>
            <p class="step-subtitle">Choose your desired makeup style and intensity</p>
            {{-- Makeup Style --}}
            <div class="form-group">
                <label class="form-label">Makeup Style</label>
                <div class="choice-group">
                    <label class="choice-chip"> <input type="radio" name="style" value="Feminine" required> <span>Feminine</span> </label>
                    <label class="choice-chip"> <input type="radio" name="style" value="Masculine" required> <span>Masculine</span> </label>
                </div>
            </div>
            {{-- Makeup Intensity --}}
            <div class="form-group">
                <label class="form-label">Makeup Intensity</label>
                <div class="choice-group">
                    <label class="choice-chip"> <input type="radio" name="intensity" value="Fresh" required> <span>Fresh</span> </label>
                    <label class="choice-chip"> <input type="radio" name="intensity" value="Natural" required> <span>Natural</span> </label>
                    <label class="choice-chip"> <input type="radio" name="intensity" value="Heavy" required> <span>Heavy</span> </label>
                </div>
            </div>
        </div>

        {{-- Step 2 Card --}}
        <div class="step-card">
             <h2 class="step-title">Step 2: Upload Your Photo</h2>
            <p class="step-subtitle">For best results, use a well-lit photo with your face clearly visible. Minimum 500x500 recommended.</p>
            <div class="form-group">
                 <label for="face-image" class="form-label">Face Image</label>
                 <div class="file-input-wrapper">
                    <label for="face-image" class="button button-grey"> <i class="fa-solid fa-upload"></i> Choose File </label>
                    <input type="file" id="face-image" name="image" accept="image/*" required style="display: none;">
                    <span id="file-name" class="file-name">No file chosen</span>
                 </div>
                 <div class="image-preview-container">
                      <img id="image-preview" src="#" alt="Image Preview" class="image-preview" style="display: none;"/>
                 </div>
            </div>
            <div class="button-container">
                 <button type="submit" id="analyze-button" class="button button-pink button-analyze" disabled>
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Analyze Features
                 </button>
                 <div id="loading-spinner" class="spinner" style="display: none;"></div>
            </div>
             <p id="form-error" class="form-error" style="display: none;"></p>
        </div>
    </form>
</div>
@endsection

{{-- Add Page-Specific JavaScript --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Get form elements ---
        const form = document.getElementById('analysis-form');
        const styleRadios = form.elements['style'];
        const intensityRadios = form.elements['intensity'];
        const imageInput = document.getElementById('face-image');
        const fileNameSpan = document.getElementById('file-name');
        const analyzeButton = document.getElementById('analyze-button');
        const loadingSpinner = document.getElementById('loading-spinner');
        const errorDisplay = document.getElementById('form-error');
        const imagePreview = document.getElementById('image-preview');
        let selectedFile = null;

        // --- Function to enable/disable analyze button ---
        function checkFormValidity() {
            const styleSelected = !!styleRadios.value;
            const intensitySelected = !!intensityRadios.value;
            const imageSelected = imageInput.files.length > 0;
            analyzeButton.disabled = !(styleSelected && intensitySelected && imageSelected);
        }
        form.addEventListener('change', checkFormValidity); // Check on any form change

        // --- Handle file selection and preview ---
         imageInput.addEventListener('change', function(event) {
            if (event.target.files.length > 0) {
                selectedFile = event.target.files[0];
                fileNameSpan.textContent = selectedFile.name;
                fileNameSpan.style.fontStyle = 'normal';
                fileNameSpan.style.color = '#1f2937';
                const reader = new FileReader();
                reader.onload = function(e) { if (imagePreview) { imagePreview.src = e.target.result; imagePreview.style.display = 'block'; } }
                reader.readAsDataURL(selectedFile);
            } else {
                selectedFile = null;
                fileNameSpan.textContent = 'No file chosen';
                fileNameSpan.style.fontStyle = 'italic';
                fileNameSpan.style.color = '#6b7280';
                 if (imagePreview) { imagePreview.src = '#'; imagePreview.style.display = 'none'; }
            }
            checkFormValidity();
        });

        // --- Handle form submission ---
        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            if (analyzeButton.disabled) return;

            // --- Show Loading ---
            analyzeButton.style.display = 'none';
            loadingSpinner.style.display = 'block';
            errorDisplay.style.display = 'none';
            errorDisplay.textContent = '';

            // --- Prepare form data ---
            const formData = new FormData();
            formData.append('style', styleRadios.value);
            formData.append('intensity', intensityRadios.value);
            formData.append('image', selectedFile);

            // --- Get CSRF token (needed for web session protection) ---
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                console.log('Sending data (using web session auth)...');
                const response = await fetch('/api/analyze', { // Target the API route
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken // Send CSRF token for web routes
                        // No Authorization header needed when using web auth middleware
                    },
                    body: formData
                });

                const data = await response.json();
                console.log('Response:', data);

                if (response.ok) {
                    console.log('Analysis Success:', data);
                    // Store results in sessionStorage to pass to next page
                    sessionStorage.setItem('analysisResult', JSON.stringify(data));
                    // Redirect to a results page (using saved-looks for now)
                     window.location.href = "{{ route('saved-looks') }}";
                } else {
                    // Check specifically for 401/403 which likely means not logged in
                    if (response.status === 401 || response.status === 403) {
                         throw new Error(data.message || 'Authentication required. Please log in.');
                    }
                    // Handle other errors from Laravel/Gemini
                    throw new Error(data.error || data.details || data.message || `Server error: ${response.status}`);
                }

            } catch (error) {
                console.error('Analysis submission error:', error);
                errorDisplay.textContent = `An error occurred: ${error.message}`;
                errorDisplay.style.display = 'block';
            } finally {
                // --- Hide Loading ---
                analyzeButton.style.display = 'inline-block';
                loadingSpinner.style.display = 'none';
            }
        });
    });
</script>
@endpush