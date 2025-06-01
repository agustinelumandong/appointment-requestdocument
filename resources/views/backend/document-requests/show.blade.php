@extends('adminlte::page')

@section('title', 'Document Request Details')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Document Request Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.document-requests.index') }}">Document Requests</a></li>
                    <li class="breadcrumb-item active">{{ $documentRequest->reference_number }}</li>
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
        <div class="row">
            <!-- Main Document Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt mr-2"></i>
                            Request #{{ $documentRequest->reference_number }}
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-{{
                                $documentRequest->status == 'Pending' ? 'warning' :
                                ($documentRequest->status == 'Processing' ? 'primary' :
                                ($documentRequest->status == 'Ready for Pickup' ? 'info' :
                                ($documentRequest->status == 'Completed' ? 'success' : 'danger')))
                            }} badge-lg">
                                {{ $documentRequest->status }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Document Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Document Type:</strong></td>
                                        <td>{{ $documentRequest->document_type }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>For Whom:</strong></td>
                                        <td>{{ $documentRequest->for_whom }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Purpose:</strong></td>
                                        <td>{{ $documentRequest->purpose }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Contact Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td>{{ $documentRequest->contact_first_name }} {{ $documentRequest->contact_middle_name }} {{ $documentRequest->contact_last_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $documentRequest->contact_email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $documentRequest->contact_phone }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5>Schedule Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Claim Date:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($documentRequest->claim_date)->format('F d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Claim Time:</strong></td>
                                        <td>{{ $documentRequest->claim_time }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Request Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Submitted:</strong></td>
                                        <td>{{ $documentRequest->created_at->format('F d, Y g:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td>{{ $documentRequest->updated_at->format('F d, Y g:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($documentRequest->application_data)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Application Data</h5>
                                    <div class="border p-3 rounded">
                                        @php
                                            $appData = json_decode($documentRequest->application_data, true);
                                        @endphp
                                        @if(is_array($appData))
                                            <div class="row">
                                                @foreach($appData as $key => $value)
                                                    <div class="col-md-6 mb-2">
                                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                        {{ is_array($value) ? json_encode($value) : $value }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <pre>{{ $documentRequest->application_data }}</pre>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($documentRequest->admin_notes)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Admin Notes</h5>
                                    <div class="border p-3 rounded bg-light">
                                        {{ $documentRequest->admin_notes }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions Panel -->
            <div class="col-md-4">
                @can('document-requests.edit')
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.document-requests.update', $documentRequest) }}">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label for="status">Update Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="Pending" {{ $documentRequest->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Processing" {{ $documentRequest->status == 'Processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="Ready for Pickup" {{ $documentRequest->status == 'Ready for Pickup' ? 'selected' : '' }}>Ready for Pickup</option>
                                        <option value="Completed" {{ $documentRequest->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Rejected" {{ $documentRequest->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="admin_notes">Admin Notes</label>
                                    <textarea name="admin_notes" id="admin_notes" class="form-control" rows="4"
                                              placeholder="Add notes about this request...">{{ $documentRequest->admin_notes }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-2"></i>Update Request
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan

                <!-- User Information -->
                @if($documentRequest->user)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">User Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $documentRequest->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $documentRequest->user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Member Since:</strong></td>
                                    <td>{{ $documentRequest->user->created_at->format('F Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.document-requests.index') }}" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-arrow-left mr-2"></i>Back to List
                        </a>

                        @can('document-requests.delete')
                            <form method="POST" action="{{ route('admin.document-requests.destroy', $documentRequest) }}"
                                  onsubmit="return confirm('Are you sure you want to delete this document request? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-trash mr-2"></i>Delete Request
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        .table-borderless td {
            border: none;
            padding: 0.25rem 0.5rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            $(".alert").delay(5000).slideUp(300);
        });
    </script>
@stop
