<form class="mt-5" action="" method="POST">
    <label for="cat-title" class="form-label">Edit Category</label>
    
    <?php // EDIT CATEGORY
        
        if(isset($_GET['edit'])){

            $cat_id = $_GET['edit'];

            $query = "SELECT * FROM categories WHERE cat_id = $cat_id ";
            $select_categories_id = mysqli_query($connection,$query);

            while($row = mysqli_fetch_assoc($select_categories_id)) {
                $cat_id = $row['cat_id'];
                $cat_title = $row['cat_title'];

                ?>
            <input value="<?php if(isset($cat_title)){echo $cat_title;} ?>" type="text" class="form-control custom-field" name="cat_title" id="">
        
        <?php }} ?>

        <?php // UPDATE CATEGORY

        if(isset($_POST['update_category'])){
            $the_cat_title = $_POST['cat_title'];
            $query = "UPDATE categories SET cat_title = '{$the_cat_title}' WHERE cat_id = {$cat_id} ";
            $update_query = mysqli_query($connection,$query);

            if(!$update_query) {

                die("Query Failed" . mysqli_error($connection));
                }
            }
        ?>
    <div class="d-flex justify-content-center align-items-center mt-4 gap-2">
    <button type="reset" class="btn btn-clear" style="border-radius: 28px">Clear</button>
    <input type="submit" class="btn btn-main" name="update_category" value="Update Category">
</div>
</form>