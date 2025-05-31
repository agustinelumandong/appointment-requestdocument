const documentTypes = window.documentTypes || [];
let docState = {
    currentStep: 1,
    documentType: null,
    forWhom: null,
    applicationData: {},
    purpose: '',
    delivery_method: '',
    contact: {},
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

    // Step 1: Select document type
    $(document).on('click', '.doc-type-card', function() {
        $('.doc-type-card').removeClass('border-primary');
        $(this).addClass('border-primary');
        docState.documentType = $(this).data('type');
    });

    // Step 2: For whom
    $('#doc-step2 button').click(function() {
        docState.forWhom = $(this).data('for');
        showStep(3);
        renderApplicationForm();
    });

    // Step navigation
    $('#doc-next-step').click(function() {
        if (!validateStep(docState.currentStep)) return;
        showStep(docState.currentStep + 1);
        if (docState.currentStep === 3) renderApplicationForm();
        if (docState.currentStep === 6) renderSummary();
    });
    $('#doc-prev-step').click(function() {
        if (docState.currentStep > 1) showStep(docState.currentStep - 1);
    });

    // Step 3: Dynamic form
    function renderApplicationForm() {
        let html = '';
        // Example: dynamic fields based on type and forWhom
        if (docState.documentType === 'birth-certificate') {
            html += `<div class="col-md-6">
                <label class="form-label">Child's Name</label>
                <input type="text" class="form-control" name="child_name" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date of Birth</label>
                <input type="date" class="form-control" name="dob" required>
            </div>`;
            if (docState.forWhom === 'other') {
                html += `<div class="col-md-6">
                    <label class="form-label">Relationship</label>
                    <input type="text" class="form-control" name="relationship" required>
                </div>`;
            }
        }
        // Add more document types as needed...
        $('#application-form').html(html);
    }

    // Step 4: Purpose & Contact
    $('#purpose-contact-form input, #purpose-contact-form select').on('input change', function() {
        const id = $(this).attr('id');
        if (id.startsWith('contact_')) {
            docState.contact[id.replace('contact_', '')] = $(this).val();
        } else {
            docState[id] = $(this).val();
        }
    });

    // Step 5: Date/Time
    $('#claim_date, #claim_time').on('change', function() {
        docState[$(this).attr('id')] = $(this).val();
    });

    // Step 6: Review
    function renderSummary() {
        let html = `<ul class="list-group">`;
        html += `<li class="list-group-item"><strong>Document Type:</strong> ${docState.documentType}</li>`;
        html += `<li class="list-group-item"><strong>For:</strong> ${docState.forWhom === 'self' ? 'Myself' : 'Someone Else'}</li>`;
        // Application data
        $('#application-form').serializeArray().forEach(f => {
            html += `<li class="list-group-item"><strong>${f.name.replace('_', ' ')}:</strong> ${f.value}</li>`;
            docState.applicationData[f.name] = f.value;
        });
        html += `<li class="list-group-item"><strong>Purpose:</strong> ${$('#purpose').val()}</li>`;
        html += `<li class="list-group-item"><strong>Delivery:</strong> ${$('#delivery_method').val()}</li>`;
        html += `<li class="list-group-item"><strong>Contact Name:</strong> ${$('#contact_name').val()}</li>`;
        html += `<li class="list-group-item"><strong>Contact Phone:</strong> ${$('#contact_phone').val()}</li>`;
        html += `<li class="list-group-item"><strong>Contact Email:</strong> ${$('#contact_email').val()}</li>`;
        html += `<li class="list-group-item"><strong>Claim Date/Time:</strong> ${$('#claim_date').val()} ${$('#claim_time').val()}</li>`;
        html += `</ul>`;
        $('#application-summary').html(html);
    }

    // Submit
    $('#submit-request').click(function() {
        // Gather all data
        const data = {
            document_type: docState.documentType,
            for_whom: docState.forWhom,
            application_data: docState.applicationData,
            purpose: $('#purpose').val(),
            delivery_method: $('#delivery_method').val(),
            contact_name: $('#contact_name').val(),
            contact_phone: $('#contact_phone').val(),
            contact_email: $('#contact_email').val(),
            claim_date: $('#claim_date').val(),
            claim_time: $('#claim_time').val(),
            _token: window.csrfToken
        };
        //submit data to the server
        $.post(window.documentRequestStoreUrl, data, function(response) {
            if (response.success) {
                $('#doc-ref-number').text(response.reference_number);
                new bootstrap.Modal('#docRequestSuccessModal').show();
            } else {
                alert('Submission failed.');
            }
        });
    });

    function validateStep(step) {
        switch (step) {
            case 1:
                if (!docState.documentType) { alert('Select a document type.'); return false; }
                break;
            case 2:
                if (!docState.forWhom) { alert('Select for whom.'); return false; }
                break;
            case 3:
                if (!$('#application-form')[0].checkValidity()) { $('#application-form')[0].reportValidity(); return false; }
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
    showStep(1);
});