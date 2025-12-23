<?php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['experience_id'])) {
    $experienceId = intval($_POST['experience_id']);
    
    try {
        $pdo->beginTransaction();
        
        // Delete manoeuvres first (if no CASCADE is set)
        $stmt = $pdo->prepare("DELETE FROM driving_experience_manoeuvres WHERE Experience_ID = ?");
        $stmt->execute([$experienceId]);
        
        // Delete the driving experience
        $stmt = $pdo->prepare("DELETE FROM driving_experience WHERE Experience_ID = ?");
        $stmt->execute([$experienceId]);
        
        $pdo->commit();
        
        header("Location: summary.php?deleted=1");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error deleting experience: " . $e->getMessage());
    }
} else {
    header("Location: summary.php");
    exit();
}
?>