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

