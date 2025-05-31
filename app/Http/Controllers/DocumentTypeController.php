<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentType;

class DocumentTypeController extends Controller
{
    public function showForm()
{
    $documentTypes = DocumentType::all();
    $setting = \App\Models\Setting::first();
    return view('frontend.document_request', compact('documentTypes', 'setting'));
}
}
