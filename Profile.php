<?php
session_start();
include "db_conn.php";

// 1. Check kung naka-login ang user
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Kunin ang lahat ng data mula sa 'create_acc'
$sql = "SELECT * FROM create_acc WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
    
    // Data variables
    $fullname    = $user_data['full_name']; 
    $email       = $user_data['email'];
    $contact     = $user_data['contact_number'] ?? ""; 
    $google_id   = $user_data['google_id'] ?? "";   
    $facebook_id = $user_data['facebook_id'] ?? ""; 
    
    $profile_pic = $user_data['profile_pic'] ?? ""; 
    $home_addr   = $user_data['address_home'] ?? "";
    $work_addr   = $user_data['address_work'] ?? "";

    // DISCOUNT DATA
    $discount_type   = $user_data['discount_type'] ?? "None"; 
    $discount_id     = $user_data['discount_id_no'] ?? "";
    $discount_name   = $user_data['discount_id_name'] ?? ""; 
    $discount_id_front = $user_data['discount_id_pic'] ?? ""; 
    $discount_id_back  = $user_data['discount_id_back'] ?? ""; 

    // Check kung Social Login (Google or Facebook)
    $is_social_user = (!empty($google_id) || !empty($facebook_id));
} else {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Kainan ni Ate Kabayan</title>
    
    <link rel="stylesheet" href="Profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@300;400;600;700;800&family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
    
    <!-- Leaflet.js para sa Pinned Location (Foodpanda Style) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

    <style>
        /* CSS OVERRIDES */
        .avatar-big { overflow: visible !important; border: 2px solid #ddd; position: relative; cursor: pointer; display: flex; align-items: center; justify-content: center; background: #eee; }
        .avatar-big img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .camera-icon { bottom: 0 !important; right: 0 !important; width: 40px !important; height: 40px !important; border: 3px solid white !important; z-index: 10 !important; background: #F4A42B; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; position: absolute; }
        
        /* FIX PARA SA HEADER PROFILE PIC */
        .profile-img-small {
            width: 35px;
            height: 35px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #ddd;
        }
        .profile-img-small i { color: #f39c12; font-size: 18px; }
        .profile-img-small img { width: 100%; height: 100%; object-fit: cover; }

        .edit-actions { display: flex; flex-direction: column; gap: 5px; margin-top: 10px; margin-bottom: 20px; }
        .edit-photo { font-size: 0.85rem; color: #F4A42B; text-decoration: none; font-weight: 600; cursor: pointer; }
        .edit-photo:hover { text-decoration: underline; }
        .btn-remove-photo { font-size: 0.8rem; color: #e74c3c; text-decoration: none; font-weight: 600; }
        textarea.address-box { width: 100%; min-height: 80px; resize: none; padding: 12px; background: #fff; border: 1.5px solid #FFE0B2; border-radius: 10px; outline: none; display: block; }
        .status-connected { color: #2ecc71; font-weight: 800; }

        .discount-row { display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap; }
        .discount-row .form-group { flex: 1; min-width: 200px; }
        select.discount-select { width: 100%; padding: 12px; border-radius: 10px; border: 1.5px solid #FFE0B2; background: #fff; outline: none; font-family: inherit; font-size: 1rem; }
        
        /* MULTI-UPLOAD STYLES */
        .id-upload-container { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        .id-upload-box { padding: 15px; border: 2px dashed #FFE0B2; border-radius: 15px; text-align: center; background: #fffcf5; }
        .id-preview { width: 100%; height: 120px; object-fit: contain; margin-top: 10px; border-radius: 10px; display: none; border: 1px solid #ddd; background: #eee; }
        .id-preview.active { display: block; }
        .btn-verify-submit { width: 100%; padding: 15px; margin-top: 20px; border-radius: 12px; border: none; background: #2ecc71; color: white; font-weight: 800; cursor: pointer; display: none; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; }
        .custom-modal { background-color: #FEEBC8; padding: 30px; border-radius: 25px; width: 350px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.3); border: none; }
        .photo-option-btn { width: 100%; padding: 15px; margin-bottom: 10px; border-radius: 15px; border: none; background: white; font-family: 'Poppins', sans-serif; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; }
        .modal-btns { display: flex; justify-content: space-between; gap: 15px; margin-top: 10px; }
        .modal-btns button { flex: 1; padding: 12px; border-radius: 18px; border: none; font-weight: 800; font-size: 1.1rem; cursor: pointer; }

        .confirm-preview-box { width: 180px; height: 180px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; border: 4px solid #F4A42B; position: relative; background: #ccc; cursor: move; touch-action: none; }
        #confirm_image_preview { position: absolute; top: 0; left: 0; min-width: 100%; min-height: 100%; user-select: none; -webkit-user-drag: none; }
        .drag-hint { font-size: 0.75rem; color: #555; margin-bottom: 15px; display: block; }

        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { padding-right: 45px !important; }
        .password-toggle { position: absolute; right: 15px; color: #888; cursor: pointer; transition: 0.3s; z-index: 5; }
        .password-toggle:hover { color: #F4A42B; }
        .social-notice { background: #fdf2f2; border: 1px solid #f8d7da; color: #721c24; padding: 10px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }

        /* GPS PIN BUTTON STYLE (TULAD SA CHECKOUT) */
        .btn-pin-gps { 
            width: 100%; margin-top: 12px; padding: 12px; border: 2px solid #F4A42B; 
            border-radius: 12px; background: #fff; color: #F4A42B; font-weight: 800; 
            cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-pin-gps:hover { background: #F4A42B; color: #fff; }
        .btn-pin-gps.pinned { border-color: #2ecc71; color: #2ecc71; }

        /* ADDED: ADDRESS SUGGESTION BOX STYLE */
        .address-item { position: relative; }
        .address-suggestions {
            background: #FFF3E0;
            border: 2px solid #F4A42B;
            border-radius: 10px;
            margin-top: 5px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 9999;
            position: absolute;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .suggestion-item {
            padding: 12px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #000;
            font-weight: 600;
            border-bottom: 1px solid #FFE0B2;
            transition: 0.2s;
            text-align: left;
        }
        .suggestion-item:hover { background: #F4A42B; color: #fff; }

        /* HIDE CLASS PARA SA TOGGLE NG ADDRESS BOX */
        .hide-address-box { display: none !important; }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="homepage.php" class="logo" style="text-decoration: none;">
            <div class="logo-circle">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            </div>
            <h2>KAINAN NI ATE KABAYAN</h2>
        </a>

        <nav class="desktop-nav">
            <a href="homepage.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="orders.php">Orders</a>
            <a href="ratings.php">Reviews</a>
            <a href="About Us.php">About Us</a>
            <a href="Contactus.php">Contact</a>
        </nav>
        
        <div class="header-actions">
            <!-- Header Profile Section -->
            <a href="Profile.php" class="header-profile-desktop active-profile" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <div class="profile-img-small">
                    <?php if (!empty($profile_pic)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
                    <?php else: ?>
                        <i class="fa-solid fa-user"></i>
                    <?php endif; ?>
                </div>
                <span class="header-user-name" style="color: #fff; font-weight: bold;">HI, <?php echo strtoupper(explode(' ', trim($fullname))[0]); ?>!</span>
            </a>

            <a href="cart.php" class="cart-icon-btn">
                <i class="fa-solid fa-shopping-cart"></i>
                <span class="badge" id="cart-badge"></span>
            </a>
            
            <div class="hamburger-menu" id="hamburger-btn">
                <i class="fa-solid fa-bars"></i>
            </div>
        </div>
    </div>
</header>

<nav class="side-nav" id="side-nav">
    <div class="nav-profile">
        <div class="close-btn" id="close-btn"><i class="fa-solid fa-xmark"></i></div>
        <div class="profile-info">
            <div class="profile-img" style="background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <?php if (!empty($profile_pic)): ?>
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" style="width:100%; border-radius:50%; aspect-ratio: 1/1; object-fit: cover;">
                <?php else: ?>
                    <i class="fa-solid fa-user" style="font-size: 40px; color: #aaa;"></i>
                <?php endif; ?>
            </div>
            <div class="profile-text">
                <h3><?php echo htmlspecialchars($fullname); ?></h3>
                <a href="Profile.php">(View Profile)</a>
            </div>
        </div>
    </div>

    <div class="nav-links">
        <a href="homepage.php"><i class="fa-solid fa-house"></i> Home</a>
        <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
        <a href="orders.php"><i class="fa-solid fa-file-lines"></i> Orders</a>
        <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
        <a href="reviews.php"><i class="fa-solid fa-star"></i> Reviews</a>
        <a href="About Us.php"><i class="fa-solid fa-book-open"></i> About Us</a>
        <a href="Contactus.php"><i class="fa-solid fa-phone"></i> Contact Us</a>
        <!-- FIXED LOGOUT BUTTON (SIDE NAV) -->
        <a href="logout.php" class="logout" onclick="confirmLogout(event)"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</a>
    </div>
</nav>

<div class="overlay" id="overlay"></div>

<main class="profile-container">
    <h1 class="page-title">My Account</h1>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data" id="profileUpdateForm">
        <section class="info-card">
            <div class="card-header"><i class="fa-solid fa-user"></i> <h2>Personal Information</h2></div>
            <div class="personal-flex">
                <div class="inputs-side">
                    <div class="form-group"><label>Full Name</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($fullname); ?>"></div>
                    <div class="form-group"><label>Email Address</label><input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly style="background:#eee;"></div>
                    <div class="form-group"><label>Phone Number</label><input type="text" name="contact_number" value="<?php echo htmlspecialchars($contact); ?>"></div>
                </div>

                <div class="photo-side">
                    <div class="avatar-big" onclick="showPhotoOptions()" style="cursor:pointer;">
                        <?php if (!empty($profile_pic)): ?> <img id="profile_display" src="<?php echo htmlspecialchars($profile_pic); ?>"> <?php else: ?> <i class="fa-solid fa-user" id="user_icon" style="font-size: 4rem;"></i> <img id="profile_display" style="display:none;"> <?php endif; ?>
                        <div class="camera-icon"><i class="fa-solid fa-camera"></i></div>
                    </div>
                    <input type="file" name="profile_pic" id="profile_pic_input" style="display:none;" accept="image/*" onchange="previewImage(this)">
                    <input type="file" name="profile_pic_camera" id="profile_pic_camera" style="display:none;" accept="image/*" capture="user" onchange="previewImage(this)">
                    
                    <div class="edit-actions">
                        <span class="edit-photo" onclick="showPhotoOptions()">Change Photo</span>
                        <?php if (!empty($profile_pic)): ?> 
                            <a href="update_profile.php?remove_photo=1" class="btn-remove-photo" onclick="confirmRemovePhoto(event, this.href)">Remove Photo</a> 
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="btn_save_profile" class="btn-save">Save Changes</button>
                </div>
            </div>
        </section>

        <section class="info-card">
            <div class="card-header"><i class="fa-solid fa-house"></i> <h2>Saved Addresses</h2></div>
            
            <!-- Pin Selector -->
            <div class="address-selector" style="display: flex; gap: 10px; margin-bottom: 15px;">
                <button type="button" id="select-home" class="btn-save" style="flex: 1; background: #F4A42B; font-size: 0.8rem; padding: 10px;">Pin to Home</button>
                <button type="button" id="select-work" class="btn-save" style="flex: 1; background: #ccc; font-size: 0.8rem; padding: 10px; color: #333;">Pin to Work</button>
            </div>

            <!-- Map Container -->
            <div id="map" style="height: 300px; border-radius: 15px; margin-bottom: 15px; border: 2px solid #FFE0B2; z-index: 1;"></div>
            
            <!-- GPS Pin Button (MANUAL TRIGGER) -->
            <button type="button" class="btn-pin-gps" id="btn-pin-location">
                <i class="fa-solid fa-location-crosshairs"></i> Pin My Current Location
            </button>

            <p style="font-size: 0.75rem; color: #888; margin-top: 15px; display: flex; align-items: center; gap: 5px;">
                <i class="fa-solid fa-circle-info"></i> Piliin ang Home o Work, i-click ang GPS button o i-drag ang pin para mag-auto fill.
            </p>

            <div class="address-item" id="container_home_addr">
                <strong>Home Address</strong>
                <textarea name="address_home" id="home_addr" class="address-box"><?php echo htmlspecialchars($home_addr); ?></textarea>
                <div id="home_suggestions" class="address-suggestions"></div>
            </div>
            
            <div class="address-item hide-address-box" id="container_work_addr" style="margin-top:15px;">
                <strong>Work Address</strong>
                <textarea name="address_work" id="work_addr" class="address-box"><?php echo htmlspecialchars($work_addr); ?></textarea>
                <div id="work_suggestions" class="address-suggestions"></div>
            </div>
            
            <!-- Save Button para sa Address section -->
            <button type="submit" name="btn_save_profile" class="btn-save" style="margin-top: 20px;">Save Address</button>
        </section>

        <section class="info-card">
            <div class="card-header"><i class="fa-solid fa-shield-halved"></i> <h2>Security & Password</h2></div>
            
            <?php if ($is_social_user): ?>
                <div class="social-notice">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Your account is linked via social login. Password management is handled by your provider.</span>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Current Password</label>
                <div class="password-wrapper">
                    <input type="password" name="current_password" id="curr_pass" placeholder="••••••••" <?php echo ($is_social_user) ? 'disabled style="background:#f5f5f5; color:#aaa;"' : ''; ?>>
                    <i class="fa-solid fa-eye password-toggle" onclick="togglePassVisibility('curr_pass', this)"></i>
                </div>
            </div>
            <div class="discount-row">
                <div class="form-group">
                    <label>New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_password" id="new_pass" placeholder="Min. 8 characters" <?php echo ($is_social_user) ? 'disabled style="background:#f5f5f5; color:#aaa;"' : ''; ?>>
                        <i class="fa-solid fa-eye password-toggle" onclick="togglePassVisibility('new_pass', this)"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_new_password" id="conf_pass" placeholder="Confirm new password" <?php echo ($is_social_user) ? 'disabled style="background:#f5f5f5; color:#aaa;"' : ''; ?>>
                        <i class="fa-solid fa-eye password-toggle" onclick="togglePassVisibility('conf_pass', this)"></i>
                    </div>
                </div>
            </div>
            <p style="font-size: 0.75rem; color: #888; margin-bottom: 15px;">Iwanang blanko kung ayaw palitan ang password.</p>
            
            <button type="submit" name="btn_change_password" id="btn_change_pass" class="btn-save" <?php echo ($is_social_user) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>Confirm Password Change</button>
        </section>

        <section class="info-card">
            <div class="card-header"><i class="fa-solid fa-id-card"></i> <h2>Discount Verification</h2></div>
            <div class="discount-row">
                <div class="form-group">
                    <label>ID Type</label>
                    <select name="discount_type" class="discount-select" id="discount_type" onchange="toggleDiscountFields()">
                        <option value="None" <?php if($discount_type=="None") echo "selected"; ?>>None (Regular)</option>
                        <option value="Senior" <?php if($discount_type=="Senior") echo "selected"; ?>>Senior Citizen</option>
                        <option value="PWD" <?php if($discount_type=="PWD") echo "selected"; ?>>PWD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ID Number (Numbers only)</label>
                    <input type="text" 
                           name="discount_id_no" 
                           id="discount_id_no" 
                           value="<?php echo htmlspecialchars($discount_id); ?>" 
                           placeholder="0000-0000-0000" 
                           maxlength="19" 
                           inputmode="numeric"
                           <?php if($discount_type=="None") echo "disabled"; ?> 
                           oninput="formatIDNumber(this); checkVerificationInputs()">
                </div>
            </div>

            <div class="form-group" id="name_on_id_group" style="margin-top: 15px; <?php echo ($discount_type == 'None') ? 'display:none;' : ''; ?>">
                <label>Name on ID (Dapat tugma sa ID)</label>
                <input type="text" 
                       name="discount_id_name" 
                       id="discount_id_name" 
                       value="<?php echo htmlspecialchars($discount_name); ?>" 
                       placeholder="Enter full name as shown on ID" 
                       oninput="checkVerificationInputs()">
            </div>

            <div id="id_photo_section" style="<?php echo ($discount_type == 'None') ? 'display:none;' : ''; ?>">
                <p style="font-weight: 600; font-size: 0.9rem; margin-top: 15px; color: #e67e22;">Upload Front and Back Photo of your ID</p>
                <div class="id-upload-container">
                    <div class="id-upload-box">
                        <label style="font-size: 0.8rem; display: block; margin-bottom: 5px;">FRONT SIDE</label>
                        <input type="file" name="discount_id_front" id="id_front_input" accept="image/*" onchange="previewID(this, 'front_preview')" style="width: 100%;">
                        <img id="front_preview" src="<?php echo !empty($discount_id_front) ? htmlspecialchars($discount_id_front) : ''; ?>" class="id-preview <?php echo !empty($discount_id_front) ? 'active' : ''; ?>">
                    </div>
                    <div class="id-upload-box">
                        <label style="font-size: 0.8rem; display: block; margin-bottom: 5px;">BACK SIDE</label>
                        <input type="file" name="discount_id_back" id="id_back_input" accept="image/*" onchange="previewID(this, 'back_preview')" style="width: 100%;">
                        <img id="back_preview" src="<?php echo !empty($discount_id_back) ? htmlspecialchars($discount_id_back) : ''; ?>" class="id-preview <?php echo !empty($discount_id_back) ? 'active' : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" name="btn_submit_verification" id="btn_verify" class="btn-verify-submit">Submit for Admin Approval</button>
            </div>
        </section>
        
        <input type="hidden" name="link_google" id="link_google_id">
        <input type="hidden" name="link_fb" id="link_fb_id">
        <input type="hidden" name="fb_name" id="fb_name">
        <input type="hidden" name="fb_email" id="fb_email">
        <input type="hidden" name="social_pic_url" id="social_pic_url">
    </form>

    <div class="security-grid">
        <section class="info-card">
            <div class="card-header"><h2>Social Connections</h2></div>
            <div class="social-row"><span>Google</span><?php if (!empty($google_id)): ?><span class="status-connected">Connected</span><?php else: ?><button class="btn-connect" onclick="googleConnect()">Connect</button><?php endif; ?></div>
            <div class="social-row"><span>Facebook</span><?php if (!empty($facebook_id)): ?><span class="status-connected">Connected</span><?php else: ?><button class="btn-connect" onclick="fbConnect()">Connect</button><?php endif; ?></div>
        </section>
        <section class="info-card">
            <div class="card-header"><h2>Account Actions</h2></div>
            <div class="delete-logout-btns">
                <!-- FIXED LOGOUT BUTTON (ACCOUNT ACTIONS) -->
                <button type="button" class="btn-outline-logout" onclick="confirmLogout(event)">Log Out</button>
                <button type="button" class="btn-red-delete" onclick="showDeleteModal()">Delete Account</button>
            </div>
        </section>
    </div>
</main>

<div class="modal-overlay" id="photoModalOverlay">
    <div class="custom-modal">
        <p>Change Profile Photo</p>
        <button class="photo-option-btn" onclick="openGallery()"><i class="fa-solid fa-image"></i> Gallery</button>
        <button class="photo-option-btn" onclick="openCamera()"><i class="fa-solid fa-camera"></i> Take a Photo</button>
        <?php if (!empty($facebook_id)): ?><button class="photo-option-btn" onclick="syncFBPhoto()"><i class="fa-brands fa-facebook" style="color:#1877F2;"></i> Sync from Facebook</button><?php endif; ?>
        <button class="photo-option-btn" style="color:red; margin-top:10px;" onclick="closePhotoOptions()">Cancel</button>
    </div>
</div>

<div class="modal-overlay" id="confirmPhotoOverlay">
    <div class="custom-modal">
        <p>Adjust Profile Picture</p>
        <small class="drag-hint">I-drag ang picture para i-center</small>
        <div class="confirm-preview-box" id="drag_area">
            <img id="confirm_image_preview" src="" style="top:0; left:0;">
        </div>
        <div class="modal-btns">
            <button class="btn-modal-cancel" style="background:white; color:black;" onclick="cancelPhotoSelection()">Cancel</button>
            <button class="btn-modal-yes" style="background:#2ecc71; color:white;" onclick="confirmAndUpload()">Confirm</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModalOverlay">
    <div class="custom-modal">
        <p>Are you sure you want to <span style="color:red;">DELETE</span> your account permanently?</p>
        <div class="modal-btns">
            <button class="btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn-modal-yes" style="background:red; color:white;" onclick="proceedDelete()">Yes</button>
        </div>
    </div>
</div>

<!-- ADDED: CUSTOM ALERT/CONFIRM MODAL -->
<div class="modal-overlay" id="customAlertOverlay">
    <div class="custom-modal">
        <p id="customAlertMessage" style="font-weight: 600; margin-bottom: 20px; color: #333;"></p>
        <div class="modal-btns" id="customAlertBtns"></div>
    </div>
</div>

<script>
    // --- CUSTOM ALERT/CONFIRM FUNCTION ---
    function showCustomAlert(message, isConfirm = false, onConfirm = null) {
        const overlay = document.getElementById('customAlertOverlay');
        const msgEl = document.getElementById('customAlertMessage');
        const btnsEl = document.getElementById('customAlertBtns');

        msgEl.innerText = message;
        btnsEl.innerHTML = '';

        if (isConfirm) {
            const btnCancel = document.createElement('button');
            btnCancel.className = 'btn-modal-cancel';
            btnCancel.style.cssText = 'background:white; color:black;';
            btnCancel.innerText = 'Cancel';
            btnCancel.onclick = () => { overlay.style.display = 'none'; };

            const btnYes = document.createElement('button');
            btnYes.className = 'btn-modal-yes';
            btnYes.style.cssText = 'background:#2ecc71; color:white;';
            btnYes.innerText = 'Yes';
            btnYes.onclick = () => { overlay.style.display = 'none'; if(onConfirm) onConfirm(); };

            btnsEl.appendChild(btnCancel);
            btnsEl.appendChild(btnYes);
        } else {
            const btnOk = document.createElement('button');
            btnOk.className = 'btn-modal-yes';
            btnOk.style.cssText = 'background:#F4A42B; color:white; width: 100%;';
            btnOk.innerText = 'OK';
            btnOk.onclick = () => { overlay.style.display = 'none'; };
            btnsEl.appendChild(btnOk);
        }
        overlay.style.display = 'flex';
    }

    function confirmRemovePhoto(event, url) {
        event.preventDefault();
        showCustomAlert('Sigurado ka bang gusto mong burahin ang profile picture?', true, () => {
            window.location.href = url;
        });
    }

    // --- MAP LOGIC ---
    var map; var marker;
    var currentMode = 'home'; // Track active box

    function initMap() {
        if (map) return; 
        var container = L.DomUtil.get('map');
        if (container) container._leaflet_id = null;
        
        // Inalis ang maxBounds para makita pa rin ang labas ng Metro Manila[cite: 17]
        map = L.map('map').setView([14.5547, 121.0244], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        marker = L.marker([14.5547, 121.0244], { draggable: true }).addTo(map);

        marker.on('dragend', function() {
            var pos = marker.getLatLng();
            reverseGeocode(pos.lat, pos.lng);
        });
    }

    // UPDATE PIN KAPAG MAY TINYPE[cite: 17]
    let searchTimer;
    function updatePinFromText(addressText) {
        clearTimeout(searchTimer);
        const suggBox = (currentMode === 'home') ? document.getElementById('home_suggestions') : document.getElementById('work_suggestions');
        const activeField = (currentMode === 'home') ? document.getElementById('home_addr') : document.getElementById('work_addr');
        
        if (!addressText || addressText.trim().length < 4) { 
            if(suggBox) suggBox.style.display = 'none'; 
            return; 
        }

        searchTimer = setTimeout(() => {
            // Inalis ang limits at bounds para lumabas lahat ng lokasyon sa Pinas[cite: 17]
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(addressText)}&countrycodes=ph&addressdetails=1&email=atekabayan@example.com`)
                .then(r => r.json()).then(data => {
                    if (data.length > 0 && suggBox) {
                        suggBox.innerHTML = ''; 
                        suggBox.style.display = 'block';
                        data.forEach(item => {
                            let div = document.createElement('div');
                            div.className = 'suggestion-item'; 
                            div.innerText = item.display_name;
                            div.onclick = () => {
                                activeField.value = item.display_name;
                                if(marker && map) {
                                    marker.setLatLng([item.lat, item.lon]); 
                                    map.setView([item.lat, item.lon], 17);
                                }
                                suggBox.style.display = 'none';

                                // --- METRO MANILA CHECKER TRIGGER ---[cite: 17]
                                const ncrRegex = /metro manila|ncr|national capital region|makati|manila|taguig|quezon city|pasig|mandaluyong|pasay|san juan|pateros|valenzuela|caloocan|malabon|navotas|marikina|muntinlupa|las piñas|parañaque/i;
                                if (!ncrRegex.test(item.display_name)) {
                                    showCustomAlert("Pasensya na, Kabayan! Hanggang Metro Manila lang po ang sakop ng aming serbisyo.");
                                    if(marker && map) {
                                        marker.setLatLng([14.5547, 121.0244]);
                                        map.setView([14.5547, 121.0244], 15);
                                    }
                                    activeField.value = "";
                                }
                            };
                            suggBox.appendChild(div);
                        });
                    } else {
                        if(suggBox) suggBox.style.display = 'none';
                    }
                }).catch(() => { console.log("Nominatim is busy..."); });
        }, 1000); 
    }

    // FUNCTION PARA HANAPIN ANG LOKASYON KUNG MAY NAKA-SAVE NA ADDRESS
    function updatePinFromSavedAddress(addressText) {
        if (!addressText || addressText.trim() === "") return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(addressText)}&email=atekabayan@example.com`)
            .then(r => r.json()).then(data => {
                if (data.length > 0 && marker && map) {
                    marker.setLatLng([data[0].lat, data[0].lon]); 
                    map.setView([data[0].lat, data[0].lon], 17);
                }
            });
    }

    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&email=atekabayan@example.com`)
            .then(r => r.json()).then(data => {
                const addrStr = (data.display_name || "").toLowerCase();
                const ncrRegex = /metro manila|ncr|national capital region|makati|manila|taguig|quezon city|pasig|mandaluyong|pasay|san juan|pateros|valenzuela|caloocan|malabon|navotas|marikina|muntinlupa|las piñas|parañaque/i;
                
                // --- METRO MANILA DRAG CHECKER TRIGGER ---[cite: 17]
                if (!ncrRegex.test(addrStr)) {
                    showCustomAlert("Pasensya na, Kabayan! Hanggang Metro Manila lang po ang sakop ng aming serbisyo.");
                    marker.setLatLng([14.5547, 121.0244]);
                    map.setView([14.5547, 121.0244], 15);
                    return;
                }

                const homeBtn = document.getElementById('select-home');
                const activeField = (homeBtn.style.background.includes('rgb(244')) ? document.getElementById('home_addr') : document.getElementById('work_addr');
                if (data.display_name && activeField) activeField.value = data.display_name;
            });
    }

    window.addEventListener('load', () => {
        initMap();
        
        const homeBtnMap = document.getElementById('select-home');
        const workBtnMap = document.getElementById('select-work');
        const contHome = document.getElementById('container_home_addr');
        const contWork = document.getElementById('container_work_addr');
        const hAddr = document.getElementById('home_addr');
        const wAddr = document.getElementById('work_addr');

        // Basa agad ng location pag bukas ng page base sa Home
        if (hAddr.value.trim() !== "") {
            updatePinFromSavedAddress(hAddr.value);
        }

        // --- BUTTON TOGGLES: Pindot = Palit Box & Palit Pin Location ---
        if(homeBtnMap && workBtnMap) {
            homeBtnMap.addEventListener('click', () => {
                currentMode = 'home';
                homeBtnMap.style.background = '#F4A42B'; homeBtnMap.style.color = '#fff';
                workBtnMap.style.background = '#ccc'; workBtnMap.style.color = '#333';
                
                contHome.classList.remove('hide-address-box');
                contWork.classList.add('hide-address-box');

                if (hAddr.value.trim() !== "") updatePinFromSavedAddress(hAddr.value);
            });
            
            workBtnMap.addEventListener('click', () => {
                currentMode = 'work';
                workBtnMap.style.background = '#F4A42B'; workBtnMap.style.color = '#fff';
                homeBtnMap.style.background = '#ccc'; homeBtnMap.style.color = '#333';
                
                contWork.classList.remove('hide-address-box');
                contHome.classList.add('hide-address-box');

                if (wAddr.value.trim() !== "") updatePinFromSavedAddress(wAddr.value);
            });
        }

        const gpsBtn = document.getElementById('btn-pin-location');
        if(gpsBtn) {
            gpsBtn.addEventListener('click', () => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        
                        // Kung ang GPS ng user ay nasa labas ng Metro Manila[cite: 17]
                        if (lat < 14.33 || lat > 14.77 || lng < 120.89 || lng > 121.14) {
                            showCustomAlert("Nasa labas ka ng Metro Manila, Kabayan! Hindi namin ma-pin ang current location mo.");
                            return;
                        }

                        if(!map) initMap();
                        map.setView([lat, lng], 17);
                        marker.setLatLng([lat, lng]);
                        reverseGeocode(lat, lng);
                    });
                }
            });
        }

        // AJAX Form Submission with Final Validation Check[cite: 17]
        const profileForm = document.getElementById('profileUpdateForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault(); 
                
                // Final Check bago mag-save sa Database
                const ncrRegex = /metro manila|ncr|national capital region|makati|manila|taguig|quezon city|pasig|mandaluyong|pasay|san juan|pateros|valenzuela|caloocan|malabon|navotas|marikina|muntinlupa|las piñas|parañaque/i;
                const hAddrVal = hAddr ? hAddr.value : '';
                const wAddrVal = wAddr ? wAddr.value : '';
                
                if (hAddrVal.trim() !== "" && !ncrRegex.test(hAddrVal)) {
                    showCustomAlert('Kabayan, ang Home Address ay dapat nasa loob ng Metro Manila.');
                    return;
                }
                if (wAddrVal.trim() !== "" && !ncrRegex.test(wAddrVal)) {
                    showCustomAlert('Kabayan, ang Work Address ay dapat nasa loob ng Metro Manila.');
                    return;
                }

                let formData = new FormData(this);
                const submitter = e.submitter;
                if(submitter) formData.append(submitter.name, '1');

                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    showCustomAlert('Changes saved successfully, Kabayan!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showCustomAlert('Nagkaroon ng error sa pag-save. Subukan muli.');
                });
            });
        }

        if(hAddr) hAddr.addEventListener('input', (e) => updatePinFromText(e.target.value));
        if(wAddr) wAddr.addEventListener('input', (e) => updatePinFromText(e.target.value));

        document.addEventListener('click', (e) => {
            if (!e.target.classList.contains('address-box')) {
                const hSugg = document.getElementById('home_suggestions');
                const wSugg = document.getElementById('work_suggestions');
                if(hSugg) hSugg.style.display = 'none';
                if(wSugg) wSugg.style.display = 'none';
            }
        });
    });

    // --- BURGER MENU LOGIC ---
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sideNav = document.getElementById('side-nav');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('close-btn');

    hamburgerBtn.addEventListener('click', () => { 
        sideNav.classList.add('active'); 
        overlay.classList.add('active'); 
    });
    closeBtn.addEventListener('click', () => { 
        sideNav.classList.remove('active'); 
        overlay.classList.remove('active'); 
    });
    overlay.addEventListener('click', () => { 
        sideNav.classList.remove('active'); 
        overlay.classList.remove('active'); 
    });

    // --- CART BADGE COUNT ---
    fetch('get_cart_count.php').then(r=>r.json()).then(d=>{
        const b=document.getElementById('cart-badge');
        if(b){b.innerText=d.total_qty||0;b.style.display=(d.total_qty>0)?'flex':'none';}
    }).catch(()=>{});

    // --- LOGOUT CONFIRMATION LOGIC ---
    function confirmLogout(event) {
        event.preventDefault(); 
        showCustomAlert("Kabayan, sigurado ka bang gusto mo nang mag-logout?", true, () => {
            window.location.href = 'logout.php'; 
        });
    }

    // (Existing Helper Functions)
    function toggleDiscountFields() {
        const type = document.getElementById('discount_type').value;
        const idInput = document.getElementById('discount_id_no');
        const nameInput = document.getElementById('discount_id_name');
        const nameGroup = document.getElementById('name_on_id_group');
        const photoSection = document.getElementById('id_photo_section');
        
        if (type === 'None') { 
            idInput.disabled = true; 
            idInput.value = ''; 
            nameInput.value = ''; 
            nameGroup.style.display = 'none'; 
            photoSection.style.display = 'none'; 
        } else { 
            idInput.disabled = false; 
            nameGroup.style.display = 'block'; 
            photoSection.style.display = 'block'; 
        }
        checkVerificationInputs();
    }

    function formatIDNumber(input) { 
        let value = input.value.replace(/\D/g, ''); 
        let formattedValue = ""; 
        for (let i = 0; i < value.length; i++) { 
            if (i > 0 && i % 4 === 0) { formattedValue += "-"; } 
            formattedValue += value[i]; 
        } 
        input.value = formattedValue; 
    }

    function previewID(input, previewId) { 
        if (input.files && input.files[0]) { 
            var reader = new FileReader(); 
            reader.onload = function(e) { 
                const img = document.getElementById(previewId); 
                img.src = e.target.result; 
                img.classList.add('active'); 
                checkVerificationInputs(); 
            };
            reader.readAsDataURL(input.files[0]); 
        } 
    }

    function checkVerificationInputs() { 
        const type = document.getElementById('discount_type').value; 
        const idNo = document.getElementById('discount_id_no').value.trim(); 
        const idName = document.getElementById('discount_id_name').value.trim(); 
        const frontPreview = document.getElementById('front_preview');
        const backPreview = document.getElementById('back_preview');
        
        const frontPic = document.getElementById('id_front_input').files.length > 0 || (frontPreview.src !== "" && !frontPreview.src.includes('Profile.php') && !frontPreview.src.endsWith('/')); 
        const backPic = document.getElementById('id_back_input').files.length > 0 || (backPreview.src !== "" && !backPreview.src.includes('Profile.php') && !backPreview.src.endsWith('/')); 
        
        const btnVerify = document.getElementById('btn_verify'); 
        
        if (type !== 'None' && idNo.length >= 5 && idName.length > 0 && frontPic && backPic) { 
            btnVerify.style.display = 'block'; 
        } else { 
            btnVerify.style.display = 'none'; 
        } 
    }

    function showPhotoOptions() { 
        document.getElementById('photoModalOverlay').style.display = 'flex'; 
    }

    function closePhotoOptions() { 
        document.getElementById('photoModalOverlay').style.display = 'none'; 
    }

    function openGallery() { 
        closePhotoOptions(); 
        document.getElementById('profile_pic_input').click(); 
    }

    function openCamera() { 
        closePhotoOptions(); 
        document.getElementById('profile_pic_camera').click(); 
    }

    function previewImage(input) { 
        if (input.files && input.files[0]) { 
            var reader = new FileReader(); 
            reader.onload = function(e) { 
                document.getElementById('confirm_image_preview').src = e.target.result; 
                document.getElementById('confirmPhotoOverlay').style.display = 'flex'; 
            };
            reader.readAsDataURL(input.files[0]); 
        } 
    }

    function cancelPhotoSelection() { 
        document.getElementById('profile_pic_input').value = ""; 
        document.getElementById('confirmPhotoOverlay').style.display = 'none'; 
    }

    function confirmAndUpload() { 
        document.getElementById('profileUpdateForm').submit(); 
    }

    function googleConnect() { 
        google.accounts.id.prompt(); 
    }

    window.fbAsyncInit = function() { 
        FB.init({ appId: '3396938717141397', cookie: true, xfbml: true, version: 'v18.0' }); 
    };

    function fbConnect() { 
        FB.login(function(response) { 
            if (response.status === 'connected') { 
                document.getElementById('link_fb_id').value = response.authResponse.userID; 
                document.getElementById('profileUpdateForm').submit(); 
            } 
        }, {scope: 'public_profile,email'}); 
    }

    function syncFBPhoto() { 
        closePhotoOptions(); 
        const fbId = "<?php echo $facebook_id; ?>"; 
        if (fbId) { 
            document.getElementById('social_pic_url').value = 'https://graph.facebook.com/' + fbId + '/picture?type=large'; 
            document.getElementById('profileUpdateForm').submit(); 
        } 
    }

    function showDeleteModal() { 
        document.getElementById('deleteModalOverlay').style.display = 'flex'; 
    }

    function closeDeleteModal() { 
        document.getElementById('deleteModalOverlay').style.display = 'none'; 
    }

    function proceedDelete() { 
        window.location.href = 'delete_account.php'; 
    }

    function togglePassVisibility(inputId, icon) { 
        const input = document.getElementById(inputId); 
        if (input.type === "password") { 
            input.type = "text"; 
            icon.classList.replace("fa-eye", "fa-eye-slash"); 
        } else { 
            input.type = "password"; 
            icon.classList.replace("fa-eye-slash", "fa-eye"); 
        } 
    }

    window.onclick = function(e) { 
        if (e.target.className === 'modal-overlay') { 
            closePhotoOptions(); 
            closeDeleteModal(); 
            document.getElementById('confirmPhotoOverlay').style.display = 'none'; 
            const customAlert = document.getElementById('customAlertOverlay');
            if(customAlert) customAlert.style.display = 'none';
        } 
    };
</script>
<script src="Profile.js"></script>
</body>
</html>