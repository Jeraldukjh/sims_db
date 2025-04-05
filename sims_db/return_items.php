

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Items - SIMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #1abc9c;
        }

        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #1abc9c;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
            z-index: 1000;
        }

        .toggle-btn:hover {
            background: #2471a3;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
            transition: margin-left 0.3s ease-in-out;
        }

        .content.collapsed {
            margin-left: 0;
        }

        .inventory-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s forwards ease-out;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h3 {
            margin-top: 0;
            color: #1abc9c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #1abc9c;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #d1f2eb;
        }

        .return-request {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .return-request:hover {
            background: #2471a3;
        }

        .return-request.success {
            background: #2ecc71;
            animation: bounce 0.5s;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <button class="toggle-btn" id="toggle-btn">☰</button>
    <nav class="sidebar" id="sidebar">
        <h2>SIMS</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="borrow_items.php">Borrow Items</a></li>
            <li><a href="return_items.php" class="active">Return Items</a></li>
            <li><a href="products.php">Inventory</a></li>
            <li><a href="home.php">Logout</a></li>
        </ul>
    </nav>
    <main class="content" id="content">
        <div class="inventory-box">
            <h3>Borrowed Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($borrowedItems)): ?>
                        <?php foreach ($borrowedItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['due_date']); ?></td>
                                <td>
                                    <button class="return-request" data-id="<?php echo htmlspecialchars($item['product_id']); ?>">Request Return</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">No borrowed items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
   $(document).on('click', '.return-request', function() {
    var button = $(this);
    var productId = button.data('id');

    console.log("Button clicked! Product ID:", productId);

    button.prop('disabled', true).text('Processing...');

    $.post('return_items.php', { return_item: true, product_id: productId }, function(response) {
        console.log("✅ Server Response:", response);

        if (response.status === "success") {
            button.addClass('success').text('Request Sent');
        } else {
            button.prop('disabled', false).text('Request Return');
            alert(response.message || "Request failed.");
        }
    }, "json")
    .fail(function(xhr) {
        console.error("❌ AJAX Error:", xhr.responseText);
        alert("Error: " + (xhr.responseJSON?.message || "An error occurred. Please try again."));
        button.prop('disabled', false).text('Request Return');
    });
});

    </script>
</body>
</html>