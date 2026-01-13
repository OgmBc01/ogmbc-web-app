<?php
include '../includes/database.php';

if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);
    
    $sql = "SELECT * FROM employees WHERE employee_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($employee = $result->fetch_assoc()) {
        ?>
        <div class="row">
            <div class="col-md-4 text-center">
                <?php
                $image_url = "";
                if (!empty($employee['user_image']) && file_exists("../uploads/profiles/" . $employee['user_image'])) {
                    $image_url = "../uploads/profiles/" . $employee['user_image'];
                } else {
                    $name = urlencode(($employee['first_name'] ?? '') . '+' . ($employee['last_name'] ?? ''));
                    $image_url = "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=150";
                }
                ?>
                <img src="<?php echo $image_url; ?>" 
                     alt="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>"
                     class="img-fluid rounded-circle mb-3" width="150" height="150"
                     onerror="this.src='https://ui-avatars.com/api/?name=Employee&background=f1bf70&color=0f172a&size=150'">
                <h4><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h4>
                <p class="text-muted">Employee ID: <?php echo $employee['employee_id']; ?></p>
                <p class="text-muted">User ID: <?php echo $employee['user_id']; ?></p>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-6 mb-3">
                        <strong class="d-block text-primary">Email:</strong>
                        <?php echo htmlspecialchars($employee['user_email']); ?>
                    </div>
                    <div class="col-6 mb-3">
                        <strong class="d-block text-primary">Field of Study:</strong>
                        <?php echo !empty($employee['field_of_study']) ? htmlspecialchars($employee['field_of_study']) : 'N/A'; ?>
                    </div>
                    <div class="col-6 mb-3">
                        <strong class="d-block text-primary">Qualification:</strong>
                        <?php echo !empty($employee['qualification']) ? htmlspecialchars($employee['qualification']) : 'N/A'; ?>
                    </div>
                    <div class="col-6 mb-3">
                        <strong class="d-block text-primary">Highest Graduation:</strong>
                        <?php echo !empty($employee['highest_graduation']) ? htmlspecialchars($employee['highest_graduation']) : 'N/A'; ?>
                    </div>
                    <div class="col-6 mb-3">
                        <strong class="d-block text-primary">Year of Graduation:</strong>
                        <?php echo !empty($employee['year_of_graduation']) ? htmlspecialchars($employee['year_of_graduation']) : 'N/A'; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Employee not found.</div>';
    }
    
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?>