<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";
?>

<div class="row">
    <div class="col-md-12">
      <?php
        if(isset($_GET['source'])) {
          $source = $_GET['source'];
        } else {
          $source = '';
        }

        switch($source) {
        case 'add_client';
        include "includes/add_client.php";
        break;

        case 'edit_client';
        include "includes/edit_client.php";
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

// Handle proposal generation
function generateProposal(clientId) {
    $.ajax({
        url: 'generate_proposal.php',
        type: 'POST',
        data: { client_id: clientId },
        success: function(response) {
            try {
                var result = JSON.parse(response);
                if(result.success) {
                    showAlert('Proposal generated successfully!', 'success');
                    // Open proposal in new tab
                    if(result.file_path) {
                        window.open(result.file_path, '_blank');
                    }
                    // Reload client details
                    loadClientDetails(clientId);
                } else {
                    showAlert('Error generating proposal: ' + result.message, 'error');
                }
            } catch (e) {
                showAlert('Error parsing response: ' + e, 'error');
            }
        },
        error: function(xhr, status, error) {
            showAlert('Error generating proposal: ' + error, 'error');
        }
    });
}

// Handle proforma generation
function generateProforma(clientId) {
    $.ajax({
        url: 'generate_proforma.php',
        type: 'POST',
        data: { client_id: clientId },
        success: function(response) {
            try {
                var result = JSON.parse(response);
                if(result.success) {
                    showAlert('Proforma invoice generated successfully!', 'success');
                    // Open proforma in new tab
                    if(result.file_path) {
                        window.open(result.file_path, '_blank');
                    }
                    // Reload client details
                    loadClientDetails(clientId);
                } else {
                    showAlert('Error generating proforma: ' + result.message, 'error');
                }
            } catch (e) {
                showAlert('Error parsing response: ' + e, 'error');
            }
        },
        error: function(xhr, status, error) {
            showAlert('Error generating proforma: ' + error, 'error');
        }
    });
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
</script>


</body>
</br>
</html>

<?php
include "includes/footer.php";
?>