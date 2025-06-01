@extends('adminlte::page')

@section('title', 'Pending Document Requests')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Pending Document Requests</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.document-requests.index') }}">Document Requests</a></li>
                    <li class="breadcrumb-item active">Pending</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="container-fluid">
        <!-- Alert for pending requests -->
        @if($documentRequests->count() > 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>{{ $documentRequests->count() }}</strong> document request(s) are waiting for your attention.
            </div>
        @endif

        <!-- Pending Document Requests -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-2"></i>
                    Pending Document Requests
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.document-requests.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-list mr-1"></i>View All Requests
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($documentRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="15%">Reference #</th>
                                    <th width="20%">Applicant</th>
                                    <th width="15%">Document Type</th>
                                    <th width="15%">For Whom</th>
                                    <th width="15%">Claim Date</th>
                                    <th width="10%">Submitted</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentRequests as $request)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $request->reference_number }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $request->contact_first_name }} {{ $request->contact_last_name }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $request->contact_email }}</small><br>
                                            <small class="text-muted">{{ $request->contact_phone }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $request->document_type }}</span>
                                        </td>
                                        <td>{{ $request->for_whom }}</td>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($request->claim_date)->format('M d, Y') }}</strong>
                                            <small class="d-block text-muted">{{ $request->claim_time }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $request->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                @can('document-requests.view')
                                                    <a href="{{ route('admin.document-requests.show', $request) }}"
                                                       class="btn btn-info btn-sm mb-1" title="View Details">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                @endcan

                                                @can('document-requests.edit')
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm dropdown-toggle"
                                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fas fa-cogs"></i> Process
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item quick-status"
                                                               href="#"
                                                               data-id="{{ $request->id }}"
                                                               data-status="Processing">
                                                                <i class="fas fa-play text-primary"></i> Start Processing
                                                            </a>
                                                            <a class="dropdown-item quick-status"
                                                               href="#"
                                                               data-id="{{ $request->id }}"
                                                               data-status="Ready for Pickup">
                                                                <i class="fas fa-check text-info"></i> Mark Ready
                                                            </a>
                                                            <a class="dropdown-item quick-status"
                                                               href="#"
                                                               data-id="{{ $request->id }}"
                                                               data-status="Rejected">
                                                                <i class="fas fa-times text-danger"></i> Reject
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $documentRequests->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4 class="text-success">All Caught Up!</h4>
                        <p class="text-muted">There are no pending document requests at the moment.</p>
                        <a href="{{ route('admin.document-requests.index') }}" class="btn btn-primary">
                            <i class="fas fa-list mr-2"></i>View All Requests
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            $(".alert").delay(5000).slideUp(300);

            // Quick status update
            $('.quick-status').on('click', function(e) {
                e.preventDefault();

                const requestId = $(this).data('id');
                const newStatus = $(this).data('status');
                const row = $(this).closest('tr');

                if (!confirm(`Are you sure you want to change the status to "${newStatus}"?`)) {
                    return;
                }

                // Show loading state
                const button = $(this).closest('.btn-group').find('.dropdown-toggle');
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: `/admin/document-requests/${requestId}/status`,
                    method: 'PATCH',
                    data: {
                        status: newStatus,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                              response.message +
                              '<button type="button" class="close" data-dismiss="alert">' +
                              '<span>&times;</span></button></div>'
                            ).prependTo('.container-fluid').delay(3000).slideUp(300);

                            // Remove the row since it's no longer pending
                            row.fadeOut(500, function() {
                                $(this).remove();

                                // Check if no more rows
                                if ($('tbody tr').length === 0) {
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function() {
                        alert('Failed to update status. Please try again.');
                        button.prop('disabled', false).html('<i class="fas fa-cogs"></i> Process');
                    }
                });
            });
        });
    </script>
@stop
