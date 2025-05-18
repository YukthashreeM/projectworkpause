<?php 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];
    $leave_type = $_POST['leave_type']; // Get leave type from the form
    $reason = htmlspecialchars($_POST['reason']);

    if (!empty($from) && !empty($to) && !empty($reason) && !empty($leave_type)) {
        // Insert leave request into the database
        $stmt = $conn->prepare("INSERT INTO leaves (user_id, from_date, to_date, leave_type, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $from, $to, $leave_type, $reason);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success'] = "Leave request submitted successfully.";
        header("Location: apply_leave.php"); // Redirect after form submission to avoid re-submit
        exit();
    } else {
        $message = "<div class='error'>All fields are required.</div>";
    }
}
?>