<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";
include "status_helper.php"
?>

<div class="row">
    <div class="col-md-12">
      <?php
        if(isset($_GET['source'])) {
          $source = $_GET['source'];
        }   else    {
            $source = '';
    }

    switch($source) {
        case 'add_client';
            include "includes/add_client.php";
            break;

        case 'edit_client';
            include "includes/edit_client.php";
            break;

        case 'generate_proposal';
            include "includes/generate_proposal.php";
            break;

        case 'generate_proforma';
            include "includes/generate_proforma.php";
            break;

        case 'review_proposal';
            include "includes/review_proposal.php";
            break;

        case 'review_proforma';
            include "includes/review_proforma.php";
            break;

        default:
            include "includes/view_all_clients.php";
            break;
        }
      ?>
    </div>
  </div>  
</div>

<!-- Client Details Modal -->
<div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="clientDetailsModalLabel">Client Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="clientDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading client details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="reviewModalLabel">Review Proposal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reviewModalContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading proposal details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Load client details for modal
function loadClientDetails(clientId) {
    $.ajax({
        url: 'get_client_details.php',
        type: 'GET',
        data: { id: clientId },
        success: function(response) {
            $('#clientDetailsContent').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading client details:', error);
            $('#clientDetailsContent').html('<div class="alert alert-danger">Error loading client details: ' + error + '</div>');
        }
    });
}

// Load review details for modal
function loadReviewDetails(clientId) {
    $.ajax({
        url: 'get_review_details.php',
        type: 'GET',
        data: { id: clientId },
        success: function(response) {
            $('#reviewModalContent').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading review details:', error);
            $('#reviewModalContent').html('<div class="alert alert-danger">Error loading review details: ' + error + '</div>');
        }
    });
}

// Handle proposal generation - REDIRECT VERSION
function generateProposal(clientId) {
    // Redirect to generate proposal page
    window.location.href = 'clients.php?source=generate_proposal&client_id=' + clientId;
}

// Handle proforma generation - REDIRECT VERSION
function generateProforma(clientId) {
    // Redirect to generate proforma page
    window.location.href = 'clients.php?source=generate_proforma&client_id=' + clientId;
}

////////////////// Handle document upload (for AJAX-loaded modal content)////////////////////
$(document).on('submit', '#documentUploadForm', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let submitBtn = $(this).find('button[type="submit"]');
    let originalText = submitBtn.html();

    // Button busy state
    submitBtn
        .html('<i class="bi bi-hourglass-split me-1"></i> Uploading...')
        .prop('disabled', true);

    $.ajax({
        url: 'upload_document.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        success: function(response) {
            submitBtn.html(originalText).prop('disabled', false);

            let result;
            try {
                result = JSON.parse(response);
            } catch (e) {
                alert("Upload failed. Server returned invalid response.");
                console.error("JSON parse error:", e, "Response:", response);
                return;
            }

            if (result.success) {
                alert(result.message);

                // Reset the form
                $('#documentUploadForm')[0].reset();

                // Refresh document list
                reloadClientDocuments(
                    $("#documentUploadForm input[name='client_id']").val()
                );
            } else {
                alert("Error: " + result.message);
            }
        },

        error: function(xhr, status, error) {
            submitBtn.html(originalText).prop('disabled', false);
            alert("Upload failed: " + error);
        }
    });
});


// Refresh document table after upload
function reloadClientDocuments(clientId) {
    $.ajax({
        url: 'get_client_documents.php',
        type: 'GET',
        data: { client_id: clientId },
        success: function(html) {
            // Replace ONLY the documents table section
            $("#clientDocumentsTable").html(html);
        },
        error: function(xhr, status, error) {
            console.error("Failed to reload documents:", error);
        }
    });
}


// Add new document input set
$(document).on("click", "#addDocumentField", function() {

    let clone = $(".document-field-set").first().clone();

    // Clear values
    clone.find("input").val("");
    clone.find("select").val("trade_license");

    // Show remove button on cloned sets
    clone.find(".removeFieldBtn").show();

    $("#documentFieldsWrapper").append(clone);
});

// Remove a document field set
$(document).on("click", ".removeFieldBtn", function() {
    $(this).closest(".document-field-set").remove();
});


// Properly handle modal closing
document.addEventListener('DOMContentLoaded', function() {
    // Get all modals
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        // Listen for hidden event
        modal.addEventListener('hidden.bs.modal', function (event) {
            // Remove any remaining backdrop
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });
            
            // Reset body class
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
        });
        
        // Listen for show event
        modal.addEventListener('show.bs.modal', function (event) {
            // Remove any existing backdrop before showing new modal
            const existingBackdrops = document.querySelectorAll('.modal-backdrop');
            existingBackdrops.forEach(backdrop => {
                backdrop.remove();
            });
        });
    });
    
    // Fix for close buttons
    const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                // Use Bootstrap's modal method to hide
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                } else {
                    // Fallback
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    
                    // Remove backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
        });
    });
});

// Alternative: Force hide all modals function
function hideAllModals() {
    // Hide all Bootstrap modals
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        } else {
            modal.classList.remove('show');
            modal.style.display = 'none';
        }
    });
    
    // Remove backdrop
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    
    // Reset body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

// Call this when page loads to clear any stuck modals
window.addEventListener('load', function() {
    // Clean up any stuck modals
    const stuckModals = document.querySelectorAll('.modal.show');
    if (stuckModals.length > 0) {
        hideAllModals();
    }
});

</script>


</body>
</br>
</html>

<?php
include "includes/footer.php";
?>