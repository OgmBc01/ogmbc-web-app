<?php

// Function to insert/update categories
function insert_categories() {
    global $connection;

    if(isset($_POST['submit'])) {
        $cat_title = mysqli_real_escape_string($connection, $_POST['cat_title']);
        $cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;

        if($cat_title == "" || empty($cat_title)) {
            echo "<script>showAlert('This field should not be empty', 'error');</script>";
        } else {
            if ($cat_id > 0) {
                // Update existing category
                $query = "UPDATE categories SET cat_title = '{$cat_title}' WHERE cat_id = {$cat_id}";
                $success_message = "Category updated successfully!";
                $redirect_param = "updated=true";
            } else {
                // Insert new category
                $query = "INSERT INTO categories(cat_title) VALUES('{$cat_title}')";
                $success_message = "Category added successfully!";
                $redirect_param = "added=true";
            }

            $category_query = mysqli_query($connection, $query);

            if(!$category_query) {
                die('Query Failed' . mysqli_error($connection));
            } else {
                // Use JavaScript redirect instead of header redirect
                echo "<script>window.location.href = 'categories.php?{$redirect_param}';</script>";
                exit;
            }
        }
    }
}


//////////////// FIND ALL CATEGORIES //////////////////
    function findAllCategories() {
    global $connection;

    $query = "SELECT * FROM categories";
    $select_all_categories_query = mysqli_query($connection, $query);

    while($row = mysqli_fetch_assoc($select_all_categories_query)) {
        $cat_id = $row['cat_id'];
        $cat_title = $row['cat_title'];

        echo "<tr>";
        echo "<td>{$cat_id}</td>";
        echo "<td>{$cat_title}</td>";
        echo "<td class='action-links'>";
        echo "<a href='categories.php?edit={$cat_id}'><i class='bi bi-pencil'></i> Edit</a>";
        echo "<a href='' data-bs-toggle='modal' data-bs-target='#confirmDeleteModalCategory' data-id='{$cat_id}' data-name='{$cat_title}' onclick='setDeleteId({$cat_id}, \"{$cat_title}\")'><i class='bi bi-trash'></i> Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
}

// Function to delete categories
function deleteCategory() {
    global $connection;

    if (isset($_GET['delete_category'])) {
        $cat_id = intval($_GET['delete_category']); // Sanitize the input

        // Delete query
        $query = "DELETE FROM categories WHERE cat_id = {$cat_id}";
        $delete_query = mysqli_query($connection, $query);

        if ($delete_query) {
            // Use JavaScript redirect instead of header redirect
            echo "<script>window.location.href = 'categories.php?deleted=true';</script>";
            exit;
        } else {
            // Handle deletion error
            echo "<script>window.location.href = 'categories.php?error=true';</script>";
            exit;
        }
    }
}

?>
