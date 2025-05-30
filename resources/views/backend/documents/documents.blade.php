@extends('adminlte::page')

@section('title', 'My Documents')

@section('content_header')
    <h1>Documents</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Available Documents</h4>
    </div>
    <div class="card-body">
        <!-- Row Card 1 -->
        <div class="mb-3 p-3 rounded shadow-sm d-flex justify-content-between align-items-center" style="background-color: #f9f9f9;">
            <div>
                <strong>Birth Certificate</strong><br>
                <small class="text-muted">Office: MCR</small>
            </div>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#birthModal">
                <i class="fas fa-eye"></i> View
            </button>
        </div>

        <!-- Row Card 2 -->
        <div class="mb-3 p-3 rounded shadow-sm d-flex justify-content-between align-items-center" style="background-color: #f9f9f9;">
            <div>
                <strong>Death Certificate</strong><br>
                <small class="text-muted">Office: MCR</small>
            </div>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#deathModal">
                <i class="fas fa-eye"></i> View
            </button>
        </div>
    </div>
</div>

<!-- Birth Certificate Modal -->
<div class="modal fade" id="birthModal" tabindex="-1" role="dialog" aria-labelledby="birthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Birth Certificate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('uploads/images/birth.WEBP') }}" class="img-fluid rounded" alt="Birth Certificate">
            </div>
        </div>
    </div>
</div>

<!-- Death Certificate Modal -->
<div class="modal fade" id="deathModal" tabindex="-1" role="dialog" aria-labelledby="deathModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Death Certificate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('uploads/images/birth.WEBP') }}" class="img-fluid rounded" alt="Death Certificate">
            </div>
        </div>
    </div>
</div>
@stop