<form class="mt-5" action="" method="POST">
    <label for="cat-title" class="form-label">Edit Category</label>
    
    <?php 
    // EDIT CATEGORY - Populate form with existing data
    if(isset($_GET['edit'])){
        $cat_id = $_GET['edit'];

        $query = "SELECT * FROM categories WHERE cat_id = $cat_id ";
        $select_categories_id = mysqli_query($connection,$query);

        while($row = mysqli_fetch_assoc($select_categories_id)) {
            $cat_id = $row['cat_id'];
            $cat_title = $row['cat_title'];
            $cat_price = isset($row['cat_price']) ? $row['cat_price'] : 0.00;
            ?>
            
            <!-- Category Title Field -->
            <div class="mb-3">
                <label for="cat_title" class="form-label">Category Title</label>
                <input value="<?php echo htmlspecialchars($cat_title); ?>" 
                       type="text" 
                       class="form-control custom-field" 
                       name="cat_title" 
                       id="cat_title"
                       required>
            </div>
            
            <!-- Category Price Field -->
            <div class="mb-3">
                <label for="cat_price" class="form-label">Category Price ($)</label>
                <input value="<?php echo htmlspecialchars($cat_price); ?>" 
                       type="number" 
                       class="form-control custom-field" 
                       name="cat_price" 
                       id="cat_price"
                       step="0.01" 
                       min="0" 
                       required>
            </div>
            
            <!-- Hidden field for category ID -->
            <input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">
            
        <?php 
        }
    } 
    ?>

    <div class="d-flex justify-content-center align-items-center mt-4 gap-2">
        <button type="reset" class="btn btn-clear" style="border-radius: 28px">Clear</button>
        <!-- Changed from update_category to submit to use the main function -->
        <input type="submit" class="btn btn-main" name="submit" value="Update Category">
    </div>
</form>