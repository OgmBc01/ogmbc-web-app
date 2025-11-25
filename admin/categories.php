<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php'
?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            
           <h1 class="page-title">Services Management</h1>
        
        <!-- Alert Messages -->
        <div id="alertBox"></div>
        
        <div class="content-wrapper">
            <!-- Form Section -->
            <div class="form-section">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-plus-circle"></i> Add New Service
                    </div>
                    
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="cat_title">Service Name</label>
                            <input type="text" id="cat_title" name="cat_title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="cat_price">Price</label>
                            <input type="number" step="0.01" id="cat_price" name="cat_price" class="form-control" required>
                        </div>
                        
                        <button type="submit" name="submit" class="btn">
                            <i class="bi bi-check-lg"></i> Add Service
                        </button>
                    </form>
                    <?php
                    // Call the function
                    insert_categories();
                    ?>
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="table-section">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list-ul"></i> Existing Services
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service Name</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php  
                            // Call the function
                            findAllCategories();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
 
        <?php //UPDATE CATEGORY AND INCLUDE
      
      if(isset($_GET['edit'])) {

        $cat_id = $_GET['edit'];

      include "./includes/update_categories.php";
      }
      
      ?>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
            </div>
            <p>Are you sure you want to delete the service "<span id="categoryName"></span>"?</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteCategory()">Delete</button>
            </div>
        </div>
    </div>
    
    <?php
    
    // Call the function
    deleteCategory();
    ?>
        </div>
    </div>

<?php
include 'includes/footer.php'
?>