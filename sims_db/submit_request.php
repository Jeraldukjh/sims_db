if (isset($_POST['due_date']) && isset($_POST['student_id']) && isset($_POST['product_id'])) {
    $due_date = $_POST['due_date'];
    $due_time = date('H:i', strtotime($due_date)); // Convert sa 24-hour format (HH:MM)

    if (strtotime($due_time) >= strtotime("07:00") && strtotime($due_time) <= strtotime("17:00")) {
        $query = "INSERT INTO borrow_requests (student_id, product_id, due_date, status) 
                  VALUES (:student_id, :product_id, :due_date, 'pending')";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':student_id' => $_POST['student_id'],
            ':product_id' => $_POST['product_id'],
            ':due_date' => $due_date
        ]);
        echo "Request submitted successfully!";
    } else {
        echo "Error: Due date must be between 7:00 AM and 5:00 PM!";
    }
}
