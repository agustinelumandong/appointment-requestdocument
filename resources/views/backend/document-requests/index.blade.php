@extends('adminlte::page')

@section('title', 'Document Requests')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Document Requests</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Document Requests</li>
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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $statusCounts['total'] ?? 0 }}</h3>
                        <p>Total Requests</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $statusCounts['pending'] ?? 0 }}</h3>
                        <p>Pending</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $statusCounts['processing'] ?? 0 }}</h3>
                        <p>Processing</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $statusCounts['ready'] ?? 0 }}</h3>
                        <p>Ready for Pickup</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $statusCounts['completed'] ?? 0 }}</h3>
                        <p>Completed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $statusCounts['rejected'] ?? 0 }}</h3>
                        <p>Rejected</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter and Search</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.document-requests.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Processing" {{ request('status') == 'Processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="Ready for Pickup" {{ request('status') == 'Ready for Pickup' ? 'selected' : '' }}>Ready for Pickup</option>
                                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" name="search" id="search" class="form-control"
                                       placeholder="Search by reference number, name, email, or document type..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Document Requests Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Document Requests</h3>
            </div>
            <div class="card-body">
                @if($documentRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Reference #</th>
                                    <th>Applicant</th>
                                    <th>Document Type</th>
                                    <th>For Whom</th>
                                    <th>Status</th>
                                    <th>Claim Date</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentRequests as $request)
                                    <tr>
                                        <td>
                                            <strong>{{ $request->reference_number }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $request->contact_first_name }} {{ $request->contact_last_name }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $request->contact_email }}</small>
                                        </td>
                                        <td>{{ $request->document_type }}</td>
                                        <td>{{ $request->for_whom }}</td>
                                        <td>
                                            <span class="badge badge-{{
                                                $request->status == 'Pending' ? 'warning' :
                                                ($request->status == 'Processing' ? 'primary' :
                                                ($request->status == 'Ready for Pickup' ? 'info' :
                                                ($request->status == 'Completed' ? 'success' : 'danger')))
                                            }}">
                                                {{ $request->status }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($request->claim_date)->format('M d, Y') }}
                                            <small class="d-block text-muted">{{ $request->claim_time }}</small>
                                        </td>
                                        <td>
                                            {{ $request->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('document-requests.view')
                                                    <a href="{{ route('admin.document-requests.show', $request) }}"
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('document-requests.delete')
                                                    <form method="POST"
                                                          action="{{ route('admin.document-requests.destroy', $request) }}"
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Are you sure you want to delete this document request?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $documentRequests->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No document requests found</h5>
                        <p class="text-muted">There are no document requests matching your criteria.</p>
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
        });
    </script>
@stop
