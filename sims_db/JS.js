// Profile Picture Upload
document.getElementById("profile-upload").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append("profile_pic", file);

        fetch("upload.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Profile Upload Response:", data); // Debugging

            if (data.status === "success") {
                document.getElementById("profileImage").src = data.filePath;
                alert("Profile picture updated successfully!");
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Profile Upload Error:", error));
    } else {
        console.error("No file selected");
    }
});

// Product Filtering by Category
document.getElementById("categoryFilter").addEventListener("change", function() {
    const selectedCategory = this.value.trim();

    if (selectedCategory === "") {
        console.error("No category selected");
        return;
    }

    console.log("Fetching products for category:", selectedCategory); // Debugging

    fetch("fetch_products.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "category=" + encodeURIComponent(selectedCategory)
    })
    .then(response => response.json())
    .then(data => {
        console.log("Received Products Data:", data); // Debugging

        const tableBody = document.getElementById("productsTableBody");
        tableBody.innerHTML = ""; // Clear table before adding new rows

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(product => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${product.product_id}</td>
                    <td>${product.product_name}</td>
                    <td>${product.stock}</td>
                    <td>${product.category}</td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            console.warn("No products found for this category.");
            tableBody.innerHTML = "<tr><td colspan='4'>No products available.</td></tr>";
        }
    })
    .catch(error => console.error("Fetch Products Error:", error));
});
