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
        case 'add_employee';
        include "includes/add_employee.php";
        break;

        case 'edit_employee';
        include "includes/edit_employee.php";
        break;

        default:
        include "includes/view_all_employees.php";
        break;
      }

      ?>
    </div>
  </div>  
</div>

</body></br>
</html>

<?php
include "includes/footer.php";
?>  