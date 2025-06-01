const documentTypes = window.documentTypes || [];
let docState = {
    currentStep: 1,
    documentType: null,
    forWhom: null,
    applicationData: {},
    purpose: '',
    contact_first_name: '',
    contact_middle_name: '',
    contact_last_name: '',
    contact_phone: '',
    contact_email: '',
    claim_date: '',
    claim_time: ''
};

function showStep(step) {
    $('.document-step').removeClass('active');
    $('#doc-step' + step).addClass('active');
    docState.currentStep = step;
    $('#doc-prev-step').prop('disabled', step === 1);
    $('#doc-next-step').toggle(step < 6);
}

$(document).ready(function() {
    // Step 1: Render document types
    let docTypeHtml = '';
    documentTypes.forEach(dt => {
        docTypeHtml +=
        `<div class="col-md-4 mb-3">
            <div class="card doc-type-card" data-type="${dt.slug}">
                <div class="card-body text-center">
                    <h5>${dt.name}</h5>
                    <p>${dt.description || ''}</p>
                </div>
            </div>
        </div>`;
    });
    $('#document-types-container').html(docTypeHtml);

    // Step 1: Select document type with data persistence
    $(document).on('click', '.doc-type-card', function() {
        $('.doc-type-card').removeClass('border-primary');
        $(this).addClass('border-primary');
        docState.documentType = $(this).data('type');
        // Save immediately when selection changes
        saveCurrentStepData();
    });

    // Step 2: For whom with data persistence
    $(document).on('click', '#for-whom-container button', function() {
        docState.forWhom = $(this).data('for');
        // Save immediately when selection changes
        saveCurrentStepData();
        goToStep(3);
    });

    // Step navigation with data persistence
    $('#doc-next-step').click(function() {
        if (!validateStep(docState.currentStep)) return;
        goToStep(docState.currentStep + 1);
    });

    $('#doc-prev-step').click(function() {
        if (docState.currentStep > 1) {
            // Save current data before going back
            saveCurrentStepData();
            goToStep(docState.currentStep - 1);
        }
    });

    // Step 3: Dynamic form with auto-save
    $(document).on('input change', '#application-form input, #application-form select, #application-form textarea', function() {
        if (docState.currentStep === 3) {
            saveCurrentStepData();
        }
    });

    // Step 4: Purpose & Contact with enhanced data persistence
    $(document).on('input change', '#purpose-contact-form input, #purpose-contact-form select', function() {
        const id = $(this).attr('id');
        if (id.startsWith('contact_')) {
            if (!docState.contact) docState.contact = {};
            docState.contact[id.replace('contact_', '')] = $(this).val();
        } else {
            docState[id] = $(this).val();
        }
        if (docState.currentStep === 4) {
            saveCurrentStepData();
        }
    });

    // Step 5: Date/Time with enhanced data persistence
    $(document).on('change', '#claim_date, #claim_time', function() {
        docState[$(this).attr('id')] = $(this).val();
        if (docState.currentStep === 5) {
            saveCurrentStepData();
        }
    });



    // Submit with all collected data
    $('#submit-request').click(function() {
        // Check if user is authenticated
        if (!window.isAuthenticated || !window.userId) {
            alert('You must be logged in to submit a document request. Please log in and try again.');
            window.location.href = '/login';
            return;
        }

        // Final save before submission
        saveCurrentStepData();

        // Gather all data
        const data = {
            user_id: window.userId,
            document_type: docState.documentType,
            for_whom: docState.forWhom,
            application_data: docState.applicationData,
            purpose: docState.purpose || $('#purpose').val(),
            contact_first_name: docState.contact_first_name || $('#contact_first_name').val(),
            contact_middle_name: docState.contact_middle_name || $('#contact_middle_name').val(),
            contact_last_name: docState.contact_last_name || $('#contact_last_name').val(),
            contact_phone: docState.contact_phone || $('#contact_phone').val(),
            contact_email: docState.contact_email || $('#contact_email').val(),
            claim_date: docState.claim_date || $('#claim_date').val(),
            claim_time: docState.claim_time || $('#claim_time').val(),
            _token: window.csrfToken
        };

        // Log all data before submission
        console.log('Submitting document request with data:', data);

        // Submit data to the server
        $.post(window.documentRequestStoreUrl, data, function(response) {
            if (response.success) {
                $('#doc-ref-number').text(response.reference_number);
                new bootstrap.Modal('#docRequestSuccessModal').show();
                // Redirect to dashboard after successful submission
                    window.location.href = '/dashboard';
            } else {
                alert('Submission failed: ' + (response.message || 'Unknown error'));
            }
        }).fail(function(xhr) {
            console.error('Error submitting document request:', xhr.responseJSON);
            let errorMessage = 'Submission failed.';

            if (xhr.status === 401) {
                errorMessage = 'Authentication required. Please log in and try again.';
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMessage = 'Validation errors: ' + errors.join(', ');
            }

            alert(errorMessage);
        });
    });

    function validateStep(step) {
        // Save current step data before validation
        saveCurrentStepData();

        switch (step) {
            case 1:
                if (!docState.documentType) { alert('Select a document type.'); return false; }
                break;
            case 2:
                if (!docState.forWhom) { alert('Select for whom.'); return false; }
                break;
            case 3:
                // Add debugging for death certificate
            if (docState.documentType === 'death-certificate') {
                const form = $('#application-form')[0];
                const invalidFields = [];

                // Check each required field
                $(form).find('[required]').each(function() {
                    if (!this.checkValidity()) {
                        invalidFields.push(this.name || this.id);
                    }
                });

                if (invalidFields.length > 0) {
                    console.log('Invalid fields:', invalidFields);
                    alert(`Please fill in all required fields`);
                    return false;
                }
            }

            if (!$('#application-form')[0].checkValidity()) {
                $('#application-form')[0].reportValidity();
                return false;
            }
            break;
            case 4:
                if (!$('#purpose-contact-form')[0].checkValidity()) { $('#purpose-contact-form')[0].reportValidity(); return false; }
                break;
            case 5:
                if (!$('#claim_date').val() || !$('#claim_time').val()) { alert('Select date and time.'); return false; }
                break;
        }
        return true;
    }
    goToStep(1);
});


// Step 6: Review
function renderSummary() {
    let html = `<div class="summary-container">`;

    // Document Type Section
    html += `
        <div class="summary-section mb-4">
            <h4 class="section-title text-secondary mb-3">Document Information</h4>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="label">Document Type</span>
                    <span class="value fw-bold">${docState.documentType}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="label">Requested For</span>
                    <span class="value fw-bold">${docState.forWhom === 'self' ? 'Myself' : 'Someone Else'}</span>
                </li>
            </ul>
        </div>`;

    // Application Data Section
    html += `
        <div class="summary-section mb-4">
            <h4 class="section-title text-secondary mb-3">Application Details</h4>
            <ul class="list-group">`;

    $('#application-form').serializeArray().forEach(f => {
        const formattedName = f.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        html += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span class="label">${formattedName}</span>
                <span class="value fw-bold">${f.value}</span>
            </li>`;
        docState.applicationData[f.name] = f.value;
    });

    html += `</ul></div>`;

    // Contact Information Section
    html += `
        <div class="summary-section mb-4">
            <h4 class="section-title text-secondary mb-3">Contact Information</h4>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="label">Purpose</span>
                    <span class="value fw-bold">${$('#purpose').val()}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="label">Contact Name</span>
                    <span class="value fw-bold">${$('#contact_first_name').val()} ${$('#contact_middle_name').val()} ${$('#contact_last_name').val()}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="label">Contact Phone</span>
                    <span class="value fw-bold">${$('#contact_phone').val()}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="label">Contact Email</span>
                    <span class="value fw-bold">${$('#contact_email').val()}</span>
                </li>
            </ul>
        </div>`;

    // Claim Information Section
    html += `
        <div class="summary-section">
            <h4 class="section-title text-secondary mb-3">Claim Information</h4>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="label">Claim Date/Time</span>
                    <span class="value fw-bold">${$('#claim_date').val()} ${$('#claim_time').val()}</span>
                </li>
            </ul>
        </div>`;

    html += `</div>`;

    // Add some custom styles
    html += `
        <style>
            .summary-container {
                max-width: 800px;
                margin: 0 auto;
            }
            .section-title {
                font-size: 1.25rem;
                border-bottom: 2px solid #e9ecef;
                padding-bottom: 0.5rem;
            }
            .list-group-item {
                padding: 1rem;
            }
            .label {
                color: #6c757d;
            }
            .value {
                color: #212529;
            }
        </style>`;

    $('#application-summary').html(html);
}

function renderForWhom() {
    let html = '';
    html += `<div class="col-md-12 mb-3">
        <h6>Document Type: ${docState.documentType}</h6>
    </div>`;
    if(docState.documentType === 'birth-certificate'){
        html += `<div class="col-md-6">
            <button class="btn btn-outline-primary me-2 w-100 h-100" style="height: 100px;" data-for="self">For Myself</button>

        </div><div class="col-md-6">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="other">For Someone Else</button>

        </div>`;
    }else if(docState.documentType === 'marriage-contract'){
        html += `<div class="col-md-6">
            <button class="btn btn-outline-primary me-2 w-100 h-100" style="height: 100px;" data-for="self">For Myself</button>

        </div><div class="col-md-6">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="other">For Someone Else</button>

        </div>`;
    }else if(docState.documentType === 'death-certificate'){
        html += ` <div class="col-md-6 mb-3">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="spouse">My Spouse</button>
        </div>
        <div class="col-md-6 mb-3">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="son">My Son</button>
        </div>
        <div class="col-md-6 mb-3">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="daughter">My Daughter</button>
        </div>
        <div class="col-md-6 mb-3">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="other">My Father</button>
        </div>
        <div class="col-md-6 mb-3">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="other">My Mother</button>
        </div>
        <div class="col-md-6 mb-3">
            <button class="btn btn-outline-secondary w-100 h-100" style="height: 100px;" data-for="other">My Other Relative</button>
        </div>`;
    }
    $('#for-whom-container').html(html);
}

function renderApplicationForm() {
    console.log('Selected docType:', docState.documentType);
    let html = '';
    if (docState.documentType === 'birth-certificate') {
        html += `
        <div class="col-md-4">
            <label class="form-label">Your First Name</label>
            <input type="text" class="form-control" name="first_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Your Middle Name</label>
            <input type="text" class="form-control" name="middle_name" required>
        </div>
        <div class="col">
            <label class="form-label">Your Last Name</label>
            <input type="text" class="form-control" name="last_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Your Sex</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="your_sex" id="sex-male" value="Male" required>
                <label class="form-check-label" for="sex-male">Male</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="your_sex" id="sex-female" value="Female" required>
                <label class="form-check-label" for="sex-female">Female</label>
            </div>
        </div>
        <div class="col-md-8">
            <label class="form-label">Your Birth Date</label>
            <div class="row">
                <div class="col">
                    <select class="form-select" name="birth_month" required>
                        <option value="">Month</option>
                        <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
                </div>
                <div class="col">
                    <select class="form-select" name="birth_day" required>
                        <option value="">Day</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
                <option value="13">13</option>
                <option value="14">14</option>
                <option value="15">15</option>
                <option value="16">16</option>
                <option value="17">17</option>
                <option value="18">18</option>
                <option value="19">19</option>
                <option value="20">20</option>
                <option value="21">21</option>
                <option value="22">22</option>
                <option value="23">23</option>
                <option value="24">24</option>
                <option value="25">25</option>
                <option value="26">26</option>
                <option value="27">27</option>
                <option value="28">28</option>
                <option value="29">29</option>
                <option value="30">30</option>
                <option value="31">31</option>
            </select>
        </div>
        <div class="col">
            <select class="form-select" name="birth_year" required>
                <option value="">Year</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <option value="2021">2021</option>
                <option value="2020">2020</option>
                <option value="2019">2019</option>
                <option value="2018">2018</option>
                <option value="2017">2017</option>
                <option value="2016">2016</option>
                <option value="2015">2015</option>
                <option value="2014">2014</option>
                <option value="2013">2013</option>
                <option value="2012">2012</option>
                <option value="2011">2011</option>
                <option value="2010">2010</option>
                <option value="2009">2009</option>
                <option value="2008">2008</option>
                <option value="2007">2007</option>
                <option value="2006">2006</option>
                <option value="2005">2005</option>
                <option value="2004">2004</option>
                <option value="2003">2003</option>
                <option value="2002">2002</option>
                <option value="2001">2001</option>
                <option value="2000">2000</option>
                <option value="1999">1999</option>
                <option value="1998">1998</option>
                <option value="1997">1997</option>
                <option value="1996">1996</option>
                <option value="1995">1995</option>
                <option value="1994">1994</option>
                <option value="1993">1993</option>
                <option value="1992">1992</option>
                <option value="1991">1991</option>
                <option value="1990">1990</option>
                <option value="1989">1989</option>
                <option value="1988">1988</option>
                <option value="1987">1987</option>
                <option value="1986">1986</option>
                <option value="1985">1985</option>
                <option value="1984">1984</option>
                <option value="1983">1983</option>
                <option value="1982">1982</option>
                <option value="1981">1981</option>
                <option value="1980">1980</option>
                <option value="1979">1979</option>
                <option value="1978">1978</option>
                <option value="1977">1977</option>
                <option value="1976">1976</option>
                <option value="1975">1975</option>
                <option value="1974">1974</option>
                <option value="1973">1973</option>
                <option value="1972">1972</option>
                <option value="1971">1971</option>
                <option value="1970">1970</option>
                <option value="1969">1969</option>
                <option value="1968">1968</option>
                <option value="1967">1967</option>
                <option value="1966">1966</option>
                <option value="1965">1965</option>
                <option value="1964">1964</option>
                <option value="1963">1963</option>
                <option value="1962">1962</option>
                <option value="1961">1961</option>
                <option value="1960">1960</option>
                <option value="1959">1959</option>
                <option value="1958">1958</option>
                <option value="1957">1957</option>
                <option value="1956">1956</option>
            </select>
        </div>
    </div>
</div>


        <div class="col-md-4">
            <label class="form-label">Father's First Name</label>
            <input type="text" class="form-control" name="father_first_name" placeholder="First Name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Father's Middle Name</label>
            <input type="text" class="form-control" name="father_middle_name" placeholder="Middle Name" >
        </div>
        <div class="col-md-4">
            <label class="form-label">Father's Last Name</label>
            <input type="text" class="form-control" name="father_last_name" placeholder="Last Name" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Mother's First Name</label>
            <input type="text" class="form-control" name="mother_first_name" placeholder="First Name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Mother's Middle Name</label>
            <input type="text" class="form-control" name="mother_middle_name" placeholder="Middle Name" >
        </div>
        <div class="col">
            <label class="form-label">Mother's Last Name</label>
            <input type="text" class="form-control" name="mother_last_name" placeholder="Last Name" required>
        </div>


        `;
        if (docState.forWhom === 'other') {
            html += `<div class="col-md-12">
                <label class="form-label">Relationship</label>
                <select class="form-select" name="relationship" required>
                    <option value="">Select Relationship</option>
                    <option value="Son">Son</option>
                    <option value="Daughter">Daughter</option>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Other">Other</option>
                </select>
            </div>`;
        }
    } else if(docState.documentType === 'marriage-contract'){
        html += `<div class="col-md-12">
            <label class="form-label">Spouse's Name</label>
            <input type="text" class="form-control" name="spouse_name" required>
        </div>`;
    } else if (docState.documentType === 'death-certificate') {
        html += `
        <div class="col-md-4">
            <label class="form-label">Deceased's First Name</label>
            <input type="text" class="form-control" name="deceased_first_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Middle Name</label>
            <input type="text" class="form-control" name="deceased_middle_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Last Name</label>
            <input type="text" class="form-control" name="deceased_last_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of Death</label>
            <input type="date" class="form-control" name="date_of_death" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Place of Death</label>
            <div class="row g-2">
                <div class="col-3">
                    <select class="form-control" name="death_country_id" id="death_country" >
                        <option value="">Select Country</option>
                    </select>
                </div>
                <div class="col-3 philippines-fields">
                    <select class="form-control" name="death_region_id" id="death_region" > >
                        <option value="">Select Region</option>
                    </select>
                </div>
                <div class="col-3 philippines-fields">
                    <select class="form-control" name="death_city_id" id="death_city" > >
                        <option value="">Select City</option>
                    </select>
                </div>
                <div class="col-3 philippines-fields">
                    <select class="form-control" name="death_barangay_id" id="death_barangay" required >
                        <option value="">Select Barangay</option>
                    </select>
                </div>
                 <!-- Foreign fields -->
                <div class="col-3 foreign-fields" style="display: none;">
                    <input type="text" class="form-control" name="reference_number" placeholder="Reference Number" >
                </div>
                <div class="col-3 foreign-fields" style="display: none;">
                    <input type="text" class="form-control" name="dispatch_number" placeholder="Dispatch Number" >
                </div>
                <div class="col-3 foreign-fields" style="display: none;">
                    <input type="text" class="form-control" name="dispatch_date" placeholder="Dispatch Date (e.g. Jan 1, 2000)" >
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Father's First Name</label>
            <input type="text" class="form-control" name="deceased_father_first_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Father's Middle Name</label>
            <input type="text" class="form-control" name="deceased_father_middle_name" >
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Father's Last Name</label>
            <input type="text" class="form-control" name="deceased_father_last_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Mother's First Name</label>
            <input type="text" class="form-control" name="deceased_mother_first_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Mother's Middle Name</label>
            <input type="text" class="form-control" name="deceased_mother_middle_name" >
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Mother's Last Name</label>
            <input type="text" class="form-control" name="deceased_mother_last_name" required>
        </div>`;
    }
    // Add more document types as needed...
    $('#application-form').html(html);

 // Initialize cascading dropdowns AFTER the HTML is rendered
    if (docState.documentType === 'death-certificate') {
        // Use setTimeout to ensure DOM is fully updated
        setTimeout(() => {
            initializeCascadingDropdowns();
            // Don't call loadCountries separately - it's called in initializeCascadingDropdowns
        }, 100);
    }
}


function initializeCascadingDropdowns() {
    // Load countries immediately
    loadCountries();

    // Remove any existing event listeners to prevent duplicates
    $(document).off('change', '#death_country');
    $(document).off('change', '#death_region');
    $(document).off('change', '#death_city');

    // Country change handler
    $(document).on('change', '#death_country', function() {
        const countryId = $(this).val();
        const regionSelect = $('#death_region');
        const citySelect = $('#death_city');
        const barangaySelect = $('#death_barangay');

        // Assume Philippines has countryId = 'PH' or whatever your backend returns
        const philippinesCountryId = '1'; // <-- Set this to your actual PH country id

        if (countryId === philippinesCountryId) {
            $('.philippines-fields').show();
            $('.foreign-fields').hide();

             $('#death_region, #death_city, #death_barangay').attr('required', true);
            $('.foreign-fields input').removeAttr('required');

            // Reset and disable dependent dropdowns
            regionSelect.html('<option value="">Select Region</option>');
            citySelect.html('<option value="">Select City</option>');
            barangaySelect.html('<option value="">Select Barangay</option>');
            if (countryId) {
                loadRegions(countryId);
            }
        } else if (countryId) {
            $('.philippines-fields').hide();
            $('.foreign-fields').show();

            $('.foreign-fields input').attr('required', true);
            $('#death_region, #death_city, #death_barangay').removeAttr('required');

            // Optionally clear PH fields
            regionSelect.val('');
            citySelect.val('');
            barangaySelect.val('');
        } else {
            // No country selected
            $('.philippines-fields').hide();
            $('.foreign-fields').hide();

            // Remove required from all conditional fields
            $('#death_region, #death_city, #death_barangay').removeAttr('required');
            $('.foreign-fields input').removeAttr('required');
        }

        // Auto-save when country changes
        if (docState.currentStep === 3) {
            saveCurrentStepData();
        }
    });

    // Region change handler
    $(document).on('change', '#death_region', function() {
        const regionId = $(this).val();
        const citySelect = $('#death_city');
        const barangaySelect = $('#death_barangay');

        // Reset dependent dropdowns
        citySelect.html('<option value="">Select City</option>');
        barangaySelect.html('<option value="">Select Barangay</option>');

        if (regionId) {
            loadCities(regionId);
        }

        // Auto-save when region changes
        if (docState.currentStep === 3) {
            saveCurrentStepData();
        }
    });

    // City change handler
    $(document).on('change', '#death_city', function() {
        const cityId = $(this).val();
        const barangaySelect = $('#death_barangay');

        // Reset barangay dropdown
        barangaySelect.html('<option value="">Select Barangay</option>');

        if (cityId) {
            loadBarangays(cityId);
        }

        // Auto-save when city changes
        if (docState.currentStep === 3) {
            saveCurrentStepData();
        }
    });

    // Barangay change handler
    $(document).on('change', '#death_barangay', function() {
        // Auto-save when barangay changes
        if (docState.currentStep === 3) {
            saveCurrentStepData();
        }
    });

    // Foreign fields change handler
    $(document).on('input change', '.foreign-fields input', function() {
        // Auto-save when foreign fields change
        if (docState.currentStep === 3) {
            saveCurrentStepData();
        }
    });
}

function loadCountries() {
    $.ajax({
        url: '/api/locations/countries',
        method: 'GET',
        success: function(countries) {
            const countrySelect = $('#death_country');
            if (countrySelect.length === 0) {
                console.warn('Country select element not found');
                return;
            }

            countrySelect.html('<option value="">Select Country</option>');

            countries.forEach(country => {
                countrySelect.append(`<option value="${country.id}">${country.name}</option>`);
            });

            console.log('Countries loaded successfully:', countries.length);
        },
        error: function(xhr) {
            console.error('Error loading countries:', xhr);
            console.error('Response:', xhr.responseText);
            showToast('Error loading countries. Please try again.', 'error');
        }
    });
}

function loadRegions(countryId) {
    $.ajax({
        url: '/api/locations/regions',
        method: 'GET',
        data: { country_id: countryId },
        success: function(regions) {
            const regionSelect = $('#death_region');
            regionSelect.html('<option value="">Select Region</option>');

            regions.forEach(region => {
                regionSelect.append(`<option value="${region.id}">${region.name}</option>`);
            });

            regionSelect.prop('disabled', false);
        },
        error: function(xhr) {
            console.error('Error loading regions:', xhr);
            showToast('Error loading regions. Please try again.', 'error');
        }
    });
}

function loadCities(regionId) {
    $.ajax({
        url: '/api/locations/cities',
        method: 'GET',
        data: { region_id: regionId },
        success: function(cities) {
            const citySelect = $('#death_city');
            citySelect.html('<option value="">Select City</option>');

            cities.forEach(city => {
                citySelect.append(`<option value="${city.id}">${city.name}</option>`);
            });

            citySelect.prop('disabled', false);
        },
        error: function(xhr) {
            console.error('Error loading cities:', xhr);
            showToast('Error loading cities. Please try again.', 'error');
        }
    });
}

function loadBarangays(cityId) {
    $.ajax({
        url: '/api/locations/barangays',
        method: 'GET',
        data: { city_id: cityId },
        success: function(barangays) {
            const barangaySelect = $('#death_barangay');
            barangaySelect.html('<option value="">Select Barangay</option>');

            barangays.forEach(barangay => {
                barangaySelect.append(`<option value="${barangay.id}">${barangay.name}</option>`);
            });

            barangaySelect.prop('disabled', false);
        },
        error: function(xhr) {
            console.error('Error loading barangays:', xhr);
            showToast('Error loading barangays. Please try again.', 'error');
        }
    });
}

function showToast(message, type = 'info') {
    // Implement your toast notification here
    console.log(`${type}: ${message}`);
}

// Add data persistence functions
function saveCurrentStepData() {
    switch (docState.currentStep) {
        case 1:
            // Document type is already saved in docState.documentType
            console.log('Saved document type:', docState.documentType);
            break;
        case 2:
            // For whom is already saved in docState.forWhom
            console.log('Saved for whom:', docState.forWhom);
            break;
        case 3:
            // Save application form data
            const formData = {};
            const applicationForm = $('#application-form');

            if (applicationForm.length === 0) {
                console.warn('Application form not found, cannot save step 3 data');
                break;
            }

            applicationForm.find('input, select, textarea').each(function() {
                const element = $(this);
                const name = element.attr('name');
                const type = element.attr('type');

                if (name) {
                    if (type === 'radio' || type === 'checkbox') {
                        if (element.is(':checked')) {
                            formData[name] = element.val();
                        }
                    } else {
                        formData[name] = element.val();
                    }
                }
            });
            docState.applicationData = formData;
            console.log('Saved application data:', formData);

            // Extra logging for death certificate location data
            if (docState.documentType === 'death-certificate') {
                console.log('Death certificate location data saved:', {
                    country: formData['death_country_id'],
                    region: formData['death_region_id'],
                    city: formData['death_city_id'],
                    barangay: formData['death_barangay_id'],
                    reference: formData['reference_number'],
                    dispatch: formData['dispatch_number']
                });
            }

            break;
        case 4:
            // Save purpose and contact data
            docState.purpose = $('#purpose').val();
            docState.contact_first_name = $('#contact_first_name').val();
            docState.contact_middle_name = $('#contact_middle_name').val();
            docState.contact_last_name = $('#contact_last_name').val();
            docState.contact_phone = $('#contact_phone').val();
            docState.contact_email = $('#contact_email').val();
            console.log('Saved contact data:', docState);
            break;
        case 5:
            // Save claim date and time
            docState.claim_date = $('#claim_date').val();
            docState.claim_time = $('#claim_time').val();
            console.log('Saved claim data:', { date: docState.claim_date, time: docState.claim_time });
            break;
    }
}

function restoreStepData(step) {
    switch (step) {
        case 1:
            // Restore document type selection
            if (docState.documentType) {
                setTimeout(() => {
                    $('.doc-type-card').removeClass('border-primary');
                    $(`.doc-type-card[data-type="${docState.documentType}"]`).addClass('border-primary');
                    console.log('Restored document type:', docState.documentType);
                }, 50);
            }
            break;
        case 2:
            // Restore for whom selection - visual indication if needed
            if (docState.forWhom) {
                setTimeout(() => {
                    $(`#for-whom-container button[data-for="${docState.forWhom}"]`).addClass('btn-primary').removeClass('btn-outline-primary btn-outline-secondary');
                    console.log('Restored for whom:', docState.forWhom);
                }, 100);
            }
            break;
        case 3:
            // Restore application form data
            if (Object.keys(docState.applicationData).length > 0) {
                setTimeout(() => {
                    // Handle death certificate location fields specially
                    if (docState.documentType === 'death-certificate') {
                        restoreDeathCertificateLocationData();
                    } else {
                        // Regular form restoration for other document types
                        restoreRegularFormData();
                    }
                    console.log('Restored application data');
                }, 200);
            }
            break;
        case 4:
            // Restore purpose and contact data
            setTimeout(() => {
                $('#purpose').val(docState.purpose || '');
                $('#contact_first_name').val(docState.contact_first_name || '');
                $('#contact_middle_name').val(docState.contact_middle_name || '');
                $('#contact_last_name').val(docState.contact_last_name || '');
                $('#contact_phone').val(docState.contact_phone || '');
                $('#contact_email').val(docState.contact_email || '');
                console.log('Restored contact data');
            }, 50);
            break;
        case 5:
            // Restore claim date and time
            setTimeout(() => {
                $('#claim_date').val(docState.claim_date || '');
                $('#claim_time').val(docState.claim_time || '');
                console.log('Restored claim data');
            }, 50);
            break;
    }
}

// Helper function to restore regular form data (non-death certificate)
function restoreRegularFormData() {
    Object.keys(docState.applicationData).forEach(name => {
        const element = $(`[name="${name}"]`);
        const value = docState.applicationData[name];

        if (element.length > 0 && value) {
            if (element.attr('type') === 'radio') {
                element.filter(`[value="${value}"]`).prop('checked', true);
            } else if (element.attr('type') === 'checkbox') {
                element.prop('checked', value === element.val());
            } else {
                element.val(value);
            }
        }
    });
}

// Helper function to restore death certificate location data with cascading dropdowns
function restoreDeathCertificateLocationData() {
    console.log('Restoring death certificate data:', docState.applicationData);

    // First restore all non-location fields
    Object.keys(docState.applicationData).forEach(name => {
        if (!name.includes('death_country') && !name.includes('death_region') &&
            !name.includes('death_city') && !name.includes('death_barangay')) {

            const element = $(`[name="${name}"]`);
            const value = docState.applicationData[name];

            if (element.length > 0 && value) {
                if (element.attr('type') === 'radio') {
                    element.filter(`[value="${value}"]`).prop('checked', true);
                } else if (element.attr('type') === 'checkbox') {
                    element.prop('checked', value === element.val());
                } else {
                    element.val(value);
                }
            }
        }
    });

    // Handle location fields with proper cascading - wait for countries to load first
    const countryId = docState.applicationData['death_country_id'];
    const regionId = docState.applicationData['death_region_id'];
    const cityId = docState.applicationData['death_city_id'];
    const barangayId = docState.applicationData['death_barangay_id'];

    console.log('Location data to restore:', { countryId, regionId, cityId, barangayId });

    if (countryId) {
        // Wait for countries to be loaded, then set the country
        waitForCountriesAndRestore(countryId, regionId, cityId, barangayId);
    }

    // Restore foreign fields if they exist
    ['reference_number', 'dispatch_number', 'dispatch_date'].forEach(fieldName => {
        const value = docState.applicationData[fieldName];
        if (value) {
            $(`[name="${fieldName}"]`).val(value);
        }
    });
}

// Helper function to wait for countries to load and then restore location data
function waitForCountriesAndRestore(countryId, regionId, cityId, barangayId) {
    const checkCountries = () => {
        const countrySelect = $('#death_country');
        if (countrySelect.length > 0 && countrySelect.find('option').length > 1) {
            // Countries are loaded, now set the country value
            console.log('Countries loaded, setting country:', countryId);
            countrySelect.val(countryId);

            // Trigger change event to show appropriate fields
            countrySelect.trigger('change');

            // If Philippines (assuming ID = 1), restore cascading selections
            if (countryId === '1' && regionId) {
                // Wait for regions to load, then set region
                setTimeout(() => {
                    waitForRegionsAndRestore(regionId, cityId, barangayId);
                }, 500);
            }
        } else {
            // Countries not loaded yet, try again
            console.log('Countries not loaded yet, waiting...');
            setTimeout(checkCountries, 100);
        }
    };

    checkCountries();
}

// Helper function to wait for regions to load and restore region data
function waitForRegionsAndRestore(regionId, cityId, barangayId) {
    const checkRegions = () => {
        const regionSelect = $('#death_region');
        if (regionSelect.length > 0 && regionSelect.find('option').length > 1) {
            console.log('Regions loaded, setting region:', regionId);
            regionSelect.val(regionId);
            regionSelect.trigger('change');

            if (cityId) {
                setTimeout(() => {
                    waitForCitiesAndRestore(cityId, barangayId);
                }, 500);
            }
        } else {
            console.log('Regions not loaded yet, waiting...');
            setTimeout(checkRegions, 100);
        }
    };

    checkRegions();
}

// Helper function to wait for cities to load and restore city data
function waitForCitiesAndRestore(cityId, barangayId) {
    const checkCities = () => {
        const citySelect = $('#death_city');
        if (citySelect.length > 0 && citySelect.find('option').length > 1) {
            console.log('Cities loaded, setting city:', cityId);
            citySelect.val(cityId);
            citySelect.trigger('change');

            if (barangayId) {
                setTimeout(() => {
                    waitForBarangaysAndRestore(barangayId);
                }, 500);
            }
        } else {
            console.log('Cities not loaded yet, waiting...');
            setTimeout(checkCities, 100);
        }
    };

    checkCities();
}

// Helper function to wait for barangays to load and restore barangay data
function waitForBarangaysAndRestore(barangayId) {
    const checkBarangays = () => {
        const barangaySelect = $('#death_barangay');
        if (barangaySelect.length > 0 && barangaySelect.find('option').length > 1) {
            console.log('Barangays loaded, setting barangay:', barangayId);
            barangaySelect.val(barangayId);
        } else {
            console.log('Barangays not loaded yet, waiting...');
            setTimeout(checkBarangays, 100);
        }
    };

    checkBarangays();
}

function goToStep(step) {
    console.log('Going to step:', step);

    // Save current step data before navigating
    if (docState.currentStep !== step) {
        saveCurrentStepData();
    }

    // Update UI
    $(".booking-step").removeClass("active");
    $(`#doc-step${step}`).addClass("active");

    // Update step indicators if they exist
    $(".step").removeClass("active completed");
    for (let i = 1; i <= 6; i++) {
        if (i < step) {
            $(`.step[data-step="${i}"]`).addClass("completed");
        } else if (i === step) {
            $(`.step[data-step="${i}"]`).addClass("active");
        }
    }

    docState.currentStep = step;

    // Update navigation buttons
    $('#doc-prev-step').prop('disabled', step === 1);
    $('#doc-next-step').toggle(step < 6);

    // Render step content and restore data
    if (step === 1) {
        // Document type selection is already rendered, just restore selection
        restoreStepData(step);
    } else if (step === 2) {
        renderForWhom();
        restoreStepData(step);
    } else if (step === 3) {
        renderApplicationForm();
        restoreStepData(step);
    } else if (step === 4) {
        restoreStepData(step);
    } else if (step === 5) {
        hideSubmitButton();
        restoreStepData(step);
    } else if (step === 6) {
        showSubmitButton();
        renderSummary();
    }

    // Scroll to form if container exists
    if ($(".booking-container").length > 0) {
        $(".booking-container")[0].scrollIntoView({ behavior: "smooth" });
    }
}

function showSubmitButton() {
    $('#submit-request').removeAttr('style');
}

function hideSubmitButton() {
    $('#submit-request').css('display', 'none');
}

function updateNavigationButtons() {
    $('#doc-prev-step').prop('disabled', docState.currentStep === 1);
    $('#doc-next-step').toggle(docState.currentStep < 6);
}

function updateProgressBar() {
    // Update progress bar if it exists
    const progress = (docState.currentStep / 6) * 100;
    $('.progress-bar').css('width', progress + '%');
}
