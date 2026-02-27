<?php
session_start();
include dirname(__DIR__) . '/includes/database.php';

if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Unauthorized access.</div>';
    exit();
}

if (isset($_GET['client_id'])) {
    $client_id = intval($_GET['client_id']);
    
    // Get client documents
    $docs_sql = "SELECT cd.*, u.first_name, u.last_name 
                FROM client_documents cd 
                LEFT JOIN users u ON cd.uploaded_by = u.user_id 
                WHERE cd.client_id = ? 
                ORDER BY cd.uploaded_at DESC";
    $docs_stmt = $connection->prepare($docs_sql);
    
    if ($docs_stmt) {
        $docs_stmt->bind_param("i", $client_id);
        $docs_stmt->execute();
        $docs_result = $docs_stmt->get_result();
        ?>

        <?php if ($docs_result->num_rows > 0): ?>
            <?php while ($doc = $docs_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doc['document_title']); ?></td>
                    <td>
                        <span class="badge bg-secondary">
                            <?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></td>
                    <td><?php echo date('M j, Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                    <td>
                        <?php 
                        $file_path = $doc['file_path'];
                        $full_file_path = '../' . $file_path;
                        $file_exists = file_exists($full_file_path);
                        ?>
                        <?php if (!empty($doc['file_path']) && $file_exists): ?>
                            <a href="<?php echo $full_file_path; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="<?php echo $full_file_path; ?>" download class="btn btn-sm btn-outline-success">
                                <i class="bi bi-download"></i> Download
                            </a>
                        <?php else: ?>
                            <span class="text-muted">
                                <?php if (empty($doc['file_path'])): ?>
                                    No file path
                                <?php else: ?>
                                    File not found
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-folder-x display-4"></i>
                    <p class="mt-2">No documents uploaded yet</p>
                </td>
            </tr>
        <?php endif; ?>
        
        <?php
        $docs_stmt->close();
    } else {
        echo '<tr><td colspan="5" class="text-center text-danger">Error loading documents</td></tr>';
    }
} else {
    echo '<tr><td colspan="5" class="text-center text-danger">Invalid client ID</td></tr>';
}
?>