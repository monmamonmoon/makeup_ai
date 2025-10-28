<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Gemini\Laravel\Facades\Gemini;
// ... (other use statements) ...
use Google\Cloud\Aiplatform\V1\PredictionServiceClient;
use Google\Cloud\Aiplatform\V1\Client\PredictionServiceClient as PredictionServiceGapicClient; // Use the GAPIC client
use Google\Protobuf\Value;
// Import the classes needed for the Gemini call
use Gemini\Data\Part;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
// We don't need Content::parse if passing the array directly
// use Gemini\Data\Content; 

class MakeupController extends Controller
{
    public function analyze(Request $request)
    {
        // 1. Validate incoming request
        $validated = $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
            'style' => 'required|string|in:Feminine,Masculine',
            'intensity' => 'required|string|in:Fresh,Natural,Heavy',
        ]);

        try {
            $imageFile = $request->file('image');
            $imageMimeType = $imageFile->getMimeType();
            $imageData = base64_encode($imageFile->get());

            // 2. Prepare the prompt for Gemini (Facial Analysis)
            $prompt = <<<PROMPT
            Analyze the facial features in the provided image.
            Identify the following features and return them as a JSON object:
            - face_shape: (e.g., Oval, Round, Square, Heart, Long, Diamond)
            - skin_tone_description: (e.g., Light skin with cool undertones, Medium skin with warm undertones, Deep skin with neutral undertones)
            - eye_shape: (e.g., Almond, Round, Hooded, Monolid, Downturned, Upturned)
            - lip_shape: (e.g., Full, Thin, Heart-shaped, Wide, Bow-shaped)
            - eyebrow_shape: (e.g., Arched, Straight, Rounded, S-shaped)

            Please only return the JSON object, without any introductory text or markdown formatting. Example:
            {
              "face_shape": "Oval",
              "skin_tone_description": "Medium skin with warm undertones",
              "eye_shape": "Almond",
              "lip_shape": "Full",
              "eyebrow_shape": "Arched"
            }
            PROMPT;

            // 3. Call the Gemini API
            Log::info('Calling Gemini API for analysis...');

            // Use the model name from our successful test
            $result = Gemini::generativeModel(model: 'models/gemini-pro-latest') 
                ->generateContent(
                     [
                         $prompt,
                         new Blob(
                             mimeType: MimeType::tryFrom($imageMimeType) ?? MimeType::IMAGE_JPEG,
                             data: $imageData
                         )
                     ]
                 );
                 

             // 4. Process the response
             $analysisJson = $result->text();
             Log::info('Gemini Raw Response: ' . $analysisJson);
             $analysisJson = trim(str_replace(['```json', '```'], '', $analysisJson));

             $analysisData = json_decode($analysisJson, true);

             // Check for JSON decoding errors
             if (json_last_error() !== JSON_ERROR_NONE) {
                 Log::error('Failed to decode Gemini JSON: ' . json_last_error_msg() . ' | Raw: ' . $analysisJson);
                 if (preg_match('/\{.*\}/s', $analysisJson, $matches)) {
                     $analysisData = json_decode($matches[0], true);
                     if (json_last_error() !== JSON_ERROR_NONE) { throw new \Exception('AI response unclear.'); }
                     Log::warning('Extracted JSON from potentially unclean response.');
                 } else { throw new \Exception('AI response invalid.'); }
             }

             // --- 5. Generate More Detailed Recommendations ---
             Log::info('Generating detailed recommendations...');

             // Safely get analysis results with defaults
             $skinTone = $analysisData['skin_tone_description'] ?? 'unknown';
             $eyeShape = $analysisData['eye_shape'] ?? 'unknown';
             $lipShape = $analysisData['lip_shape'] ?? 'unknown';
             $faceShape = $analysisData['face_shape'] ?? 'unknown';
             $eyebrowShape = $analysisData['eyebrow_shape'] ?? 'unknown';
             
             // Get user preferences
             $style = $validated['style'];
             $intensity = $validated['intensity'];

             // --- Recommendation Logic (This replaces the old simple placeholders) ---
             
             // Foundation
             $foundationRec = "For $skinTone, a foundation matching your undertones is key.";
             if ($intensity === 'Heavy') $foundationRec .= " Try a full-coverage matte finish.";
             elseif ($intensity === 'Natural') $foundationRec .= " A medium-coverage, natural finish is recommended.";
             else $foundationRec .= " A light-coverage dewy finish or tinted moisturizer would be ideal.";

             // Concealer
             $concealerRec = "Choose a concealer based on $intensity coverage needs. Use one shade lighter than your foundation for highlighting under the eyes.";

             // Contour/Blush (Example based on face shape)
             $contourRec = "Contour placement should complement your $faceShape shape.";
             $blushRec = "Apply blush suitable for your $skinTone and $faceShape face.";
              if ($faceShape === 'Round') $blushRec .= " Blend upwards along the cheekbones to add definition.";
              elseif ($faceShape === 'Square') $blushRec .= " Apply to the apples of the cheeks in a circular motion to soften angles.";

             // Eyeshadow (Example based on eye shape and style)
             $eyeShadowRec = "Your $eyeShape eyes can be enhanced with this technique:";
             if ($style === 'Feminine') {
                 if ($intensity === 'Heavy') $eyeShadowRec .= " A dramatic smoky eye using deep plums or browns would suit a '$intensity' look.";
                 else $eyeShadowRec .= " Soft, neutral shades (like beige or taupe) blended well will look '$intensity' and natural.";
             } else { // Masculine
                 if ($intensity !== 'Fresh') $eyeShadowRec .= " Use a matte neutral shadow (slightly darker than your skin) in the crease to add subtle definition.";
                 else $eyeShadowRec .= " For a '$intensity' look, often just grooming or a clear brow gel is sufficient.";
             }

             // Eyeliner
             $eyelinerRec = "For $eyeShape eyes and a $style style, a subtle eyeliner ($intensity intensity) can define the lash line.";

             // Eyebrows
             $eyebrowRec = "Groom your brows to follow their natural $eyebrowShape shape. Use a pencil or powder to lightly fill in sparse areas if needed.";

             // Lipstick
             $lipstickRec = "For $lipShape lips and a $style, $intensity look:";
             if ($style === 'Feminine') {
                  if ($intensity === 'Heavy') $lipstickRec .= " Try bold colors like deep reds or berries.";
                  elseif ($intensity === 'Natural') $lipstickRec .= " Nude or soft pink shades are a great choice.";
                  else $lipstickRec .= " A tinted lip balm or light gloss is perfect.";
             } else { // Masculine
                 if ($intensity !== 'Heavy') $lipstickRec .= " A clear, matte lip balm or a very subtle nude tint is advised.";
                 else $lipstickRec .= " Deeper neutral tones could be used for a more defined look.";
             }
             // --- End Recommendation Logic ---

             // Structure recommendations with clear keys
             $recommendations = [
                 'Foundation' => $foundationRec,
                 'Concealer' => $concealerRec,
                 'Contour' => $contourRec,
                 'Blush' => $blushRec,
                 'Eyeshadow' => $eyeShadowRec,
                 'Eyeliner' => $eyelinerRec,
                 'Eyebrows' => $eyebrowRec,
                 'Lipstick' => $lipstickRec,
             ];

             Log::info('Generated recommendations:', $recommendations);
             // --- End Recommendation Generation ---

            // 6. Return results
            return response()->json([
                'message' => 'Analysis complete!',
                'analysis' => $analysisData,
                'recommendations' => $recommendations // Now contains more detailed strings
            ]);

        } catch (\Exception $e) {
            Log::error('Makeup Analysis Failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Analysis failed. Please try again later.', 'details' => $e->getMessage()], 500);
        }
    }
    /**
     * Apply virtual makeup to an image using Google's Imagen API.
     * Fulfills Objective #4: Virtual Try-On 
     */
    public function virtualTryOn(Request $request)
    {
        // 1. Validate the incoming request
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000', // The makeup instruction (e.g., "Apply red lipstick")
            'image_data_url' => 'required|string', // The base64 Data URL of the original image
        ]);

        Log::info('Virtual Try-On request received for prompt: ' . $validated['prompt']);

        try {
            // 2. Prepare Google Cloud Service Account credentials
            $credentialsPath = storage_path('app/secrets/google-credentials.json');
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Service account key file not found.');
            }

            // 3. Prepare Image Data (remove "data:image/png;base64," prefix)
            $imageDataUrlParts = explode(',', $validated['image_data_url'], 2);
            if (count($imageDataUrlParts) !== 2) {
                throw new \Exception('Invalid image data URL format.');
            }
            $imageBase64 = $imageDataUrlParts[1];

            // 4. Set up the Vertex AI Prediction Client
            $clientOptions = ['credentials' => $credentialsPath];
            // TODO: Replace 'us-central1' with your project's region if different
            $apiEndpoint = 'us-central1-aiplatform.googleapis.com';

            $predictionClient = new PredictionServiceGapicClient(array_merge($clientOptions, ['apiEndpoint' => $apiEndpoint]));

            // 5. Define Model Endpoint
            // This requires your Project ID and Location.
            // Model 'imagegeneration@006' is an example for Imagen 3 image editing.
            // You MUST replace 'YOUR_PROJECT_ID' and 'us-central1'
            $endpoint = sprintf(
                'projects/%s/locations/%s/publishers/google/models/imagegeneration@006', // Example Imagen 3 model
                env('GOOGLE_CLOUD_PROJECT_ID', 'YOUR_PROJECT_ID'), // Add YOUR_PROJECT_ID to .env or paste it here
                'us-central1' // TODO: Update location if different
            );

            // 6. Create the instance payload for the API
            $instance = new Value([
                'struct_value' => [
                    'fields' => [
                        'prompt' => new Value(['string_value' => $validated['prompt']]), // The instruction
                        'image' => new Value(['struct_value' => [ // The base image
                            'fields' => ['bytesB64Encoded' => new Value(['string_value' => $imageBase64])]
                        ]]),
                        // TODO: Add mask if needed (more complex, requires drawing mask on frontend)
                        // For now, we rely on prompt-based editing.
                    ]
                ]
            ]);

            // 7. Call the Vertex AI (Imagen) API
            Log::info('Calling Vertex AI Imagen API...');
            $predictRequest = (new \Google\Cloud\Aiplatform\V1\PredictRequest())
                ->setEndpoint($endpoint)
                ->setInstances([$instance]);

            $response = $predictionClient->predict($predictRequest);
            Log::info('Vertex AI Imagen response received.');

            // 8. Process the response
            $predictions = $response->getPredictions();
            if (empty($predictions)) {
                throw new \Exception('No predictions returned from Imagen API.');
            }

            // Get the generated image data (base64)
            // The exact response structure may vary! Log it to be sure.
            // Log::info('Imagen Response: ' . $predictions[0]->serializeToJsonString());
            $generatedImageBase64 = $predictions[0]->getStructValue()->getFields()['bytesB64Encoded']->getStringValue();

            if (empty($generatedImageBase64)) {
                 throw new \Exception('Generated image data was empty.');
            }

            // 9. Return the new image data to the frontend
            return response()->json([
                'message' => 'Virtual try-on successful!',
                'generated_image_base64' => $generatedImageBase64,
            ]);

        } catch (\Exception $e) {
            Log::error('Virtual Try-On Failed: ' . $e->getMessage());
            // Log the full trace for detailed debugging
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Virtual try-on failed. Please try again.', 'details' => $e->getMessage()], 500);
        } finally {
            if (isset($predictionClient)) {
                $predictionClient->close(); // Close the client connection
            }
        }
    }
}