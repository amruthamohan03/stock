/**
 * License Management System - JavaScript Module
 * 
 * @version 2.1.2 - DEBUG VERSION
 */

// ===== CONSTANTS =====
const MCA_KIND_IDS = [5, 6]; // UNDER VALUE, HAND CARRY
const SPECIAL_KIND_IDS = [3, 4]; // EXPORT DEFINITIVE, EXPORT TEMPORARY
const EXPIRING_DAYS_CRITICAL = 7;
const EXPIRING_DAYS_WARNING = 15;
const MCA_LICENSE_FORMAT = 'CLIENT-KIND-GOODS-TRANSPORT';

// ===== GLOBAL VARIABLES =====
let licensesTable;
let currentFilter = 'all';
let currentSearch = '';
let isMCAType = false;
let isSpecialType = false;
const today = new Date().toISOString().split('T')[0];

// ===== INITIALIZATION =====
$(document).ready(function () {
  console.log('=== LICENSE MANAGER INITIALIZED ===');
  console.log('Special Kind IDs:', SPECIAL_KIND_IDS);
  console.log('MCA Kind IDs:', MCA_KIND_IDS);
  
  initializeDateConstraints();
  initializeEventHandlers();
  initDataTable();
  updateStatistics();
  setActiveFilter('all');
  
  // Test if fields exist
  console.log('Field Check:');
  console.log('- invoiceNumberField exists:', $('#invoiceNumberField').length > 0);
  console.log('- invoiceDateField exists:', $('#invoiceDateField').length > 0);
  console.log('- invoiceFileField exists:', $('#invoiceFileField').length > 0);
  console.log('- licenseAppliedDateField exists:', $('#licenseAppliedDateField').length > 0);
  console.log('- refCodField exists:', $('#refCodField').length > 0);
  console.log('- supplierField exists:', $('#supplierField').length > 0);
});

/**
 * Initialize date input constraints
 */
function initializeDateConstraints() {
  $('#invoice_date').attr('max', today);
}

/**
 * Initialize all event handlers
 */
function initializeEventHandlers() {
  console.log('Initializing event handlers...');
  
  // Kind change event
  $('#kind_id').on('change', function() {
    console.log('=== KIND CHANGE EVENT FIRED ===');
    handleKindChange.call(this);
  });
  
  // Client change event
  $('#client_id').on('change', handleClientChange);
  
  // Type of goods change event (for MCA reference)
  $('#type_of_goods_id').on('change', function() {
    if (isMCAType) {
      generateMCAReference();
    }
  });
  
  // Date validation events
  $('#license_applied_date').on('change', handleAppliedDateChange);
  $('#license_validation_date').on('change', handleValidationDateChange);
  
  // Numeric field validation
  $('input[type="number"]').on('input', validateNumericInput);
  
  // File input validation
  $('input[type="file"]').on('change', validateFileInput);
  
  // Form submission
  $('#licenseForm').on('submit', handleFormSubmit);
  
  // Filter cards
  $('.filter-card').on('click', handleFilterCardClick);
  
  // Clear filter button
  $('#clearFilterBtn').on('click', clearFilter);
  
  // Export buttons
  $('#exportAllBtn').on('click', exportAllLicenses);
  $(document).on('click', '.exportBtn', exportSingleLicense);
  
  // CRUD buttons
  $(document).on('click', '.viewBtn', viewLicenseDetails);
  $(document).on('click', '.editBtn', editLicense);
  $(document).on('click', '.deleteBtn', deleteLicense);
  
  // Origin management
  $('#addOriginBtn').on('click', openAddOriginModal);
  $('#saveOriginBtn').on('click', saveNewOrigin);
  
  // Modal card handlers
  $('#expiredCard').on('click', showExpiredLicensesModal);
  $('#expiringCard').on('click', showExpiringLicensesModal);
  $('#incompleteCard').on('click', showIncompleteLicensesModal);
  
  // Form reset buttons
  $('#cancelBtn, #resetFormBtn').on('click', resetForm);
  
  console.log('Event handlers initialized');
}

// ===== KIND TYPE CHECKING =====

/**
 * Check if kind ID is MCA type
 */
function isMCAKind(kindId) {
  const id = parseInt(kindId);
  const result = MCA_KIND_IDS.includes(id);
  console.log('  isMCAKind check - ID:', id, 'Result:', result);
  return result;
}

/**
 * Check if kind ID is Special type
 */
function isSpecialKind(kindId) {
  const id = parseInt(kindId);
  const result = SPECIAL_KIND_IDS.includes(id);
  console.log('  isSpecialKind check - ID:', id, 'Result:', result);
  return result;
}

/**
 * Handle kind selection change
 */
function handleKindChange() {
  const kindId = parseInt($(this).val());
  const kindName = $(this).find('option:selected').text().trim();
  
  console.log('=== HANDLE KIND CHANGE ===');
  console.log('Selected Kind:', kindName);
  console.log('Kind ID:', kindId);
  console.log('Kind ID Type:', typeof kindId);
  
  if (!kindId || kindId === 0 || isNaN(kindId)) {
    console.log('Invalid kind ID, resetting fields');
    resetFieldStates();
    return;
  }
  
  isMCAType = isMCAKind(kindId);
  isSpecialType = isSpecialKind(kindId);
  
  console.log('Is MCA Type?', isMCAType);
  console.log('Is Special Type?', isSpecialType);
  
  // Reset all field states first
  console.log('Calling resetFieldStates...');
  resetFieldStates();
  
  if (isMCAType) {
    console.log('>>> Showing MCA Fields');
    toggleFieldsForMCAType(true);
  } else if (isSpecialType) {
    console.log('>>> Showing SPECIAL Type Fields');
    toggleFieldsForSpecialType(true);
  } else {
    console.log('>>> Showing STANDARD Fields');
    showStandardFields();
  }
  
  console.log('======================');
}

/**
 * Reset all field states to default
 */
function resetFieldStates() {
  console.log('  RESET: Showing all sections and fields');
  
  // Show all sections
  $('#financialInfoSection, #invoiceTransportSection, #licenseDetailsSection, #paymentInfoSection').show();
  $('#bankField, #licenseClearedByField, #weightField').show();
  
  // Show all invoice/transport fields
  $('#invoiceNumberField, #invoiceDateField, #invoiceFileField, #supplierField').show();
  
  // Show all license detail fields
  $('#licenseAppliedDateField, #fsiField, #aurField, #entryPostField, #refCodField').show();
  $('#licenseValidationDateField, #licenseExpiryDateField, #licenseFileField').show();
  
  // Show payment fields
  $('#paymentMethodField, #paymentSubtypeField, #destinationField').show();
  
  // Reset labels
  $('label[for="entry_post_id"]').html('Entry Post <span class="text-danger">*</span>');
  
  // Reset required attributes
  addRequiredToStandardFields();
  
  // Remove MCA fields if exist
  $('#mcaTransportField, #mcaCurrencyField, #mcaLicenseNumberField').remove();
  
  // Make license number editable
  $('#license_number').attr('readonly', false);
  $('#licenseNumberHelp').text('');
  
  console.log('  RESET: Complete');
}

// ===== SPECIAL TYPE HANDLING =====

/**
 * Toggle form fields for Special type
 */
function toggleFieldsForSpecialType(isSpecial) {
  console.log('  toggleFieldsForSpecialType called, isSpecial:', isSpecial);
  if (isSpecial) {
    showSpecialTypeFields();
  } else {
    showStandardFields();
  }
}

/**
 * Show fields for Special type licenses
 */
function showSpecialTypeFields() {
  console.log('  SPECIAL: Starting to hide fields...');
  
  // Show all main sections
  $('#financialInfoSection').show();
  $('#invoiceTransportSection').show();
  $('#licenseDetailsSection').show();
  $('#paymentInfoSection').show();
  
  // Show basic fields
  $('#bankField').show();
  $('#licenseClearedByField').show();
  $('#weightField').show();
  
  // KEEP supplier visible
  $('#supplierField').show();
  console.log('  SPECIAL: Supplier field shown');
  
  // HIDE invoice-related fields
  $('#invoiceNumberField').hide();
  console.log('  SPECIAL: Hidden invoiceNumberField, visible:', $('#invoiceNumberField').is(':visible'));
  
  $('#invoiceDateField').hide();
  console.log('  SPECIAL: Hidden invoiceDateField, visible:', $('#invoiceDateField').is(':visible'));
  
  $('#invoiceFileField').hide();
  console.log('  SPECIAL: Hidden invoiceFileField, visible:', $('#invoiceFileField').is(':visible'));
  
  // HIDE license applied date and REF. COD
  $('#licenseAppliedDateField').hide();
  console.log('  SPECIAL: Hidden licenseAppliedDateField, visible:', $('#licenseAppliedDateField').is(':visible'));
  
  $('#refCodField').hide();
  console.log('  SPECIAL: Hidden refCodField, visible:', $('#refCodField').is(':visible'));
  
  // Show other license detail fields
  $('#fsiField, #aurField, #entryPostField').show();
  $('#licenseValidationDateField, #licenseExpiryDateField, #licenseFileField').show();
  
  // Show payment fields
  $('#paymentMethodField, #paymentSubtypeField, #destinationField').show();
  
  // Remove required from hidden fields
  console.log('  SPECIAL: Removing required attributes...');
  $('#invoice_number').removeAttr('required');
  $('#invoice_date').removeAttr('required');
  $('#license_applied_date').removeAttr('required');
  
  // Change Entry Post label
  $('label[for="entry_post_id"]').html('Entry Post/Exit Post <span class="text-danger">*</span>');
  console.log('  SPECIAL: Changed entry post label');
  
  // Keep other fields required
  addRequiredToSpecialTypeFields();
  
  // Make license number editable
  $('#license_number').attr('readonly', false);
  $('#licenseNumberHelp').text('');
  
  console.log('  SPECIAL: Field hiding complete!');
  console.log('  SPECIAL: Final check - invoiceNumberField visible?', $('#invoiceNumberField').is(':visible'));
}

/**
 * Show fields for standard licenses
 */
function showStandardFields() {
  console.log('  STANDARD: Showing all standard fields');
  
  // Show everything
  $('#financialInfoSection, #invoiceTransportSection, #licenseDetailsSection, #paymentInfoSection').show();
  $('#bankField, #licenseClearedByField, #weightField').show();
  $('#invoiceNumberField, #invoiceDateField, #invoiceFileField, #supplierField').show();
  $('#licenseAppliedDateField, #fsiField, #aurField, #entryPostField, #refCodField').show();
  $('#licenseValidationDateField, #licenseExpiryDateField, #licenseFileField').show();
  $('#paymentMethodField, #paymentSubtypeField, #destinationField').show();
  
  // Remove MCA fields
  $('#mcaTransportField, #mcaCurrencyField, #mcaLicenseNumberField').remove();
  
  // Restore required
  addRequiredToStandardFields();
  $('#transport_mode_id, #currency_id').attr('required', true);
  
  // Reset labels
  $('label[for="entry_post_id"]').html('Entry Post <span class="text-danger">*</span>');
  
  $('#license_number').attr('readonly', false);
  $('#licenseNumberHelp').text('');
}

/**
 * Add required to special type fields
 */
function addRequiredToSpecialTypeFields() {
  const requiredFields = [
    '#kind_id', '#client_id', '#type_of_goods_id',
    '#bank_id', '#license_cleared_by', '#weight', '#unit_of_measurement_id',
    '#currency_id', '#fob_declared', '#transport_mode_id',
    '#license_validation_date', '#license_expiry_date',
    '#license_number', '#entry_post_id', '#payment_method_id', '#destination_id',
    '#supplier'
  ];
  
  requiredFields.forEach(field => $(field).attr('required', true));
  console.log('  SPECIAL: Required fields set');
}

/**
 * Add required to standard fields
 */
function addRequiredToStandardFields() {
  const requiredFields = [
    '#kind_id', '#client_id', '#type_of_goods_id',
    '#bank_id', '#license_cleared_by', '#weight', '#unit_of_measurement_id',
    '#currency_id', '#fob_declared', '#transport_mode_id', '#invoice_number',
    '#invoice_date', '#supplier', '#license_applied_date', '#license_validation_date',
    '#license_expiry_date', '#license_number', '#entry_post_id', '#payment_method_id', '#destination_id'
  ];
  
  requiredFields.forEach(field => $(field).attr('required', true));
}

// ===== MCA TYPE HANDLING =====

function toggleFieldsForMCAType(isMCA) {
  if (isMCA) {
    showMCAFields();
  }
}

function showMCAFields() {
  console.log('  MCA: Hiding sections for MCA type');
  $('#financialInfoSection, #invoiceTransportSection, #licenseDetailsSection, #paymentInfoSection').hide();
  $('#bankField, #licenseClearedByField, #weightField').hide();
  
  if ($('#mcaTransportField').length === 0) {
    appendMCAFields();
  }
  
  removeRequiredFromHiddenFields();
  $('#transport_mode_id, #currency_id').removeAttr('required');
  setupMCALicenseNumber();
  generateMCAReference();
}

function appendMCAFields() {
  $('#basicInfoRow').append(getMCAFieldsHTML());
  $('#transport_mode_id_mca').val($('#transport_mode_id').val());
  $('#currency_id_mca').val($('#currency_id').val());
  $('#transport_mode_id_mca, #currency_id_mca').on('change', generateMCAReference);
}

function getMCAFieldsHTML() {
  return `
    <div class="col-md-4 mb-3" id="mcaTransportField">
      <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
      <select name="transport_mode_id_mca" id="transport_mode_id_mca" class="form-select" required>
        <option value="">-- Select Transport Mode --</option>
        ${getTransportModeOptions()}
      </select>
      <div class="invalid-feedback">Please select transport mode</div>
    </div>
    <div class="col-md-4 mb-3" id="mcaCurrencyField">
      <label class="form-label">Currency <span class="text-danger">*</span></label>
      <select name="currency_id_mca" id="currency_id_mca" class="form-select" required>
        <option value="">-- Select Currency --</option>
        ${getCurrencyOptions()}
      </select>
      <div class="invalid-feedback">Please select currency</div>
    </div>
    <div class="col-md-12 mb-3" id="mcaLicenseNumberField">
      <label class="form-label">License Number <span class="text-danger">*</span> (Auto-generated)</label>
      <input type="text" name="license_number_mca" id="license_number_mca" class="form-control" required readonly>
      <small class="text-muted">Format: ${MCA_LICENSE_FORMAT}</small>
      <div class="invalid-feedback">License number is required</div>
    </div>
  `;
}

function getTransportModeOptions() {
  let options = '';
  $('#transport_mode_id option').each(function() {
    if ($(this).val()) {
      options += `<option value="${$(this).val()}" data-transport-letter="${$(this).data('transport-letter')}">${$(this).text()}</option>`;
    }
  });
  return options;
}

function getCurrencyOptions() {
  let options = '';
  $('#currency_id option').each(function() {
    if ($(this).val()) {
      options += `<option value="${$(this).val()}">${$(this).text()}</option>`;
    }
  });
  return options;
}

function setupMCALicenseNumber() {
  $('#license_number').attr('readonly', true).val('');
  $('#licenseNumberHelp').text('Auto-generated: ' + MCA_LICENSE_FORMAT);
}

function generateMCAReference() {
  const clientId = $('#client_id').val();
  const kindId = $('#kind_id').val();
  const goodsId = $('#type_of_goods_id').val();
  const transportId = $('#transport_mode_id_mca').val() || $('#transport_mode_id').val();
  
  if (!clientId || !kindId || !goodsId || !transportId || !isMCAType) {
    return;
  }
  
  const clientShort = $('#client_id option:selected').data('client-short');
  const kindShort = $('#kind_id option:selected').data('kind-short');
  const goodsShort = $('#type_of_goods_id option:selected').data('goods-short');
  const transportLetter = $('#transport_mode_id_mca option:selected').data('transport-letter') || 
                         $('#transport_mode_id option:selected').data('transport-letter');
  
  if (clientShort && kindShort && goodsShort && transportLetter) {
    const mcaRef = `${clientShort}-${kindShort}-${goodsShort}-${transportLetter}`;
    $('#license_number').val(mcaRef);
    $('#license_number_mca').val(mcaRef);
  }
}

function removeRequiredFromHiddenFields() {
  const hiddenFields = [
    '#bank_id', '#license_cleared_by', '#weight', '#unit_of_measurement_id',
    '#fob_declared', '#invoice_number', '#invoice_date', '#supplier',
    '#license_applied_date', '#license_validation_date', '#license_expiry_date',
    '#entry_post_id', '#payment_method_id', '#destination_id'
  ];
  hiddenFields.forEach(field => $(field).removeAttr('required'));
}

// ===== CLIENT HANDLING =====

function handleClientChange() {
  if (isMCAType) {
    generateMCAReference();
    return;
  }
  loadClientLicenseSetting($(this).val());
}

function loadClientLicenseSetting(clientId) {
  if (!clientId) {
    $('#license_cleared_by').val('');
    return;
  }
  $.ajax({
    url: BASE_URL + '/license/getClientLicenseSetting',
    method: 'GET',
    data: { client_id: clientId },
    dataType: 'json',
    success: function (res) {
      if (res.success) {
        $('#license_cleared_by').val(res.license_cleared_by);
      } else {
        $('#license_cleared_by').val('');
      }
    },
    error: function() {
      console.error('Failed to load client license setting');
    }
  });
}

// ... (I'll continue with rest of the functions - STATISTICS, FILTERS, MODALS, DATATABLE, CRUD, VALIDATION, etc.)
// For now, let me provide the CRITICAL debug functions above

// ===== STATISTICS =====
function updateStatistics() {
  $.ajax({
    url: BASE_URL + '/license/crudData/statistics',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        updateStatisticsUI(res.data);
      }
    },
    error: function() {
      console.error('Failed to load statistics');
    }
  });
}

function updateStatisticsUI(data) {
  $('#totalLicenses, #statTotal').text(data.total_licenses || 0);
  $('#expiredLicenses, #statExpired').text(data.expired_licenses || 0);
  $('#expiringLicenses, #statExpiring').text(data.expiring_licenses || 0);
  $('#incompleteLicenses, #statIncomplete').text(data.incomplete_licenses || 0);
  $('#inactiveLicenses, #statInactive').text(data.inactive_licenses || 0);
  $('#annulatedLicenses, #statAnnulated').text(data.annulated_licenses || 0);
  $('#modifiedLicenses, #statModified').text(data.modified_licenses || 0);
  $('#prorogatedLicenses, #statProrogated').text(data.prorogated_licenses || 0);
  $('#statTotalFob').text(data.total_fob_value || '0.00');
}

// ===== (REST OF THE FUNCTIONS - keeping them the same as before) =====
// ===== FILTER HANDLING =====

/**
 * Filter table by type
 * @param {string} filter - Filter type
 */
function filterTable(filter) {
  setActiveFilter(filter);
  
  if (filter === 'all') {
    $('#clearFilterBtn').hide();
  } else {
    $('#clearFilterBtn').show();
  }
  
  if (licensesTable) {
    licensesTable.ajax.reload();
  }
}

/**
 * Handle filter card click
 */
function handleFilterCardClick() {
  const cardId = $(this).attr('id');
  
  // Special handling for modal cards
  if (['expiredCard', 'expiringCard', 'incompleteCard'].includes(cardId)) {
    return;
  }
  
  const filter = $(this).data('filter');
  setActiveFilter(filter);
  
  if (filter === 'all') {
    $('#clearFilterBtn').hide();
  } else {
    $('#clearFilterBtn').show();
  }
  
  if (licensesTable) {
    licensesTable.ajax.reload();
  }
}

/**
 * Set active filter
 * @param {string} filter - Filter type
 */
function setActiveFilter(filter) {
  $('.filter-card').removeClass('active-filter');
  $(`.filter-card[data-filter="${filter}"]`).addClass('active-filter');
  currentFilter = filter;
}

/**
 * Clear active filter
 */
function clearFilter() {
  setActiveFilter('all');
  $('#clearFilterBtn').hide();
  if (licensesTable) {
    licensesTable.ajax.reload();
  }
}

// ===== ORIGIN MANAGEMENT =====

/**
 * Open add origin modal
 */
function openAddOriginModal(e) {
  e.preventDefault();
  $('#addOriginForm')[0].reset();
  $('#new_origin_name').removeClass('is-invalid');
  $('#addOriginModal').modal('show');
}

/**
 * Save new origin
 */
function saveNewOrigin() {
  const originName = $('#new_origin_name').val().trim();
  
  if (!originName) {
    $('#new_origin_name').addClass('is-invalid');
    return;
  }
  
  $('#new_origin_name').removeClass('is-invalid');
  
  const btn = $(this);
  const originalText = btn.html();
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/addOrigin',
    method: 'POST',
    data: { origin_name: originName },
    dataType: 'json',
    success: function(res) {
      btn.prop('disabled', false).html(originalText);
      
      if (res.success) {
        showSuccessMessage(res.message);
        refreshOriginDropdown(res.data);
        $('#addOriginModal').modal('hide');
      } else {
        showErrorMessage(res.message);
      }
    },
    error: function() {
      btn.prop('disabled', false).html(originalText);
      showErrorMessage('Failed to add destination/origin. Please try again.');
    }
  });
}

/**
 * Refresh origin dropdown with new data
 * @param {Object} newOrigin - New origin data
 */
function refreshOriginDropdown(newOrigin) {
  $.ajax({
    url: BASE_URL + '/license/crudData/getOrigins',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data) {
        const $dropdown = $('#destination_id');
        const currentValue = $dropdown.val();
        
        $dropdown.find('option:not(:first)').remove();
        
        res.data.forEach(function(origin) {
          $dropdown.append(new Option(origin.origin_name, origin.id));
        });
        
        if (newOrigin && newOrigin.id) {
          $dropdown.val(newOrigin.id);
        } else if (currentValue) {
          $dropdown.val(currentValue);
        }
      }
    },
    error: function() {
      console.error('Failed to refresh origin dropdown');
    }
  });
}

// ===== MODAL HANDLERS =====

/**
 * Show expired licenses modal
 */
function showExpiredLicensesModal(e) {
  e.preventDefault();
  e.stopPropagation();
  
  $.ajax({
    url: BASE_URL + '/license/crudData/expiredLicenses',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        $('#expiredLicensesContent').html(buildExpiredLicensesHTML(res.data));
      } else {
        $('#expiredLicensesContent').html(getNoDataAlertHTML('No expired licenses found.'));
      }
      $('#expiredLicensesModal').modal('show');
    },
    error: function() {
      showErrorMessage('Failed to load expired licenses');
    }
  });
  
  setActiveFilter('expired');
  $('#clearFilterBtn').show();
  if (licensesTable) {
    licensesTable.ajax.reload();
  }
}

/**
 * Build HTML for expired licenses
 * @param {Array} licenses - License data array
 * @returns {string} HTML string
 */
function buildExpiredLicensesHTML(licenses) {
  let html = '<div class="list-group">';
  
  licenses.forEach(function(license) {
    const daysExpired = parseInt(license.days_expired);
    const expiryDate = new Date(license.license_expiry_date).toLocaleDateString('en-US');
    const appliedDate = license.license_applied_date ? new Date(license.license_applied_date).toLocaleDateString('en-US') : 'N/A';
    
    html += `
      <div class="expired-license-item">
        <div class="row align-items-center">
          <div class="col-md-2">
            <span class="badge days-expired-badge">${daysExpired} days ago</span>
          </div>
          <div class="col-md-10">
            <h6 class="mb-1"><strong>License:</strong> ${license.license_number || 'N/A'}</h6>
            <div class="row">
              <div class="col-md-4">
                <small><strong>Client:</strong> ${license.client_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Bank:</strong> ${license.bank_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Applied:</strong> ${appliedDate}</small>
              </div>
            </div>
            <div class="mt-1">
              <small class="text-danger"><strong>Expired:</strong> ${expiryDate}</small>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  return html;
}

/**
 * Show expiring licenses modal
 */
function showExpiringLicensesModal(e) {
  e.preventDefault();
  e.stopPropagation();
  
  $.ajax({
    url: BASE_URL + '/license/crudData/expiringLicenses',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        $('#expiringLicensesContent').html(buildExpiringLicensesHTML(res.data));
      } else {
        $('#expiringLicensesContent').html(getNoDataAlertHTML('No licenses expiring within 30 days.'));
      }
      $('#expiringLicensesModal').modal('show');
    },
    error: function() {
      showErrorMessage('Failed to load expiring licenses');
    }
  });
  
  setActiveFilter('expiring');
  $('#clearFilterBtn').show();
  if (licensesTable) {
    licensesTable.ajax.reload();
  }
}

/**
 * Build HTML for expiring licenses
 * @param {Array} licenses - License data array
 * @returns {string} HTML string
 */
function buildExpiringLicensesHTML(licenses) {
  let html = '<div class="list-group">';
  
  licenses.forEach(function(license) {
    const daysRemaining = parseInt(license.days_remaining);
    let badgeClass = 'days-notice';
    if (daysRemaining <= EXPIRING_DAYS_CRITICAL) {
      badgeClass = 'days-critical';
    } else if (daysRemaining <= EXPIRING_DAYS_WARNING) {
      badgeClass = 'days-warning';
    }
    
    const expiryDate = new Date(license.license_expiry_date).toLocaleDateString('en-US');
    const appliedDate = license.license_applied_date ? new Date(license.license_applied_date).toLocaleDateString('en-US') : 'N/A';
    
    html += `
      <div class="expiring-license-item">
        <div class="row align-items-center">
          <div class="col-md-2">
            <span class="badge days-badge ${badgeClass}">${daysRemaining} days</span>
          </div>
          <div class="col-md-10">
            <h6 class="mb-1"><strong>License:</strong> ${license.license_number || 'N/A'}</h6>
            <div class="row">
              <div class="col-md-4">
                <small><strong>Client:</strong> ${license.client_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Bank:</strong> ${license.bank_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Applied:</strong> ${appliedDate}</small>
              </div>
            </div>
            <div class="mt-1">
              <small class="text-danger"><strong>Expires:</strong> ${expiryDate}</small>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  return html;
}

/**
 * Show incomplete licenses modal
 */
function showIncompleteLicensesModal(e) {
  e.preventDefault();
  e.stopPropagation();
  
  $('#incompleteLicensesContent').html(getLoadingHTML('Loading incomplete licenses...'));
  $('#incompleteLicensesModal').modal('show');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/incompleteLicenses',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        $('#incompleteLicensesContent').html(buildIncompleteLicensesHTML(res.data));
      } else {
        $('#incompleteLicensesContent').html(getNoDataAlertHTML('No incomplete licenses found.'));
      }
    },
    error: function() {
      $('#incompleteLicensesContent').html(getErrorAlertHTML('Failed to load incomplete licenses.'));
    }
  });
  
  setActiveFilter('incomplete');
  $('#clearFilterBtn').show();
  
  if (licensesTable) {
    licensesTable.ajax.reload();
  }
}

/**
 * Build HTML for incomplete licenses
 * @param {Array} licenses - License data array
 * @returns {string} HTML string
 */
function buildIncompleteLicensesHTML(licenses) {
  let html = '<div class="list-group">';
  
  licenses.forEach(function(license) {
    const createdDate = license.created_at ? new Date(license.created_at).toLocaleDateString('en-US') : 'N/A';
    const licenseNum = license.license_number || '<span class="text-danger">Not Set</span>';
    const clientName = license.client_name || '<span class="text-danger">Not Set</span>';
    const bankName = license.bank_name || '<span class="text-danger">Not Set</span>';
    
    html += `
      <div class="incomplete-license-item">
        <div class="row">
          <div class="col-md-12">
            <h6 class="mb-2">
              <strong>License:</strong> ${licenseNum}
              <span class="badge bg-warning text-dark ms-2">${license.missing_fields.length} Missing Field${license.missing_fields.length > 1 ? 's' : ''}</span>
            </h6>
            <div class="row mb-2">
              <div class="col-md-4">
                <small><strong>Client:</strong> ${clientName}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Bank:</strong> ${bankName}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Created:</strong> ${createdDate}</small>
              </div>
            </div>
            <div class="missing-fields-list">
              <small><strong class="text-danger">Missing Fields:</strong></small><br>
    `;
    
    if (license.missing_fields && license.missing_fields.length > 0) {
      license.missing_fields.forEach(function(field) {
        const badgeClass = field.includes('(Required)') ? 'badge-required' : 'badge-optional';
        html += `<span class="missing-field-badge ${badgeClass}">${field}</span>`;
      });
    } else {
      html += `<span class="text-muted">No missing fields identified</span>`;
    }
    
    html += `
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  return html;
}

/**
 * Get no data alert HTML
 * @param {string} message - Message to display
 * @returns {string} HTML string
 */
function getNoDataAlertHTML(message) {
  return `<div class="alert alert-info mb-0"><i class="ti ti-info-circle me-2"></i>${message}</div>`;
}

/**
 * Get error alert HTML
 * @param {string} message - Message to display
 * @returns {string} HTML string
 */
function getErrorAlertHTML(message) {
  return `<div class="alert alert-danger mb-0"><i class="ti ti-alert-circle me-2"></i>${message}</div>`;
}

/**
 * Get loading HTML
 * @param {string} message - Loading message
 * @returns {string} HTML string
 */
function getLoadingHTML(message) {
  return `
    <div class="text-center p-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">${message}</p>
    </div>
  `;
}

// ===== DATATABLE INITIALIZATION =====

/**
 * Initialize DataTable
 */
function initDataTable() {
  if ($.fn.DataTable.isDataTable('#licenseTable')) {
    $('#licenseTable').DataTable().destroy();
  }

  licensesTable = $('#licenseTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: BASE_URL + '/license/crudData/listing',
      type: 'GET',
      data: function(d) {
        d.filter = currentFilter;
      },
      error: function (xhr, error, thrown) {
        console.error('DataTable Error:', error, thrown);
        showErrorMessage('Failed to load data');
      }
    },
    columns: [
      { data: 'license_number' },
      { data: 'client_name' },
      { data: 'bank_name' },
      { 
        data: 'invoice_number',
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: 'license_applied_date',
        render: function (data) {
          return data ? new Date(data).toLocaleDateString('en-US') : '-';
        }
      },
      {
        data: 'license_expiry_date',
        render: function (data) {
          return data ? new Date(data).toLocaleDateString('en-US') : '-';
        }
      },
      {
        data: 'status',
        render: function (data) {
          const badges = {
            'ACTIVE': 'success',
            'INACTIVE': 'secondary',
            'ANNULATED': 'danger',
            'MODIFIED': 'warning',
            'PROROGATED': 'info'
          };
          const badge = badges[data] || 'secondary';
          return `<span class="badge bg-${badge}">${data}</span>`;
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return getActionButtonsHTML(row);
        }
      }
    ],
    order: [[0, 'desc']],
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    responsive: true,
    drawCallback: function() {
      updateStatistics();
      currentSearch = licensesTable.search();
    }
  });
}

/**
 * Get action buttons HTML
 * @param {Object} row - Row data
 * @returns {string} HTML string
 */
function getActionButtonsHTML(row) {
  return `
    <button class="btn btn-sm btn-view viewBtn" data-id="${row.id}" title="View Details">
      <i class="ti ti-eye"></i>
    </button>
    <button class="btn btn-sm btn-primary editBtn" data-id="${row.id}" title="Edit">
      <i class="ti ti-edit"></i>
    </button>
    <button class="btn btn-sm btn-export exportBtn" data-id="${row.id}" data-license="${row.license_number}" title="Export to Excel">
      <i class="ti ti-file-spreadsheet"></i>
    </button>
    <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.id}" title="Delete">
      <i class="ti ti-trash"></i>
    </button>
  `;
}

// ===== EXPORT FUNCTIONS =====

/**
 * Export all licenses to Excel
 */
function exportAllLicenses() {
  showLoadingMessage('Generating Excel...', 'Please wait while we export all licenses');

  let url = BASE_URL + '/license/crudData/exportAll';
  url += '?filter=' + encodeURIComponent(currentFilter);
  url += '&search=' + encodeURIComponent(currentSearch);

  window.location.href = url;
  
  setTimeout(function() {
    Swal.close();
  }, 1500);
}

/**
 * Export single license to Excel
 */
function exportSingleLicense() {
  const id = $(this).data('id');
  
  showLoadingMessage('Generating Excel...', 'Please wait');

  window.location.href = BASE_URL + '/license/crudData/exportLicense?id=' + id;
  
  setTimeout(function() {
    Swal.close();
  }, 1000);
}

// ===== CRUD OPERATIONS =====

/**
 * View license details
 */
function viewLicenseDetails() {
  const id = $(this).data('id');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/getLicense',
    method: 'GET',
    data: { id: id },
    dataType: 'json',
    success: function (res) {
      if (res.success && res.data) {
        $('#modalDetailsContent').html(buildLicenseDetailsHTML(res.data));
        $('#viewLicenseModal').modal('show');
      } else {
        showErrorMessage(res.message || 'Failed to load license data');
      }
    },
    error: function () {
      showErrorMessage('Failed to load license data');
    }
  });
}

/**
 * Build license details HTML
 * @param {Object} license - License data
 * @returns {string} HTML string
 */
function buildLicenseDetailsHTML(license) {
  const isSpecial = isSpecialKind(license.kind_id);
  const entryPostLabel = isSpecial ? 'Entry Post/Exit Post' : 'Entry Post';
  
  // Determine which fields to show based on kind type
  let invoiceSection = '';
  if (!isSpecial) {
    invoiceSection = `
      <div class="detail-row">
        <div class="row">
          <div class="col-md-6">
            <div class="detail-label">
              <i class="ti ti-file-invoice detail-icon"></i>Invoice Number
            </div>
            <div class="detail-value">${license.invoice_number || 'N/A'}</div>
          </div>
          <div class="col-md-6">
            <div class="detail-label">
              <i class="ti ti-calendar detail-icon"></i>Invoice Date
            </div>
            <div class="detail-value">${license.invoice_date ? new Date(license.invoice_date).toLocaleDateString('en-US') : 'N/A'}</div>
          </div>
        </div>
      </div>
    `;
  }
  
  let appliedDateSection = '';
  if (!isSpecial) {
    appliedDateSection = `
      <div class="col-md-4">
        <div class="detail-label">
          <i class="ti ti-calendar-event detail-icon"></i>Applied Date
        </div>
        <div class="detail-value">${license.license_applied_date ? new Date(license.license_applied_date).toLocaleDateString('en-US') : 'N/A'}</div>
      </div>
    `;
  }
  
  let refCodSection = '';
  if (!isSpecial) {
    refCodSection = `
      <div class="col-md-6">
        <div class="detail-label">
          <i class="ti ti-hash detail-icon"></i>REF. COD
        </div>
        <div class="detail-value">${license.ref_cod || 'N/A'}</div>
      </div>
    `;
  }
  
  return `
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-file-text detail-icon"></i>License Number
          </div>
          <div class="detail-value">${license.license_number || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-building detail-icon"></i>Client
          </div>
          <div class="detail-value">${license.client_name || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-building-bank detail-icon"></i>Bank
          </div>
          <div class="detail-value">${license.bank_name || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-category detail-icon"></i>Kind
          </div>
          <div class="detail-value">${license.kind_name || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    ${invoiceSection}
    
    <div class="detail-row">
      <div class="row">
        ${appliedDateSection}
        <div class="col-md-${isSpecial ? '6' : '4'}">
          <div class="detail-label">
            <i class="ti ti-calendar-check detail-icon"></i>Validation Date
          </div>
          <div class="detail-value">${license.license_validation_date ? new Date(license.license_validation_date).toLocaleDateString('en-US') : 'N/A'}</div>
        </div>
        <div class="col-md-${isSpecial ? '6' : '4'}">
          <div class="detail-label">
            <i class="ti ti-calendar-x detail-icon"></i>Expiry Date
          </div>
          <div class="detail-value">${license.license_expiry_date ? new Date(license.license_expiry_date).toLocaleDateString('en-US') : 'N/A'}</div>
        </div>
      </div>
    </div>
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-weight detail-icon"></i>Weight
          </div>
          <div class="detail-value">${license.weight || 'N/A'} ${license.unit_name || ''}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-currency-dollar detail-icon"></i>FOB Declared
          </div>
          <div class="detail-value">${license.fob_declared || 'N/A'} ${license.currency_short_name || ''}</div>
        </div>
      </div>
    </div>
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-truck-delivery detail-icon"></i>Transport Mode
          </div>
          <div class="detail-value">${license.transport_mode_name || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-user detail-icon"></i>Supplier/Buyer
          </div>
          <div class="detail-value">${license.supplier || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-map-pin detail-icon"></i>${entryPostLabel}
          </div>
          <div class="detail-value">${license.entry_post_name || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-world detail-icon"></i>Destination/Origin
          </div>
          <div class="detail-value">${license.destination_name || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    ${!isSpecial ? `
    <div class="detail-row">
      <div class="row">
        ${refCodSection}
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-flag detail-icon"></i>Status
          </div>
          <div class="detail-value">
            <span class="badge bg-${license.status === 'ACTIVE' ? 'success' : 'secondary'}">${license.status || 'N/A'}</span>
          </div>
        </div>
      </div>
    </div>
    ` : `
    <div class="detail-row">
      <div class="row">
        <div class="col-md-12">
          <div class="detail-label">
            <i class="ti ti-flag detail-icon"></i>Status
          </div>
          <div class="detail-value">
            <span class="badge bg-${license.status === 'ACTIVE' ? 'success' : 'secondary'}">${license.status || 'N/A'}</span>
          </div>
        </div>
      </div>
    </div>
    `}
  `;
}

/**
 * Edit license
 */
function editLicense() {
  const id = $(this).data('id');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/getLicense',
    method: 'GET',
    data: { id: id },
    dataType: 'json',
    success: function (res) {
      if (res.success && res.data) {
        populateFormForEdit(res.data);
      } else {
        showErrorMessage(res.message || 'Failed to load license data');
      }
    },
    error: function () {
      showErrorMessage('Failed to load license data');
    }
  });
}

/**
 * Populate form with license data for editing
 * @param {Object} license - License data
 */
function populateFormForEdit(license) {
  $('.form-control, .form-select').removeClass('is-invalid');
  
  // Set form mode to update
  $('#license_id').val(license.id);
  $('#formAction').val('update');
  $('#formTitle').text('Edit License');
  $('#submitBtnText').text('Update License');
  $('#resetFormBtn').show();

  // First, set the kind_id to trigger field visibility changes
  $('#kind_id').val(license.kind_id);
  
  // Determine kind type
  isMCAType = isMCAKind(license.kind_id);
  isSpecialType = isSpecialKind(license.kind_id);
  
  // Reset field states first
  resetFieldStates();
  
  // Toggle fields based on kind type
  if (isMCAType) {
    toggleFieldsForMCAType(true);
  } else if (isSpecialType) {
    toggleFieldsForSpecialType(true);
  } else {
    showStandardFields();
  }

  // Populate all fields
  const fillableFields = [
    'kind_id', 'bank_id', 'client_id', 'license_cleared_by', 'type_of_goods_id', 'weight',
    'unit_of_measurement_id', 'currency_id', 'fob_declared', 'insurance', 'freight', 'other_costs',
    'transport_mode_id', 'invoice_number', 'invoice_date', 'supplier',
    'license_applied_date', 'license_validation_date', 'license_expiry_date',
    'fsi', 'aur', 'license_number', 'entry_post_id', 'ref_cod',
    'payment_method_id', 'payment_subtype_id', 'destination_id', 'status'
  ];

  fillableFields.forEach(function (key) {
    if (typeof license[key] !== 'undefined' && license[key] !== null) {
      $('#' + key).val(license[key]);
    } else {
      $('#' + key).val('');
    }
  });

  // If MCA type, set MCA-specific fields
  if (isMCAType) {
    $('#transport_mode_id').val(license.transport_mode_id);
    $('#currency_id').val(license.currency_id);
    $('#transport_mode_id_mca').val(license.transport_mode_id);
    $('#currency_id_mca').val(license.currency_id);
    $('#license_number').val(license.license_number);
    $('#license_number_mca').val(license.license_number);
  }

  // Load client setting for non-MCA types
  if (!isMCAType && license.client_id) {
    // Don't trigger change to avoid overwriting license_cleared_by
    // Instead, just set the value directly after a short delay
    setTimeout(function () {
      $('#license_cleared_by').val(license.license_cleared_by);
    }, 100);
  }

  // Show current files
  if (license.invoice_file) {
    $('#current_invoice_file').html(`<a href="${BASE_URL}/${license.invoice_file}" target="_blank" class="text-primary"><i class="ti ti-file-text"></i> View Current Invoice</a>`);
  } else {
    $('#current_invoice_file').text('');
  }
  
  if (license.license_file) {
    $('#current_license_file').html(`<a href="${BASE_URL}/${license.license_file}" target="_blank" class="text-primary"><i class="ti ti-file-text"></i> View Current License</a>`);
  } else {
    $('#current_license_file').text('');
  }

  // Expand form and scroll
  $('#createLicense').collapse('show');
  $('html, body').animate({ scrollTop: $('#licenseForm').offset().top - 100 }, 500);
}

/**
 * Delete license
 */
function deleteLicense() {
  const id = $(this).data('id');
  
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      performDelete(id);
    }
  });
}

/**
 * Perform delete operation
 * @param {number} id - License ID
 */
function performDelete(id) {
  $.ajax({
    url: BASE_URL + '/license/crudData/deletion',
    method: 'POST',
    data: { id: id },
    dataType: 'json',
    success: function (res) {
      if (res.success) {
        showSuccessMessage(res.message, 1500);
        licensesTable.ajax.reload(null, false);
        updateStatistics();
      } else {
        showErrorMessage(res.message || 'Delete failed');
      }
    },
    error: function () {
      showErrorMessage('Failed to delete license');
    }
  });
}

// ===== FORM VALIDATION =====

/**
 * Handle applied date change
 */
function handleAppliedDateChange() {
  const appliedDate = $(this).val();
  if (appliedDate) {
    $('#license_validation_date').attr('min', appliedDate);
    $('#license_expiry_date').attr('min', appliedDate);
  }
}

/**
 * Handle validation date change
 */
function handleValidationDateChange() {
  const validationDate = $(this).val();
  if (validationDate) {
    $('#license_expiry_date').attr('min', validationDate);
  }
}

/**
 * Validate numeric input
 */
function validateNumericInput() {
  const value = parseFloat($(this).val());
  if (value < 0) {
    $(this).val(0);
  }
}

/**
 * Validate file input
 */
function validateFileInput() {
  const file = this.files[0];
  if (file) {
    const fileType = file.type;
    if (fileType !== 'application/pdf') {
      $(this).val('');
      $('#createLicense').collapse('show');
      showErrorMessage('Only PDF files are allowed');
    }
  }
}

// ===== FORM SUBMISSION =====

/**
 * Handle form submission
 */
function handleFormSubmit(e) {
  e.preventDefault();

  $('.form-control, .form-select').removeClass('is-invalid');
  
  // Sync MCA fields to original fields
  if (isMCAType) {
    $('#transport_mode_id').val($('#transport_mode_id_mca').val());
    $('#currency_id').val($('#currency_id_mca').val());
  }
  
  // Check HTML5 validation
  if (!this.checkValidity()) {
    e.stopPropagation();
    $(this).find(':invalid').addClass('is-invalid');
    $('#createLicense').collapse('show');
    
    scrollToFirstError();
    return;
  }

  // Custom date validations for non-MCA and non-Special types
  if (!isMCAType && !validateDates()) {
    return;
  }

  submitForm();
}

/**
 * Validate dates
 * @returns {boolean} True if valid
 */
function validateDates() {
  // For special types, skip invoice date and applied date validation
  if (!isSpecialType) {
    const invoiceDate = new Date($('#invoice_date').val());
    const todayDate = new Date();
    todayDate.setHours(0, 0, 0, 0);

    if ($('#invoice_date').val() && invoiceDate > todayDate) {
      $('#invoice_date').addClass('is-invalid');
      $('#createLicense').collapse('show');
      showErrorMessage('Invoice date cannot be in the future');
      return false;
    }
    
    const appliedDate = new Date($('#license_applied_date').val());
    const validationDate = new Date($('#license_validation_date').val());

    if ($('#license_applied_date').val() && $('#license_validation_date').val() && appliedDate > validationDate) {
      $('#license_validation_date').addClass('is-invalid');
      $('#createLicense').collapse('show');
      showErrorMessage('Validation date must be greater than or equal to applied date');
      return false;
    }
  }

  // Validation date vs Expiry date check applies to all types
  const validationDate = new Date($('#license_validation_date').val());
  const expiryDate = new Date($('#license_expiry_date').val());

  if ($('#license_validation_date').val() && $('#license_expiry_date').val() && validationDate > expiryDate) {
    $('#license_expiry_date').addClass('is-invalid');
    $('#createLicense').collapse('show');
    showErrorMessage('Expiry date must be greater than or equal to validation date');
    return false;
  }
  
  return true;
}

/**
 * Submit form data
 */
function submitForm() {
  const formData = new FormData($('#licenseForm')[0]);
  const action = $('#formAction').val();
  const url = action === 'update'
    ? BASE_URL + '/license/crudData/update'
    : BASE_URL + '/license/crudData/insertion';

  const submitBtn = $('#submitBtn');
  const originalText = submitBtn.html();
  submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

  $.ajax({
    url: url,
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (res) {
      submitBtn.prop('disabled', false).html(originalText);

      if (res.success) {
        showSuccessMessage(res.message || 'Saved successfully', 1500);
        resetForm();
        if (licensesTable) {
          licensesTable.ajax.reload(null, false);
        }
        updateStatistics();
      } else {
        if (res.message) {
          showErrorMessage(res.message);
          $('#createLicense').collapse('show');
        }
      }
    },
    error: function (xhr) {
      submitBtn.prop('disabled', false).html(originalText);
      let errorMsg = 'An error occurred while processing your request';
      try {
        const response = JSON.parse(xhr.responseText);
        errorMsg = response.message || errorMsg;
      } catch (e) {
        errorMsg = xhr.responseText || errorMsg;
      }
      showErrorMessage(errorMsg);
    }
  });
}

/**
 * Scroll to first error field
 */
function scrollToFirstError() {
  const firstError = $('.is-invalid').first();
  if (firstError.length) {
    $('html, body').animate({
      scrollTop: firstError.offset().top - 100
    }, 300);
  }
}

// ===== FORM RESET =====

/**
 * Reset form to initial state
 */
function resetForm(e) {
  if (e) {
    e.preventDefault();
  }
  
  $('#licenseForm')[0].reset();
  $('.form-control, .form-select').removeClass('is-invalid');
  $('#license_id').val('');
  $('#formAction').val('insert');
  $('#formTitle').text('Add New License');
  $('#submitBtnText').text('Save License');
  $('#resetFormBtn').hide();
  $('#current_invoice_file, #current_license_file').text('');
  $('#createLicense').collapse('hide');
  $('#license_validation_date').removeAttr('min');
  $('#license_expiry_date').removeAttr('min');
  $('#invoice_date').attr('max', today);
  $('#license_number').attr('readonly', false).val('');
  $('#licenseNumberHelp').text('');
  
  // Reset kind type states
  isMCAType = false;
  isSpecialType = false;
  
  // Reset all field states
  resetFieldStates();
  
  $('html, body').animate({ scrollTop: $('#licenseForm').offset().top - 100 }, 200);
}

// ===== UI HELPER FUNCTIONS =====

/**
 * Show success message
 * @param {string} message - Message to display
 * @param {number} timer - Auto close timer (optional)
 */
function showSuccessMessage(message, timer = null) {
  const config = {
    icon: 'success',
    title: 'Success!',
    text: message
  };
  
  if (timer) {
    config.timer = timer;
    config.showConfirmButton = false;
  }
  
  Swal.fire(config);
}

/**
 * Show error message
 * @param {string} message - Message to display
 */
function showErrorMessage(message) {
  Swal.fire({
    icon: 'error',
    title: 'Error',
    html: message
  });
}

/**
 * Show loading message
 * @param {string} title - Title text
 * @param {string} text - Body text
 */
function showLoadingMessage(title, text) {
  Swal.fire({
    title: title,
    text: text,
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
}