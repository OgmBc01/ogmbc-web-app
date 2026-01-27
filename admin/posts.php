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
            case 'add_post':
                include "includes/add_post.php";
                break;

            case 'edit_post':
                include "includes/edit_post.php";
                break;

            case 'delete_post':
                include "includes/delete_post.php";
                break;

            default:
                include "includes/view_all_posts.php";
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
</html>

<?php
include "includes/footer.php";
?> 

<script>
tinymce.init({
    selector: '#post_content',
    height: 450,
    menubar: true,
    branding: false,
    plugins: [
        'advlist autolink lists link image charmap preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table code wordcount'
    ],
    toolbar:
        'undo redo | formatselect | bold italic underline strikethrough | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | ' +
        'link image media table | removeformat | code fullscreen',
    images_upload_url: '?action=tinymce_upload',
    images_upload_credentials: true,
    automatic_uploads: true,
    image_title: true,
    file_picker_types: 'image',
    content_style: `
        body {
            font-family: Arial, sans-serif;
            font-size: 15px;
        }
        img {
            max-width: 100%;
            height: auto;
        }
    `
});
</script>
