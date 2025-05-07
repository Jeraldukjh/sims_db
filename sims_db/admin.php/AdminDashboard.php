<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <h2>Borrow Requests</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Product</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tbody id="borrowRequests">
            <!-- AJAX Loads Data Here -->
        </tbody>
    </table>

    <script>
        function loadRequests() {
            $.ajax({
                url: "ajax/load_requests.php",
                method: "GET",
                success: function (data) {
                    $("#borrowRequests").html(data);
                }
            });
        }

        function updateStatus(id, action) {
            $.post("ajax/process.php", { id: id, action: action }, function(response) {
                alert(response);
                loadRequests();
            });
        }

        $(document).ready(function () {
            loadRequests();
            setInterval(loadRequests, 5000);
        });
    </script>

</body>
</html>
