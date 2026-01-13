<!DOCTYPE html>

<?php
include "../includes/database.php";
include "functions.php";
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --dark-blue: #0f172a;
            --medium-blue: #1e293b;
            --light-blue: #334155;
            --gold: #f1bf70;
            --light-gold: #f8d7a4;
            --text: #333333;
            --muted: #666666;
            --light-bg: #f8f9fa;
            --border: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: white;
            color: var(--text);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-title {
            color: var(--dark-blue);
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--gold);
        }
        
        .content-wrapper {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .form-section {
            flex: 1;
            min-width: 300px;
        }
        
        .table-section {
            flex: 2;
            min-width: 500px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            color: var(--dark-blue);
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(241, 191, 112, 0.2);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            background: var(--gold);
            color: var(--dark-blue);
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: var(--light-gold);
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background-color: var(--light-bg);
            color: var(--dark-blue);
            font-weight: 600;
        }
        
        tr:hover {
            background-color: rgba(241, 191, 112, 0.05);
        }
        
        .action-links a {
            color: var(--gold);
            text-decoration: none;
            margin-right: 1rem;
            transition: color 0.3s;
        }
        
        .action-links a:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s forwards;
        }
        
        .alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: #166534;
            border-left: 4px solid #16a34a;
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: inherit;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .modal-footer {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .btn-cancel {
            background-color: var(--muted);
            color: white;
        }
        
        .btn-danger {
            background-color: #dc2626;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .form-section, .table-section {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Categories Management</h1>
        
        <!-- Alert Messages -->
        <div id="alertBox"></div>
        
        <div class="content-wrapper">
            <!-- Form Section -->
            <div class="form-section">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-plus-circle"></i> Add New Category
                    </div>
                    
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="cat_title">Category Name</label>
                            <input type="text" id="cat_title" name="cat_title" class="form-control" required>
                        </div>
                        
                        <button type="submit" name="submit" class="btn">
                            <i class="bi bi-check-lg"></i> Add Category
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
                        <i class="bi bi-list-ul"></i> Existing Categories
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // PHP code for displaying categories
                            
                            // Call the function
                            findAllCategories();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
            </div>
            <p>Are you sure you want to delete the category "<span id="categoryName"></span>"?</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteCategory()">Delete</button>
            </div>
        </div>
    </div>
    
    <script>
        // Show alert message
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.innerHTML = `
                <div class="alert alert-${type}">
                    <span>${message}</span>
                    <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            `;
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                if (alertBox.firstChild) {
                    alertBox.firstChild.style.display = 'none';
                }
            }, 3000);
        }
        
        // Modal functions
        let deleteId = null;
        let deleteName = null;
        
        function setDeleteId(id, name) {
            deleteId = id;
            deleteName = name;
            document.getElementById('categoryName').textContent = name;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            deleteId = null;
            deleteName = null;
        }
        
        function deleteCategory() {
            if (deleteId) {
                window.location.href = `categories.php?delete_category=${deleteId}`;
            }
        }
        
        // Close modal if clicked outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        };
        
        // Check if there's a success or error message from PHP
        window.onload = function() {
            <?php
            if (isset($_GET['deleted']) && $_GET['deleted'] == 'true') {
                echo "showAlert('Category deleted successfully!', 'success');";
            }
            
            if (isset($_GET['error'])) {
                echo "showAlert('Error: Could not delete category.', 'error');";
            }
            ?>
        };
    </script>
    
    <?php
    
    // Call the function
    deleteCategory();
    ?>
</body>
</html>