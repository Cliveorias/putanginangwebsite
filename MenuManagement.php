<?php
include "db_conn.php"; // Koneksyon sa database[cite: 1, 8]

// --- LOGIC PARA SA STATUS TOGGLE (Fallback) ---[cite: 9]
if (isset($_GET['id']) && isset($_GET['status']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    mysqli_query($conn, "UPDATE menu_items SET availability = '$status' WHERE id = '$id'");
    header("Location: MenuManagement.php"); 
    exit();
}

// --- LOGIC PARA SA PAG-ADD NG BAGONG ITEM ---[cite: 8, 9]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);

    $add_sql = "INSERT INTO menu_items (name, description, price, category, image_url, availability) 
                VALUES ('$name', '$description', '$price', '$category', '$image_url', 1)";
    
    mysqli_query($conn, $add_sql);
    header("Location: MenuManagement.php?added=1");
    exit();
}

// --- LOGIC PARA SA EDIT/UPDATE NG PAGKAIN ---[cite: 8, 9]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $id = mysqli_real_escape_string($conn, $_POST['item_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);

    $update_sql = "UPDATE menu_items SET name='$name', price='$price', category='$category', description='$description', image_url='$image_url' WHERE id='$id'";
    mysqli_query($conn, $update_sql);
    header("Location: MenuManagement.php?success=1");
    exit();
}

// --- LOGIC PARA SA RENAME NG CATEGORY ---[cite: 8, 9]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rename_category'])) {
    $old_cat = mysqli_real_escape_string($conn, $_POST['old_category_name']);
    $new_cat = mysqli_real_escape_string($conn, $_POST['new_category_name']);

    $rename_sql = "UPDATE menu_items SET category = '$new_cat' WHERE category = '$old_cat'";
    mysqli_query($conn, $rename_sql);
    header("Location: MenuManagement.php?cat_updated=1");
    exit();
}

// Kunin ang lahat ng items para sa table[cite: 8]
$menu_items = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY category ASC");

// Kunin ang mga unique categories[cite: 8]
$categories_query = mysqli_query($conn, "SELECT DISTINCT category FROM menu_items");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management | Kainan ni Ate Kabayan</title>
    <link rel="stylesheet" href="Dashboard.css"> 
    <link rel="stylesheet" href="MenuManagement.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* UI FIXES */
        .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #F4A42B; }
        input:checked + .slider:before { transform: translateX(20px); }

        .item-img { width: 50px !important; height: 50px !important; border-radius: 50%; object-fit: cover; border: 2px solid #F4A42B; }
        .item-cell { display: flex; align-items: center; gap: 15px; }

        /* Icon Buttons Styling */
        .action-btn { border: none; background: none; cursor: pointer; font-size: 1.1rem; transition: 0.3s; padding: 5px; }
        .edit-icon { color: #3498db; }
        .view-icon { color: #2ecc71; }
        .action-btn:hover { transform: scale(1.2); }

        /* Category Item Design Fix */
        .category-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1px solid #eee; transition: 0.3s; }
        .category-item:hover { background: #fffcf8; }
        .category-item span { font-weight: 600; color: #444; text-transform: capitalize; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); }
        .modal-content { background: white; margin: 5% auto; padding: 25px; width: 450px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .modal-header h3 { color: #F4A42B; font-family: 'Fredoka One'; }
        .close-modal { cursor: pointer; font-size: 1.5rem; color: #999; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.85rem; margin-bottom: 5px; color: #666; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Poppins'; }
        .btn-save { width: 100%; background: #F4A42B; color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; margin-top: 10px; }
        
        /* Overview Card Style */
        .overview-img { width: 100%; height: 200px; border-radius: 15px; object-fit: cover; margin-bottom: 15px; }
        .overview-price { font-size: 1.5rem; color: #F4A42B; font-weight: 800; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo-box">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
            <nav>
                <!-- Existing Links -->
        <a href="Dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="MenuManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'MenuManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-utensils"></i> Menu Management</a>
        <a href="StaffActivity.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'StaffActivity.php') ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> Staff & Activity</a>

        <!-- BAGONG SEKSYON: Customer Management at Activity Log -->
        <a href="CustomerManagement.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'CustomerManagement.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-group"></i> Customer Management</a>
        <a href="ActivityLog.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ActivityLog.php') ? 'active' : ''; ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>

        <!-- Existing Links Continued -->
        <a href="ServiceCenter.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ServiceCenter.php') ? 'active' : ''; ?>"><i class="fa-solid fa-headset"></i> Service Center</a>
        <a href="Sales&Promotion.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Sales&Promotion.php') ? 'active' : ''; ?>"><i class="fa-solid fa-tags"></i> Sales & Promotion</a>
        <a href="Settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Settings.php') ? 'active' : ''; ?>"><i class="fa-solid fa-gear"></i> Settings</a>
        </nav>
        </aside>

        <main>
            <header>
                <div class="admin-title">
                    <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo" class="mini-logo">
                    KAINAN NI ATE KABAYAN | <span>ADMIN</span>
                </div>
                <div class="header-icons">
                    <i class="fa-solid fa-comment-dots"></i>
                    <i class="fa-solid fa-bell"></i>
                    <i class="fa-solid fa-bars"></i>
                </div>
            </header>

            <section class="content">
                <h2 class="page-main-title">Menu Administration</h2>

                <div class="menu-layout-grid">
                    <div class="card menu-items-card">
                        <div class="card-header-orange">
                            <h3>MENU ITEMS</h3>
                            <div class="header-btns">
                                <button class="btn-white-add" onclick="openAddModal()">+ Add New Item</button>
                                <select class="category-select" id="category-dropdown-filter">
                                    <option value="All">All Categories</option>
                                    <?php 
                                    mysqli_data_seek($categories_query, 0); 
                                    while($cat = mysqli_fetch_assoc($categories_query)) { 
                                        echo "<option value='".$cat['category']."'>".ucfirst($cat['category'])."</option>";
                                    } 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="menu-table-body">
                                    <?php while($row = mysqli_fetch_assoc($menu_items)): ?>
                                    <tr>
                                        <td>
                                            <div class="item-cell">
                                                <img src="<?php echo $row['image_url']; ?>" alt="Dish" class="item-img">
                                                <span><?php echo $row['name']; ?></span>
                                            </div>
                                        </td>
                                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                        <td><span class="badge-cat"><?php echo $row['category']; ?></span></td>
                                        <td>
                                            <label class="switch">
                                                <input type="checkbox" <?php echo ($row['availability'] == 1) ? 'checked' : ''; ?> 
                                                       onchange="updateAvailability(this, <?php echo $row['id']; ?>)">
                                                <span class="slider"></span>
                                            </label>
                                        </td>
                                        <td class="action-cell">
                                            <button class="action-btn edit-icon" 
                                                onclick='openEditModal(<?php echo json_encode($row); ?>)'>
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button class="action-btn view-icon"
                                                onclick='openViewModal(<?php echo json_encode($row); ?>)'>
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card category-card">
                        <div class="card-header-orange">
                            <h3>CATEGORY MANAGEMENT</h3>
                        </div>
                        <div class="category-list" id="category-mgmt-list">
                            <?php 
                            mysqli_data_seek($categories_query, 0); 
                            while($cat = mysqli_fetch_assoc($categories_query)): 
                                $cat_name = $cat['category'];
                            ?>
                                <div class="category-item">
                                    <span><?php echo $cat_name; ?></span>
                                    <div class="cat-actions">
                                        <!-- IN-UPDATE PARA MAG-FUNCTION ANG PEN AT EYE[cite: 11] -->
                                        <button class="action-btn edit-icon" onclick="openEditCategoryModal('<?php echo $cat_name; ?>')"><i class="fa-solid fa-pen"></i></button>
                                        <button class="action-btn view-icon" onclick="filterByCategory('<?php echo $cat_name; ?>')"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <!-- IN-UPDATE PARA MAG-FUNCTION ANG ADD[cite: 11] -->
                        <button class="btn-orange-add-cat" onclick="openAddCategoryModal()">Add</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAL PARA SA ADD NEW ITEM -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Menu Item</h3>
                <span class="close-modal" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form action="MenuManagement.php" method="POST">
                <div class="form-group">
                    <label>Food Name</label>
                    <input type="text" name="name" placeholder="e.g. Special Lugaw" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the dish..." required></textarea>
                </div>
                <div style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label>Price (₱)</label>
                        <input type="number" step="0.01" name="price" placeholder="0.00" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Category</label>
                        <input type="text" name="category" id="add_item_category" placeholder="e.g. Lugaw" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" placeholder="Cloudinary link here..." required>
                </div>
                <button type="submit" name="add_item" class="btn-save">Add to Menu</button>
            </form>
        </div>
    </div>

    <!-- MODAL PARA SA EDIT ITEM -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Menu Item</h3>
                <span class="close-modal" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form action="MenuManagement.php" method="POST">
                <input type="hidden" name="item_id" id="edit_id">
                <div class="form-group">
                    <label>Food Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_desc" rows="3" required></textarea>
                </div>
                <div style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label>Price (₱)</label>
                        <input type="number" step="0.01" name="price" id="edit_price" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Category</label>
                        <input type="text" name="category" id="edit_cat" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" id="edit_img" required>
                </div>
                <button type="submit" name="update_item" class="btn-save">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- MODAL PARA SA EDIT CATEGORY[cite: 11] -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Rename Category</h3>
                <span class="close-modal" onclick="closeModal('editCategoryModal')">&times;</span>
            </div>
            <form action="MenuManagement.php" method="POST">
                <input type="hidden" name="old_category_name" id="old_cat_name">
                <div class="form-group">
                    <label>New Category Name</label>
                    <input type="text" name="new_category_name" id="new_cat_name" required>
                </div>
                <button type="submit" name="rename_category" class="btn-save">Update Category</button>
            </form>
        </div>
    </div>

    <!-- MODAL PARA SA VIEW OVERVIEW -->
    <div id="viewModal" class="modal">
        <div class="modal-content" style="text-align: center;">
            <div class="modal-header">
                <h3 id="view_name">Overview</h3>
                <span class="close-modal" onclick="closeModal('viewModal')">&times;</span>
            </div>
            <img id="view_img" src="" class="overview-img">
            <div id="view_price" class="overview-price">₱0.00</div>
            <div id="view_cat" style="color:#999; margin-bottom:10px; font-weight:600;">Category</div>
            <p id="view_desc" style="color:#555; line-height:1.5;">Food description goes here...</p>
        </div>
    </div>

    <script>
        function updateAvailability(checkbox, id) {
            const status = checkbox.checked ? 1 : 0;
            fetch(`update_status.php?id=${id}&status=${status}`)
            .then(response => response.text())
            .then(data => {
                if (data.trim() !== "Success") {
                    alert("May problema sa pag-update, Kabayan.");
                    checkbox.checked = !checkbox.checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                checkbox.checked = !checkbox.checked;
            });
        }

        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        // PAG-OPEN NG ADD CATEGORY MODAL (Shortcut to Add Item)[cite: 11]
        function openAddCategoryModal() {
            document.getElementById('add_item_category').value = "";
            openAddModal();
        }

        // PAG-OPEN NG EDIT CATEGORY MODAL[cite: 11]
        function openEditCategoryModal(catName) {
            document.getElementById('old_cat_name').value = catName;
            document.getElementById('new_cat_name').value = catName;
            document.getElementById('editCategoryModal').style.display = 'block';
        }

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_desc').value = data.description;
            document.getElementById('edit_price').value = data.price;
            document.getElementById('edit_cat').value = data.category;
            document.getElementById('edit_img').value = data.image_url;
            document.getElementById('editModal').style.display = 'block';
        }

        function openViewModal(data) {
            document.getElementById('view_name').innerText = data.name.toUpperCase();
            document.getElementById('view_img').src = data.image_url;
            document.getElementById('view_price').innerText = '₱' + parseFloat(data.price).toFixed(2);
            document.getElementById('view_cat').innerText = data.category.toUpperCase();
            document.getElementById('view_desc').innerText = data.description;
            document.getElementById('viewModal').style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // FILTER FUNCTION GAMIT ANG EYE ICON[cite: 11]
        function filterByCategory(catName) {
            const dropdown = document.getElementById('category-dropdown-filter');
            dropdown.value = catName;
            // I-trigger ang existing filter logic sa MenuManagement.js
            if(window.renderMenu) {
                window.renderMenu(catName);
            }
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
    <script src="MenuManagement.js"></script>
</body>
</html>