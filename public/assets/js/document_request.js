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
    $(document).on('click', '#for-whom-container button', function() {
        docState.forWhom = $(this).data('for');
        goToStep(3);
    });

    // Step navigation
    $('#doc-next-step').click(function() {
        if (!validateStep(docState.currentStep)) return;
        goToStep(docState.currentStep + 1);
    });
    $('#doc-prev-step').click(function() {
        if (docState.currentStep > 1) goToStep(docState.currentStep - 1);
    });

    // Step 3: Dynamic form
    

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
    goToStep(1);
});


// Step 6: Review
function renderSummary() {
    let html = `<ul class="list-group">`;
    html += `<li class="list-group-item"><strong>Document Type:</strong> ${docState.documentType}</li>`;
    html += `<li class="list-group-item"><strong>For:</strong> ${docState.forWhom === 'self' ? 'Myself' : 'Someone Else'}</li>`;
    // Application data
    $('#application-form').serializeArray().forEach(f => {
        const formattedName = f.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        html += `<li class="list-group-item"><strong>${formattedName}:</strong> ${f.value}</li>`;
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
            <input type="text" class="form-control" name="father_middle_name" placeholder="Middle Name" required>
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
            <input type="text" class="form-control" name="mother_middle_name" placeholder="Middle Name" required>
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
            <label class="form-label">Deceased's Father's First Name</label>
            <input type="text" class="form-control" name="deceased_father_first_name" required>
        </div>
        <div class="col-md-4">  
            <label class="form-label">Deceased's Father's Middle Name</label>
            <input type="text" class="form-control" name="deceased_father_middle_name" required>
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
            <input type="text" class="form-control" name="deceased_mother_middle_name" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Deceased's Mother's Last Name</label>
            <input type="text" class="form-control" name="deceased_mother_last_name" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date of Death</label>
            <input type="date" class="form-control" name="date_of_death" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Place of Death</label>
            <input type="text" class="form-control" name="place_of_death" required>
        </div>`;
    }
    // Add more document types as needed...
    $('#application-form').html(html);
}

function goToStep(step) {
    console.log('Going to step:', step);
    $(".booking-step").removeClass("active");
    $(`#doc-step${step}`).addClass("active");

    $(".step").removeClass("active completed");
    for (let i = 1; i <= 6; i++) {
        if (i < step) {
        $(`.step[data-step="${i}"]`).addClass("completed");
        } else if (i === step) {
            $(`.step[data-step="${i}"]`).addClass("active");
        }
    }

    docState.currentStep = step; // <-- use docState, not requestState
    updateNavigationButtons();
    updateProgressBar();

    // If on application form step, render it
    if(step === 2){
        renderForWhom();
    }else if (step === 3) {
        renderApplicationForm();
    } else if (step === 6) {
        renderSummary();
    }

    $(".booking-container")[0].scrollIntoView({ behavior: "smooth" });
}

function updateProgressBar() {
    const progress = ((docState.currentStep - 1) / 5) * 100;
    $(".progress-bar-steps .progress").css("width", `${progress}%`);
}

function updateNavigationButtons() {
    $("#doc-prev-step").prop("disabled", docState.currentStep === 1);
    if (docState.currentStep === 6) {
        $("#doc-next-step").hide();
    } else {
        $("#doc-next-step").show();
    }
}