<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

$client_id = $_SESSION['client_id'];
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Support Tickets</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_all';
                }

                switch($source) {
                    case 'new';
                        include "includes/create_ticket.php";
                        break;
                    case 'view';
                        include "includes/view_ticket.php";
                        break;
                    default:
                        include "includes/view_support_tickets.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title">Add Reply</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="replyForm" method="POST" action="includes/ajax/submit_ticket_reply.php">
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="reply_ticket_id">
                    <div class="mb-3">
                        <label for="reply_message" class="form-label">Your Message</label>
                        <textarea class="form-control" id="reply_message" name="message" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReplyModal(ticketId) {
    document.getElementById('reply_ticket_id').value = ticketId;
    new bootstrap.Modal(document.getElementById('replyModal')).show();
}

document.getElementById('replyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('includes/ajax/submit_ticket_reply.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error submitting reply');
    });
});
</script>

<?php include 'includes/client_footer.php'; ?>