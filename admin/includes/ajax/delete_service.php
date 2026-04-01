<?php
session_start();
require_once __DIR__ . '/../../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
	echo json_encode(['success' => false, 'message' => 'Unauthorized']);
	exit();
}

$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
if ($service_id <= 0) {
	echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
	exit();
}

// Check if service is used in any engagements
$check_query = "SELECT COUNT(*) as engagement_count FROM engagements WHERE service_id = $service_id";
$check_result = mysqli_query($connection, $check_query);
$row = mysqli_fetch_assoc($check_result);

if ($row['engagement_count'] > 0) {
	echo json_encode(['success' => false, 'message' => 'Cannot delete service that is used in engagements.']);
	exit();
}

// First delete related point rules
$delete_rules = "DELETE FROM service_point_rules WHERE service_id = $service_id";
mysqli_query($connection, $delete_rules);

// Then delete the service
$delete_query = "DELETE FROM service_types WHERE service_id = $service_id";
if (mysqli_query($connection, $delete_query)) {
	echo json_encode(['success' => true, 'message' => 'Service deleted successfully!']);
} else {
	echo json_encode(['success' => false, 'message' => 'Error deleting service: ' . mysqli_error($connection)]);
}
?>
