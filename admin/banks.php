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
            case 'add_banks':
                include "includes/add_banks.php";
                break;

            case 'edit_banks':
                include "includes/edit_banks.php";
                break;

            case 'delete_banks':
                include "includes/delete_banks.php";
                break;

            default:
                include "includes/view_all_bankss.php";
                break;
        }
      ?>
    </div>
  </div>  
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModalbanks" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirm Deletion banks</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this Bank Account <strong id="banksTitle"></strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeletebanks" class="btn btn-main">Delete</a>
      </div>
    </div>
  </div>
</div>

</body></br>
</html>

<?php
include "includes/footer.php";
?>  