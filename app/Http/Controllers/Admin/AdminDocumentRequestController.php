<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AdminDocumentRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:document-requests.view')->except(['update', 'destroy']);
        $this->middleware('can:document-requests.edit')->only(['update']);
        $this->middleware('can:document-requests.delete')->only(['destroy']);
    }

    /**
     * Display a listing of all document requests
     */
    public function index(Request $request): View
    {
        $query = DocumentRequest::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('contact_first_name', 'like', "%{$search}%")
                    ->orWhere('contact_last_name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%")
                    ->orWhere('document_type', 'like', "%{$search}%");
            });
        }

        $documentRequests = $query->paginate(15);

        $statusCounts = [
            'total' => DocumentRequest::count(),
            'pending' => DocumentRequest::where('status', 'Pending')->count(),
            'processing' => DocumentRequest::where('status', 'Processing')->count(),
            'ready' => DocumentRequest::where('status', 'Ready for Pickup')->count(),
            'completed' => DocumentRequest::where('status', 'Completed')->count(),
            'rejected' => DocumentRequest::where('status', 'Rejected')->count(),
        ];

        return view('backend.document-requests.index', compact('documentRequests', 'statusCounts'));
    }

    /**
     * Display pending document requests
     */
    public function pending(): View
    {
        $documentRequests = DocumentRequest::with('user')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('backend.document-requests.pending', compact('documentRequests'));
    }

    /**
     * Display document request statistics
     */
    public function stats(): View
    {
        $stats = [
            'total_requests' => DocumentRequest::count(),
            'pending_requests' => DocumentRequest::where('status', 'Pending')->count(),
            'completed_requests' => DocumentRequest::where('status', 'Completed')->count(),
            'monthly_requests' => DocumentRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'today_requests' => DocumentRequest::whereDate('created_at', today())->count(),
        ];

        // Get requests by document type
        $requestsByType = DocumentRequest::select('document_type', DB::raw('count(*) as count'))
            ->groupBy('document_type')
            ->orderByDesc('count')
            ->get();

        // Get monthly request trends (last 12 months)
        $monthlyTrends = DocumentRequest::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('backend.document-requests.stats', compact('stats', 'requestsByType', 'monthlyTrends'));
    }

    /**
     * Display the specified document request
     */
    public function show(DocumentRequest $documentRequest): View
    {
        $documentRequest->load('user');
        return view('backend.document-requests.show', compact('documentRequest'));
    }

    /**
     * Update the specified document request status
     */
    public function update(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Processing,Ready for Pickup,Completed,Rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($documentRequest, $validated) {
                $documentRequest->update([
                    'status' => $validated['status'],
                    'admin_notes' => $validated['admin_notes'] ?? $documentRequest->admin_notes,
                    'updated_at' => now(),
                ]);

                // Log the status change
                Log::info('Document request status updated', [
                    'reference_number' => $documentRequest->reference_number,
                    'old_status' => $documentRequest->getOriginal('status'),
                    'new_status' => $validated['status'],
                    'admin_id' => auth()->id(),
                ]);
            });

            return redirect()
                ->route('admin.document-requests.show', $documentRequest)
                ->with('success', 'Document request status updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating document request status', [
                'reference_number' => $documentRequest->reference_number,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the document request.');
        }
    }

    /**
     * Update status via AJAX
     */
    public function updateStatus(Request $request, DocumentRequest $documentRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Processing,Ready for Pickup,Completed,Rejected',
        ]);

        try {
            $documentRequest->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'new_status' => $validated['status']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }

    /**
     * Remove the specified document request
     */
    public function destroy(DocumentRequest $documentRequest): RedirectResponse
    {
        try {
            $referenceNumber = $documentRequest->reference_number;
            $documentRequest->delete();

            Log::info('Document request deleted', [
                'reference_number' => $referenceNumber,
                'admin_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.document-requests.index')
                ->with('success', 'Document request deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting document request', [
                'reference_number' => $documentRequest->reference_number,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while deleting the document request.');
        }
    }
}
