@extends('adminlte::page')

@section('title', 'Document Request Statistics')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Document Request Statistics</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.document-requests.index') }}">Document Requests</a></li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Overview Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_requests'] }}</h3>
                        <p>Total Requests</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <a href="{{ route('admin.document-requests.index') }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_requests'] }}</h3>
                        <p>Pending Requests</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="{{ route('admin.document-requests.pending') }}" class="small-box-footer">
                        View Pending <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['completed_requests'] }}</h3>
                        <p>Completed Requests</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('admin.document-requests.index', ['status' => 'Completed']) }}" class="small-box-footer">
                        View Completed <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $stats['monthly_requests'] }}</h3>
                        <p>This Month</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-month"></i>
                    </div>
                    <div class="small-box-footer">
                        {{ now()->format('F Y') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6 col-12">
                <div class="small-box bg-gradient-primary">
                    <div class="inner">
                        <h3>{{ $stats['today_requests'] }}</h3>
                        <p>Requests Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="small-box-footer">
                        {{ now()->format('F d, Y') }}
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="small-box bg-gradient-secondary">
                    <div class="inner">
                        <h3>{{ number_format(($stats['completed_requests'] / max($stats['total_requests'], 1)) * 100, 1) }}%</h3>
                        <p>Completion Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="small-box-footer">
                        Overall Performance
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Document Types Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Requests by Document Type
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($requestsByType->count() > 0)
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="documentTypeChart"></canvas>
                            </div>
                            <div class="mt-3">
                                @foreach($requestsByType as $type)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge-primary">{{ $type->document_type }}</span>
                                        <span class="font-weight-bold">{{ $type->count }} requests</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No data available for document types</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Monthly Trends Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>
                            Monthly Request Trends
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($monthlyTrends->count() > 0)
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="monthlyTrendsChart"></canvas>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No data available for monthly trends</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>
                            Quick Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center border-right">
                                <h5 class="text-primary">{{ $stats['total_requests'] }}</h5>
                                <p class="text-muted mb-0">Total Requests</p>
                            </div>
                            <div class="col-md-3 text-center border-right">
                                <h5 class="text-warning">{{ $stats['pending_requests'] }}</h5>
                                <p class="text-muted mb-0">Pending</p>
                            </div>
                            <div class="col-md-3 text-center border-right">
                                <h5 class="text-success">{{ $stats['completed_requests'] }}</h5>
                                <p class="text-muted mb-0">Completed</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <h5 class="text-info">{{ $stats['monthly_requests'] }}</h5>
                                <p class="text-muted mb-0">This Month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Document Type Chart
            @if($requestsByType->count() > 0)
                const documentTypeCtx = document.getElementById('documentTypeChart').getContext('2d');
                new Chart(documentTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($requestsByType->pluck('document_type')) !!},
                        datasets: [{
                            data: {!! json_encode($requestsByType->pluck('count')) !!},
                            backgroundColor: [
                                '#3498db',
                                '#e74c3c',
                                '#2ecc71',
                                '#f39c12',
                                '#9b59b6',
                                '#1abc9c',
                                '#34495e',
                                '#e67e22'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // This is already set correctly
                        aspectRatio: 1, // Add this to force square aspect ratio
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true,
                                    boxWidth: 12 // Limit legend box size
                                }
                            }
                        },
                        layout: {
                            padding: {
                                top: 10,
                                bottom: 10
                            }
                        }
                    }
                });
            @endif

            // Monthly Trends Chart - also fix this one for consistency
            @if($monthlyTrends->count() > 0)
                const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');

                new Chart(monthlyTrendsCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($monthlyTrends->map(function($item) { return \Carbon\Carbon::create($item->year, $item->month)->format('M Y'); })) !!},
                        datasets: [{
                            label: 'Requests',
                            data: {!! json_encode($monthlyTrends->pluck('count')) !!},
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3498db',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        aspectRatio: 2, // Force a 2:1 aspect ratio for line chart
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        layout: {
                            padding: {
                                top: 10,
                                bottom: 10
                            }
                        }
                    }
                });
            @endif
        });
    </script>
@stop
