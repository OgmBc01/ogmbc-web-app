<?php
include dirname(__DIR__) . '/includes/database.php';
header('Content-Type: application/json');

if (isset($_GET['country']) && !empty($_GET['country'])) {
    $country = mysqli_real_escape_string($connection, $_GET['country']);
    $query = "SELECT jurisdiction_name FROM jurisdictions WHERE country_id = '$country' AND is_active = 1 ORDER BY jurisdiction_name";
    $result = mysqli_query($connection, $query);
    
    $jurisdictions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jurisdictions[] = $row;
    }
    
    echo json_encode(['success' => true, 'jurisdictions' => $jurisdictions]);
} else {
    echo json_encode(['success' => false, 'message' => 'No country selected']);
}
?>
