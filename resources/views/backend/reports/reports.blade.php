@extends('adminlte::page')

@section('title', 'Reports')

@section('content')
<style>
    .full-height {
        min-height: 100vh;
        padding: 40px 20px;
        background: white;
    }

    .report-box {
        background: #f8f9fa;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        padding: 30px;
        height: 400px;
        display: flex;
        flex-direction: column;
        font-size: 1rem;
        color: #333;
        position: relative;
    }

    .mini-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 10px 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .mini-card i {
        margin-right: 8px;
    }

    .chart-title {
        font-weight: bold;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .image-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 250px;
    }

    .image-container img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    .scrollable-list {
        overflow-y: auto;
        flex-grow: 1;
        margin-bottom: 10px;
    }

    .print-btn {
        align-self: flex-end;
    }

    select.form-select-sm {
        width: 100px;
        cursor: pointer;
    }
</style>

<div class="container-fluid full-height">
    <div class="row mb-4">
        <!-- Yearly Appointments -->
        <div class="col-md-6 mb-4">
            <div class="report-box">
                <div class="chart-title">
                    <span>Yearly Appointments</span>
                    <select class="form-select form-select-sm" aria-label="Select year">
                        <option selected>2025</option>
                        <option>2024</option>
                        <option>2023</option>
                    </select>
                </div>
                <div class="image-container">
                    <img src="{{ asset('uploads/images/bargraph.png') }}" alt="Yearly Appointments Chart" />
                </div>
            </div>
        </div>

        <!-- Document Request per Status -->
        <div class="col-md-6 mb-4">
            <div class="report-box">
                <div class="chart-title">
                    <span>Document Request per Status</span>
                    <select class="form-select form-select-sm" aria-label="Select year">
                        <option selected>2025</option>
                        <option>2024</option>
                        <option>2023</option>
                    </select>
                </div>
                <div class="image-container">
                    <img src="{{ asset('uploads/images/piechart.png') }}" alt="Document Request Pie Chart" />
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Completed Appointments -->
        <div class="col-md-6 mb-4">
            <div class="report-box">
                <div class="chart-title">
                    <span>Completed Appointments</span>
                    <select class="form-select form-select-sm" aria-label="Select year">
                        <option selected>2025</option>
                        <option>2024</option>
                        <option>2023</option>
                    </select>
                </div>

                <div class="scrollable-list">
                    <div class="mini-card">
                        <div><i class="fas fa-user-circle text-primary"></i> Joyce B. Bonjoc</div>
                        <div>Appointment</div>
                    </div>
                    <div class="mini-card">
                        <div><i class="fas fa-user-circle text-primary"></i> Virgenia C. Miedes</div>
                        <div>Appointment</div>
                    </div>
                    <div class="mini-card">
                        <div><i class="fas fa-user-circle text-primary"></i> Alvie S. Osman</div>
                        <div>Request Document</div>
                    </div>
                    <div class="mini-card">
                        <div><i class="fas fa-user-circle text-primary"></i> Mary Jane M. Tao-on</div>
                        <div>Appointment</div>
                    </div>
                    <div class="mini-card">
                        <div><i class="fas fa-user-circle text-primary"></i> Lizle Y. Delos Reyes</div>
                        <div>Request Document</div>
                    </div>
                    <div class="mini-card">
                        <div><i class="fas fa-user-circle text-primary"></i> Ladie Rose C. Chicote</div>
                        <div>Appointment</div>
                    </div>
                    <!-- Add more if needed -->
                </div>

                <button class="btn btn-success btn-sm print-btn">Print</button>
            </div>
        </div>

        <!-- Insurance Count by Status -->
        <div class="col-md-6 mb-4">
            <div class="report-box">
                <div class="chart-title">
                    <div>Insurance Count by Status</div>
                    
                </div>
                <div class="image-container">
                    <img src="{{ asset('uploads/images/bargraph2.svg') }}" alt="bargraph" />
                </div>
            </div>
        </div>
    </div>
</div>
@endsection