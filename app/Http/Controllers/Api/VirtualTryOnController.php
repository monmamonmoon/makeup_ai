<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Illuminate\Support\Facades\Auth; // To check if user is logged in for protected routes

class VirtualTryOnController extends Controller
{
    /**
     * Handle the virtual try-on request.
     * Takes an image and makeup prompt, returns a generated image.
     */
    public function tryOn(Request $request)
    {
        // Ensure the user is authenticated if this route is protected
        // (We will add 'auth:sanctum' middleware to the route later for API auth,
        // or 'auth:web' if we keep it a web route with web sessions)
        if (!Auth::check()) {
            Log::warning('Unauthorized virtual try-on attempt.');
            return response()->json(['error' => 'Authentication required.'], 401);
        }

        // 1. Validate the request
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
            'image_data_url' => 'required|string', // Base64 Data URL
        ]);

        try {
            $prompt = $validated['prompt'];
            $imageDataUrl = $validated['image_data_url'];

            // Extract base64 data and mime type from Data URL
            list($mimeType, $base64Image) = explode(';', $imageDataUrl);
            list(, $base64Image) = explode(',', $base64Image);
            $mimeType = str_replace('data:', '', $mimeType); // e.g., 'image/jpeg'

            // 2. Prepare the prompt for Gemini Vision (or Imagen later)
            // We'll use Gemini-Pro-Vision to modify the image based on the prompt.
            // Note: Generative models can sometimes refuse or struggle with direct image modification.
            // For a robust solution, a dedicated image editing model or service (like Imagen, DALL-E)
            // might be integrated here instead of simple Gemini Pro Vision if its capabilities aren't sufficient.
            $geminiPrompt = "You are a professional makeup artist. Apply the following makeup to the person in the image, ensuring the changes are realistic and blend naturally. Focus only on the makeup application described: \"{$prompt}\"";

            Log::info('Calling Gemini Vision API for virtual try-on...', ['user_prompt' => $prompt]);

            // 3. Call the Gemini API with image and text
            // Using 'models/gemini-pro-vision-latest' for image understanding/generation
            $result = Gemini::generativeModel(model: 'models/gemini-pro-vision-latest')
                ->generateContent(
                    [
                        $geminiPrompt,
                        new Blob(
                            mimeType: MimeType::tryFrom($mimeType) ?? MimeType::IMAGE_JPEG,
                            data: $base64Image
                        )
                    ]
                );

            // 4. Process the response
            $generatedImagePart = null;
            foreach ($result->parts as $part) {
                if ($part instanceof Blob && str_starts_with($part->mimeType, 'image/')) {
                    $generatedImagePart = $part;
                    break;
                }
            }

            if (!$generatedImagePart) {
                Log::warning('Gemini did not return an image part for try-on.', ['gemini_response' => $result->text()]);
                return response()->json(['error' => 'AI could not generate the makeup image. Please try a different prompt.'], 500);
            }

            $generatedImageBase64 = $generatedImagePart->data;
            $generatedImageMimeType = $generatedImagePart->mimeType;

            Log::info('Virtual try-on image generated successfully.');

            // 5. Return the generated image (base64 encoded)
            return response()->json([
                'message' => 'Virtual try-on complete!',
                'generated_image_base64' => $generatedImageBase64,
                'generated_image_mime_type' => $generatedImageMimeType,
            ]);

        } catch (\Exception $e) {
            Log::error('Virtual Try-On Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_prompt' => $request->input('prompt')
            ]);
            return response()->json(['error' => 'Failed to generate image. Please try again later.', 'details' => $e->getMessage()], 500);
        }
    }
}