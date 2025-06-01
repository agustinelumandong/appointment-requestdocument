<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\DocumentType;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class DocumentRequestController extends Controller
{
    public function showForm(): View
    {
        $documentTypes = DocumentType::all();
        return view('frontend.document_request', compact('documentTypes'));
    }

    public function store(Request $request): JsonResponse
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication required. Please log in to submit a document request.'
            ], 401);
        }

        $user = Auth::user();

        try {
            $validated = $request->validate([
                'document_type' => 'required|string',
                'for_whom' => 'required|string',
                'application_data' => 'required|array',
                'purpose' => 'required|string',
                'contact_first_name' => 'required|string|max:255',
                'contact_middle_name' => 'nullable|string|max:255',
                'contact_last_name' => 'required|string|max:255',
                'contact_phone' => 'required|string|max:20',
                'contact_email' => 'required|email|max:255',
                'claim_date' => 'required|date|after_or_equal:today',
                'claim_time' => 'required|string',
            ]);

            // Generate reference number
            $reference = 'DOC-' . strtoupper(Str::random(8));

            // Use database transaction for data consistency
            $docRequest = DB::transaction(function () use ($validated, $reference, $user) {
                return DocumentRequest::create([
                    'user_id' => $user->id, // Use authenticated user's ID
                    'reference_number' => $reference,
                    'document_type' => $validated['document_type'],
                    'for_whom' => $validated['for_whom'],
                    'application_data' => json_encode($validated['application_data']),
                    'purpose' => $validated['purpose'],
                    'contact_first_name' => $validated['contact_first_name'],
                    'contact_middle_name' => $validated['contact_middle_name'] ?? '',
                    'contact_last_name' => $validated['contact_last_name'],
                    'contact_phone' => $validated['contact_phone'],
                    'contact_email' => $validated['contact_email'],
                    'claim_date' => $validated['claim_date'],
                    'claim_time' => $validated['claim_time'],
                    'status' => 'Pending',
                ]);
            });

            // Log successful creation
            Log::info('Document request created successfully', [
                'reference_number' => $reference,
                'user_id' => $user->id,
                'document_type' => $validated['document_type']
            ]);

            // Send confirmation email (implement your Mailable when ready)
            // Mail::to($docRequest->contact_email)->send(new \App\Mail\DocumentRequestConfirmation($docRequest));

            // return response()->json([
            //     'success' => true,
            //     'reference_number' => $reference,
            //     'message' => 'Document request submitted successfully.'
            // ]);

            // Redirect to dashboard after successful submission
            return response()->json([
                'success' => true,
                'reference_number' => $reference,
                'message' => 'Document request submitted successfully.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating document request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing your request. Please try again.'
            ], 500);
        }
    }

    public function status(string $reference): View
    {
        $docRequest = DocumentRequest::where('reference_number', $reference)->firstOrFail();
        return view('frontend.document_status', compact('docRequest'));
    }
}
