@extends('layouts.app')
@section('title', 'Analysis Results')

@section('content')
<div class="container results-page-v2"> {{-- Use new container class --}}
    <h1 class="results-title">Analysis Results</h1>
    <p class="results-subtitle">
        Here are the features detected by the AI. Click "Try On" to see recommendations applied to your photo!
    </p>

    {{-- Main Content Grid --}}
    <div class="results-layout-v2">
        {{-- Left Column: Your Photo --}}
        <div class="results-card-v2 card-photo">
            <h2 class="card-title-v2">
                <i class="fa-solid fa-image card-icon-sm"></i>
                Your Photo
            </h2>
            <div class="result-image-container-v2">
                <img id="result-image-preview" src="#" alt="Analyzed Photo" class="result-image-preview-v2" style="display: none;"/>
                <p id="image-placeholder" class="image-placeholder-v2" style="display: block;">No image found.</p>
            </div>
        </div>

        {{-- Right Column: Detected Features --}}
        <div class="results-card-v2 card-features">
            <h2 class="card-title-v2">
                <i class="fa-solid fa-face-smile-beam card-icon-sm"></i>
                Detected Features
            </h2>
            <div id="detected-features" class="feature-list-v2">
                <p>Loading features...</p>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Recommendations --}}
    <div class="results-card-v2 card-recommendations">
        <h2 class="card-title-v2">
            <i class="fa-solid fa-wand-magic-sparkles card-icon-sm"></i>
            Recommendations
        </h2>
        <div id="recommendations-list" class="recommendation-list-v2">
            <p>Loading recommendations...</p>
            {{-- JS will populate this --}}
        </div>
    </div>

    {{-- Save Look Button (Requires Login) --}}
    <div class="save-button-container-v2">
        <button id="save-look-button" class="button button-pink button-save-look">
            <i class="fa-regular fa-bookmark"></i> Save Look (Requires Login)
        </button>
         <p id="save-error" class="form-error" style="display: none;"></p>
    </div>

</div>

{{-- --- ADD VIRTUAL TRY-ON MODAL --- --}}
<div id="tryon-modal-overlay" class="modal-overlay" style="display: none;">
    <div id="tryon-modal-content" class="modal-content">
        <button id="modal-close-button" class="modal-close">&times;</button>
        <h2 class="modal-title">Virtual Try-On</h2>
        <div id="modal-spinner" class="spinner" style="display: none;"></div>
        <p id="modal-error" classs="form-error" style="display: none;"></p>
        <div id="modal-image-container" class="modal-image-container" style="display: none;">
             <img id="modal-generated-image" src="#" alt="Generated Makeup Try-On" class="modal-image" />
        </div>
        <p id="modal-prompt-text" class="modal-prompt"></p>
    </div>
</div>
{{-- --- END MODAL --- --}}
@endsection


{{-- Add/Move Page-Specific CSS to style.css --}}


{{-- Add JS to display results and handle Try-On --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Get Page Elements ---
        const featuresContainer = document.getElementById('detected-features');
        const recommendationsContainer = document.getElementById('recommendations-list');
        const saveButton = document.getElementById('save-look-button');
        const saveError = document.getElementById('save-error');
        const resultImage = document.getElementById('result-image-preview');
        const imagePlaceholder = document.getElementById('image-placeholder');
        
        // --- Get Modal Elements ---
        const modalOverlay = document.getElementById('tryon-modal-overlay');
        const modalContent = document.getElementById('tryon-modal-content');
        const modalCloseButton = document.getElementById('modal-close-button');
        const modalSpinner = document.getElementById('modal-spinner');
        const modalError = document.getElementById('modal-error');
        const modalImageContainer = document.getElementById('modal-image-container');
        const modalGeneratedImage = document.getElementById('modal-generated-image');
        const modalPromptText = document.getElementById('modal-prompt-text');

        // --- Get Data from Storage ---
        const storedResult = sessionStorage.getItem('analysisResult');
        const storedImage = sessionStorage.getItem('analysisImage'); // The original image's Data URL

        // --- Get Auth & CSRF ---
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Close Modal Listeners ---
        modalCloseButton.addEventListener('click', () => modalOverlay.style.display = 'none');
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) modalOverlay.style.display = 'none';
        });

        // --- Handle Try-On Button Click ---
        async function handleTryOn(promptText, recommendationKey) {
            if (!isLoggedIn) {
                alert('Please log in to use the Virtual Try-On feature.');
                window.location.href = "{{ route('login') }}";
                return;
            }
            if (!storedImage) {
                 alert('Error: Original image data not found. Please analyze again.');
                 return;
            }
            
            const fullPrompt = `Apply this makeup: ${promptText}. This is for a ${recommendationKey}.`;
            console.log('Starting Virtual Try-On for:', fullPrompt);

            modalOverlay.style.display = 'flex';
            modalSpinner.style.display = 'block';
            modalImageContainer.style.display = 'none';
            modalError.style.display = 'none';
            modalPromptText.textContent = `Generating: "${promptText}"...`;

            try {
                const response = await fetch('/api/virtual-tryon', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        prompt: fullPrompt, // Send the full instruction
                        image_data_url: storedImage // The original photo's Data URL
                    })
                });
                const data = await response.json();
                if (response.ok && data.generated_image_base64) {
                    console.log('Virtual Try-On Success!');
                    modalGeneratedImage.src = `data:image/jpeg;base64,${data.generated_image_base64}`;
                    modalImageContainer.style.display = 'block';
                } else { throw new Error(data.error || data.details || 'Failed to generate image.'); }
            } catch (error) {
                console.error('Virtual Try-On Error:', error);
                modalError.textContent = `Error: ${error.message}`;
                modalError.style.display = 'block';
            } finally {
                modalSpinner.style.display = 'none';
            }
        }

        // --- Display Image (Same as before) ---
        if (resultImage && imagePlaceholder && storedImage && storedImage !== '#') {
             resultImage.src = storedImage;
             resultImage.style.display = 'block';
             imagePlaceholder.style.display = 'none';
        } else if (imagePlaceholder) { /* ... */ }

        // --- Main Logic to Populate Page ---
        if (storedResult) {
            try {
                const resultData = JSON.parse(storedResult);
                const analysis = resultData.analysis || {};
                const recommendations = resultData.recommendations || {};

                // --- Display Detected Features ---
                if (featuresContainer) {
                    featuresContainer.innerHTML = '';
                    const features = [
                        { label: 'Face Shape', value: analysis.face_shape },
                        { label: 'Skin Tone', value: analysis.skin_tone_description },
                        { label: 'Eye Shape', value: analysis.eye_shape },
                        { label: 'Lip Shape', value: analysis.lip_shape },
                        { label: 'Eyebrow Shape', value: analysis.eyebrow_shape },
                    ];
                    let featuresFound = false;
                    features.forEach(feature => {
                        if (feature.value) {
                            featuresFound = true;
                            const p = document.createElement('p');
                            p.innerHTML = `<span>${feature.label}</span> <span>${feature.value}</span>`; // Use span for value too
                            featuresContainer.appendChild(p);
                        }
                    });
                     if (!featuresFound) featuresContainer.innerHTML = '<p>No specific features were detected.</p>';
                }

                // --- MODIFIED: Display Recommendations (with Try-On Buttons) ---
                if (recommendationsContainer) {
                    recommendationsContainer.innerHTML = '';
                    let recsFound = false;
                    for (const key in recommendations) {
                        if (recommendations.hasOwnProperty(key) && recommendations[key]) {
                            recsFound = true;
                            
                            const recommendationText = recommendations[key];
                            const formattedLabel = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                            const itemContainer = document.createElement('div');
                            itemContainer.className = 'recommendation-list-item';

                            const textElement = document.createElement('div');
                            textElement.className = 'rec-text';
                            textElement.innerHTML = `<span>${formattedLabel}</span><p>${recommendationText}</p>`;
                            
                            const tryOnButton = document.createElement('button');
                            tryOnButton.className = 'try-on-button-v2'; // Use new button style
                            tryOnButton.textContent = 'Try On';
                            tryOnButton.dataset.prompt = recommendationText; // Store the prompt
                            tryOnButton.dataset.key = key; // Store the key (e.g., 'Lipstick')
                            tryOnButton.addEventListener('click', () => handleTryOn(tryOnButton.dataset.prompt, tryOnButton.dataset.key));

                            itemContainer.appendChild(textElement);
                            itemContainer.appendChild(tryOnButton);
                            recommendationsContainer.appendChild(itemContainer);
                        }
                    }
                    if (!recsFound) recommendationsContainer.innerHTML = '<p>No specific recommendations available yet.</p>';
                }

                 // --- Save Button Logic (Same as before) ---
                 if (saveButton) { /* ... add event listener ... */ }

            } catch (e) { /* ... error handling ... */ }
        } else { /* ... handle no results ... */ }
    });
</script>
@endpush