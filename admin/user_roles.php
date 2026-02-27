<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle delete action for roles
if (isset($_GET['delete_role'])) {
    $role_id = (int)$_GET['delete_role'];
    $check_query = "SELECT COUNT(*) as user_count FROM users WHERE role_id = $role_id";
    $check_result = mysqli_query($connection, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    if ($row['user_count'] > 0) {
        echo "<script>alert('Cannot delete role that is assigned to users.'); window.location.href='user_roles.php';</script>";
        exit();
    } else {
        $delete_query = "DELETE FROM user_roles WHERE role_id = $role_id";
        if (mysqli_query($connection, $delete_query)) {
            echo "<script>alert('User role deleted successfully!'); window.location.href='user_roles.php';</script>";
            exit();
        } else {
            $error = mysqli_error($connection);
            echo "<script>alert('Error deleting role: $error'); window.location.href='user_roles.php';</script>";
            exit();
        }
    }
}

// Handle delete action for types
if (isset($_GET['delete_type'])) {
    $type_id = (int)$_GET['delete_type'];
    $check_query = "SELECT COUNT(*) as user_count FROM users WHERE type_id = $type_id";
    $check_result = mysqli_query($connection, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    if ($row['user_count'] > 0) {
        echo "<script>alert('Cannot delete type that is assigned to users.'); window.location.href='user_roles.php';</script>";
        exit();
    } else {
        $delete_query = "DELETE FROM user_types WHERE type_id = $type_id";
        if (mysqli_query($connection, $delete_query)) {
            echo "<script>alert('User type deleted successfully!'); window.location.href='user_roles.php';</script>";
            exit();
        } else {
            $error = mysqli_error($connection);
            echo "<script>alert('Error deleting type: $error'); window.location.href='user_roles.php';</script>";
            exit();
        }
    }
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                <li class="breadcrumb-item active">Roles & Types</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_all';
                }

                switch($source) {
                    case 'add_role':
                        include "includes/add_user_role.php";
                        break;
                    case 'edit_role':
                        include "includes/edit_user_role.php";
                        break;
                    case 'add_type':
                        include "includes/add_user_type.php";
                        break;
                    case 'edit_type':
                        include "includes/edit_user_type.php";
                        break;
                    default:
                        include "includes/view_all_roles_types.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal for Roles -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteRoleModalLabel">Confirm Delete Role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the role "<span id="deleteRoleName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone if the role is not assigned to any users.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteRoleBtn" class="btn btn-danger">Delete Role</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal for Types -->
<div class="modal fade" id="deleteTypeModal" tabindex="-1" aria-labelledby="deleteTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteTypeModalLabel">Confirm Delete Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the type "<span id="deleteTypeName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone if the type is not assigned to any users.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteTypeBtn" class="btn btn-danger">Delete Type</a>
            </div>
        </div>
    </div>
</div>

<script>
// Show delete role confirmation modal
function confirmDeleteRole(id, name) {
    document.getElementById('deleteRoleName').textContent = name;
    document.getElementById('confirmDeleteRoleBtn').href = 'user_roles.php?delete_role=' + id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
    modal.show();
}

// Show delete type confirmation modal
function confirmDeleteType(id, name) {
    document.getElementById('deleteTypeName').textContent = name;
    document.getElementById('confirmDeleteTypeBtn').href = 'user_roles.php?delete_type=' + id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteTypeModal'));
    modal.show();
}
</script>

<?php include "includes/footer.php"; ?>