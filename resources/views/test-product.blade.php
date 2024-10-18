<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Product API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Test Product API</h2>
        
        <!-- Form to input the product ID -->
        <form id="product-form">
            <div class="mb-3">
                <label for="productId" class="form-label">Product ID:</label>
                <input type="number" id="productId" class="form-control" placeholder="Enter Product ID" required>
            </div>
            <button type="submit" class="btn btn-primary">Get Product</button>
        </form>

        <!-- Area to display product details -->
        <div class="mt-4">
            <h4>Product Details:</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody id="product-details"></tbody>
            </table>
            <div id="product-image-container" class="mt-3"></div>
        </div>

        <!-- Update Product Form fields -->
        <div class="mt-5" id="update-form" style="display: none;">
            <h4>Update Product:</h4>
            <table class="table">
                <tbody id="update-fields">
                    <tr>
                        <td>Name</td>
                        <td>
                            <span id="currentName"></span>
                            <input type="text" id="updateName" class="form-control" style="display: inline-block; width: auto;">
                            <button type="button" class="btn btn-warning" onclick="updateField('name')">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>
                            <span id="currentDescription"></span>
                            <input type="text" id="updateDescription" class="form-control" style="display: inline-block; width: auto;">
                            <button type="button" class="btn btn-warning" onclick="updateField('description')">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Price</td>
                        <td>
                            <span id="currentPrice"></span>
                            <input type="number" id="updatePrice" class="form-control" style="display: inline-block; width: auto;">
                            <button type="button" class="btn btn-warning" onclick="updateField('price')">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Image URL</td>
                        <td>
                            <span id="currentImage"></span>
                            <input type="text" id="updateImage" class="form-control" style="display: inline-block; width: auto;">
                            <button type="button" class="btn btn-warning" onclick="updateField('image')">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Supplier ID</td>
                        <td>
                            <input type="number" id="updateSupplierId" class="form-control" style="display: inline-block; width: auto;">
                            <button type="button" class="btn btn-warning" onclick="updateField('supplier_id')">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Category ID</td>
                        <td>
                            <input type="number" id="updateCategoryId" class="form-control" style="display: inline-block; width: auto;">
                            <button type="button" class="btn btn-warning" onclick="updateField('category_id')">Update</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Fetch product details
            $('#product-form').on('submit', function (e) {
                e.preventDefault();
                const productId = $('#productId').val();
                $.ajax({
                    url: `/api/products/${productId}`,
                    method: 'GET',
                    success: function (response) {
                        // Clear previous details
                        $('#product-details').empty();
                        
                        // Populate the table with product details
                        $('#product-details').append(`
                            <tr><td>ID</td><td>${response.id}</td></tr>
                            <tr><td>Name</td><td id="currentName">${response.name}</td></tr>
                            <tr><td>Description</td><td id="currentDescription">${response.description}</td></tr>
                            <tr><td>Price</td><td id="currentPrice">${response.price}</td></tr>
                            <tr><td>Category</td><td>${response.category_name}</td></tr>
                            <tr><td>Supplier</td><td>${response.supplier_name}</td></tr>
                            <tr><td>Image</td><td>${response.image ? `<img src="${response.image.startsWith('http') ? response.image : 'http://127.0.0.1:8000/' + response.image}" alt="Product Image" class="img-fluid" style="max-width: 100px;">` : 'No image available.'}</td></tr>
                            <tr><td>Created At</td><td>${response.created_at}</td></tr>
                            <tr><td>Updated At</td><td>${response.updated_at}</td></tr>
                        `);
                        
                        // Populate the update fields with current values
                        $('#updateName').val(response.name);
                        $('#updateDescription').val(response.description);
                        $('#updatePrice').val(response.price);
                        $('#updateImage').val(response.image);
                        $('#updateSupplierId').val(response.supplier_id);
                        $('#updateCategoryId').val(response.category_id);
                        
                        // Show the update form
                        $('#update-form').show();
                    },
                    error: function (xhr) {
                        $('#product-details').html('Error: ' + xhr.responseJSON.message);
                    }
                });
            });
        });

        // Update product data
        function updateField(field) {
            const productId = $('#productId').val();
            const data = {};

            switch (field) {
                case 'name':
                    data.name = $('#updateName').val();
                    break;
                case 'description':
                    data.description = $('#updateDescription').val();
                    break;
                case 'price':
                    data.price = $('#updatePrice').val();
                    break;
                case 'image':
                    data.image = $('#updateImage').val();
                    break;
                case 'supplier_id':
                    data.supplier_id = $('#updateSupplierId').val();
                    break;
                case 'category_id':
                    data.category_id = $('#updateCategoryId').val();
                    break;
            }

            $.ajax({
                url: `/api/products/${productId}`,
                method: 'PUT',
                data: data,
                success: function (response) {
                    alert('Product updated successfully!');
                    // Refresh product details
                    $('#product-details').empty();
                    $('#product-details').append(`
                        <tr><td>ID</td><td>${response.id}</td></tr>
                        <tr><td>Name</td><td id="currentName">${response.name}</td></tr>
                        <tr><td>Description</td><td id="currentDescription">${response.description}</td></tr>
                        <tr><td>Price</td><td id="currentPrice">${response.price}</td></tr>
                        <tr><td>Category</td><td>${response.category_name}</td></tr>
                        <tr><td>Supplier</td><td>${response.supplier_name}</td></tr>
                        <tr><td>Image</td><td>${response.image ? `<img src="${response.image.startsWith('http') ? response.image : 'http://127.0.0.1:8000/' + response.image}" alt="Product Image" class="img-fluid" style="max-width: 100px;">` : 'No image available.'}</td></tr>
                        <tr><td>Created At</td><td>${response.created_at}</td></tr>
                        <tr><td>Updated At</td><td>${response.updated_at}</td></tr>
                    `);
                },
                error: function (xhr) {
                    $('#product-details').html('Error: ' + xhr.responseJSON.message);
                }
            });
        }
    </script>
</body>
</html>
