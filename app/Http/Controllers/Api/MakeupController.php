<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Gemini\Laravel\Facades\Gemini;
// Removed: use Gemini\Data\Content; // We won't use Content::parse explicitly
use Gemini\Data\Part; // Keep Part import (might be used internally)
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;

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

            // 2. Prepare the prompt for Gemini
            $prompt = <<<PROMPT
            Analyze the facial features in the provided image for makeup recommendations.
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

            // --- CORRECTED GEMINI CALL (v11 - Simpler Array Argument) ---
            $result = Gemini::generativeModel(model: 'gemini-1.5-flash-latest') // <-- CHANGED MODEL NAME
                ->generateContent(
                    [
                        $prompt,
                        new Blob(
                            mimeType: MimeType::tryFrom($imageMimeType) ?? MimeType::IMAGE_JPEG,
                            data: $imageData
                        )
                    ]
                );// End generateContent arguments
            // --- END CORRECTED GEMINI CALL (v11) ---

            // 4. Process the response
            $analysisJson = $result->text();
            Log::info('Gemini Raw Response: ' . $analysisJson);
            $analysisJson = trim(str_replace(['```json', '```'], '', $analysisJson));

            $analysisData = json_decode($analysisJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode Gemini JSON: ' . json_last_error_msg() . ' | Raw: ' . $analysisJson);
                if (preg_match('/\{.*\}/s', $analysisJson, $matches)) {
                    $analysisData = json_decode($matches[0], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('AI response unclear.');
                    }
                    Log::warning('Extracted JSON from potentially unclean response.');
                } else {
                    throw new \Exception('AI response invalid.');
                }
            }

            // 5. Generate Placeholder Recommendations
            $recommendations = [
                'foundation_suggestion' => 'Suggest Foundation based on: ' . ($analysisData['skin_tone_description'] ?? 'N/A'),
                'eye_shadow_suggestion' => 'Suggest Eye Shadow based on: ' . ($analysisData['eye_shape'] ?? 'N/A') . ' & Style: ' . $validated['style'],
                'lipstick_suggestion' => 'Suggest Lipstick based on: ' . ($analysisData['lip_shape'] ?? 'N/A') . ' & Intensity: ' . $validated['intensity'],
            ];
            Log::info('Analysis successful.');

            // 6. Return results
            return response()->json([
                'message' => 'Analysis complete!',
                'analysis' => $analysisData,
                'recommendations' => $recommendations
            ]);

        } catch (\Exception $e) {
            // Log detailed error
            Log::error('Makeup Analysis Failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log the full stack trace for debugging
            // Return generic error to the user
            return response()->json(['error' => 'Analysis failed. Please try again later.', 'details' => $e->getMessage()], 500);
        }
    }
}