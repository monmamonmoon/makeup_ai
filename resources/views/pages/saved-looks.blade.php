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
                    <img id="result-image-preview" src="#" alt="Analyzed Photo" class="result-image-preview-v2"
                        style="display: none;" />
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
        document.addEventListener('DOMContentLoaded', function () {
            // --- Get Page Elements ---
            const featuresContainer = document.getElementById('detected-features');
            const recommendationsContainer = document.getElementById('recommendations-list');
            const saveButton = document.getElementById('save-look-button');
            const saveError = document.getElementById('save-error');
            const resultImage = document.getElementById('result-image-preview');
            const imagePlaceholder = document.getElementById('image-placeholder');

            // --- Get "Customize Try-On" Modal Elements ---
            const customizeModalOverlay = document.getElementById('customize-tryon-modal-overlay');
            const customizeModalClose = document.getElementById('customize-tryon-modal-close');
            const customizeModalSubtitle = document.getElementById('customize-tryon-modal-subtitle');
            const customizeOriginalImage = document.getElementById('customize-original-image-preview');
            const generateTryOnButton = document.getElementById('generate-tryon-button');
            const colorPaletteInputs = document.querySelectorAll('input[name="makeup_color"]');
            const intensitySlider = document.getElementById('makeup-intensity');
            const intensityValueSpan = document.getElementById('intensity-value');
            const additionalInstructions = document.getElementById('additional-instructions');
            const previewSpinner = document.getElementById('customize-preview-spinner');
            const previewGeneratedImage = document.getElementById('customize-generated-image');
            const previewPlaceholder = document.getElementById('customize-preview-placeholder');
            const previewError = document.getElementById('customize-preview-error');

            // --- Get Data from Storage ---
            const storedResult = sessionStorage.getItem('analysisResult');
            const storedImage = sessionStorage.getItem('analysisImage'); // The original image's Data URL

            // --- Get Auth & CSRF ---
            const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // --- Store data for the modal ---
            let currentTryOnPrompt = '';
            let currentTryOnKey = '';

            // --- Close Customize Modal Listeners ---
            if (customizeModalClose) {
                customizeModalClose.addEventListener('click', () => customizeModalOverlay.style.display = 'none');
            }
            if (customizeModalOverlay) {
                customizeModalOverlay.addEventListener('click', (e) => {
                    if (e.target === customizeModalOverlay) customizeModalOverlay.style.display = 'none';
                });
            }

            // --- Update intensity slider text ---
            if (intensitySlider && intensityValueSpan) {
                intensitySlider.addEventListener('input', (e) => {
                    intensityValueSpan.textContent = `${e.target.value}%`;
                });
            }

            // --- Handle "Try On" Button Click (Opens Customize Modal) ---
            function openCustomizeModal(promptText, recommendationKey) {
                if (!isLoggedIn) {
                    // TODO: Use the nice login modal
                    alert('Please log in to use the Virtual Try-On feature.');
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                // Store the prompt for the "Generate" button
                currentTryOnPrompt = promptText;
                currentTryOnKey = recommendationKey;

                // Set modal content
                customizeModalSubtitle.textContent = `Customize: ${promptText}`;
                if (storedImage) {
                    customizeOriginalImage.src = storedImage;
                    customizeOriginalImage.style.display = 'block';
                }

                // Reset preview area
                previewGeneratedImage.style.display = 'none';
                previewGeneratedImage.src = '#';
                previewPlaceholder.style.display = 'block';
                previewError.style.display = 'none';

                // Show the modal
                customizeModalOverlay.style.display = 'flex';
            }

            // --- Handle "Generate Try-On" Button Click (Calls API) ---
            if (generateTryOnButton) {
                generateTryOnButton.addEventListener('click', async function () {
                    if (!storedImage) {
                        alert('Error: Original image data not found. Please analyze again.');
                        return;
                    }

                    // Show spinner, hide old image/placeholder/error
                    previewSpinner.style.display = 'block';
                    previewGeneratedImage.style.display = 'none';
                    previewPlaceholder.style.display = 'none';
                    previewError.style.display = 'none';

                    // Get selected color and intensity
                    const selectedColor = document.querySelector('input[name="makeup_color"]:checked').value || 'default';
                    const selectedIntensity = intensitySlider.value; // Value from 0-100
                    const additionalNotes = additionalInstructions.value || '';

                    // Construct the prompt
                    let finalPrompt = `Apply ${currentTryOnKey}: ${currentTryOnPrompt}.`;
                    if (selectedColor !== 'default') {
                        finalPrompt += ` Use the color ${selectedColor}.`;
                    }
                    finalPrompt += ` The intensity should be around ${selectedIntensity}%.`;
                    if (additionalNotes) {
                        finalPrompt += ` Additional notes: ${additionalNotes}.`;
                    }

                    console.log('Starting Virtual Try-On API call with prompt:', finalPrompt);

                    try {
                        const response = await fetch('/virtual-tryon', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                // 'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                prompt: finalPrompt,
                                image_data_url: storedImage
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.generated_image_base64) {
                            console.log('Virtual Try-On Success!');
                            previewGeneratedImage.src = `data:${data.generated_image_mime_type || 'image/jpeg'};base64,${data.generated_image_base64}`;
                            previewGeneratedImage.style.display = 'block';
                        } else {
                            throw new Error(data.error || data.details || 'Failed to generate image.');
                        }

                    } catch (error) {
                        console.error('Virtual Try-On Error:', error);
                        previewError.textContent = `Error: ${error.message}`;
                        previewError.style.display = 'block';
                    } finally {
                        previewSpinner.style.display = 'none'; // Hide spinner
                    }
                });
            }


            // --- Display Original Image (No change) ---
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

                    // --- Display Detected Features (No change) ---
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

                    // --- MODIFIED: Display Recommendations (Connects to new modal) ---
                    if (recommendationsContainer) {
                        recommendationsContainer.innerHTML = ''; // Clear "Loading..."
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
                                tryOnButton.className = 'try-on-button-v2';
                                tryOnButton.textContent = 'Try On';

                                // MODIFIED: Add click listener to open the *customize* modal
                                tryOnButton.addEventListener('click', () => openCustomizeModal(recommendationText, formattedLabel));

                                itemContainer.appendChild(textElement);
                                itemContainer.appendChild(tryOnButton);
                                recommendationsContainer.appendChild(itemContainer);
                            }
                        }
                        if (!recsFound) recommendationsContainer.innerHTML = '<p>No specific recommendations available yet.</p>';
                    }

                    // --- Save Button Logic (No change) ---
                    if (saveButton) { /* ... (same as before) ... */ }

                } catch (e) { /* ... (error handling) ... */ }
            } else { /* ... (handle no results) ... */ }
        });
    </script>
@endpush