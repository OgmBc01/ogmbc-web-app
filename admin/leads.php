<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';
?>

<div class="row">
    <div class="col-md-12">
      <?php
        if (isset($_GET['source'])) {
          $source = $_GET['source'];
        } else {
          $source = '';
        }

        switch ($source) {
          case 'ratio-calc-leads':
            include "includes/ratio-calc-leads.php";
            break;

          case 'newsletter-subs':
            include "includes/newsletter-subs.php";
            break;

          case 'service-enquiries':
            include "includes/service-enquiries.php";
            break;

          default:
            include "ratio-calc-leads.php";
            break;
        }
      ?>
    </div>
  </div>  
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModalPost" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirm Deletion Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete the post <strong id="postTitle"></strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeletePost" class="btn btn-main">Delete</a>
      </div>
    </div>
  </div>
</div>

</body></br>
<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="errorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
</html>

<?php
include "includes/footer.php";
?>  