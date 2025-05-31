<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\DocumentType;

final class DocumentRequestController extends Controller
{
    public function showForm()
    {
        $documentTypes = DocumentType::all();
        return view('frontend.document_request', compact('documentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'for_whom' => 'required|string',
            'application_data' => 'required|array',
            'purpose' => 'required|string',
            'delivery_method' => 'required|string',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|string',
            'contact_email' => 'required|email',
            'claim_date' => 'required|date|after_or_equal:today',
            'claim_time' => 'required',
        ]);

        $reference = 'DOC-' . strtoupper(Str::random(8));
        $docRequest = DocumentRequest::create([
            'reference_number' => $reference,
            'document_type' => $validated['document_type'],
            'for_whom' => $validated['for_whom'],
            'application_data' => json_encode($validated['application_data']),
            'purpose' => $validated['purpose'],
            'delivery_method' => $validated['delivery_method'],
            'contact_name' => $validated['contact_name'],
            'contact_phone' => $validated['contact_phone'],
            'contact_email' => $validated['contact_email'],
            'claim_date' => $validated['claim_date'],
            'claim_time' => $validated['claim_time'],
            'status' => 'Pending',
        ]);

        // Send confirmation email (implement your Mailable)
        // Mail::to($docRequest->contact_email)->send(new \App\Mail\DocumentRequestConfirmation($docRequest));

        return response()->json([
            'success' => true,
            'reference_number' => $reference,
        ]);
    }

    public function status($reference)
    {
        $docRequest = DocumentRequest::where('reference_number', $reference)->firstOrFail();
        return view('frontend.document_status', compact('docRequest'));
    }
}