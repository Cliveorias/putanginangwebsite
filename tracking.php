<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? null;

// Kunin ang data ng order kasama ang customer GPS
$sql = "SELECT * FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$res = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($res);

if (!$order) {
    header("Location: homepage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Order - Ate Kabayan</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="tracking.css">

    <style>
        #map { 
            width: 100%; 
            height: 400px; 
            border-radius: 15px;
            border: 2px solid #000;
            z-index: 1;
        }
        /* Itatago natin yung white box na may "Turn Left/Right" instructions para malinis */
        .leaflet-routing-container { display: none !important; }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo">
            <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772298452/logo_ate_kabayan_jtfqeg.jpg" alt="Logo">
            <h2>KAINAN NI ATE KABAYAN</h2>
        </div>
    </div>
</header>

<main class="tracking-content">
    <div class="upper-section">
        <div class="map-box" style="height: 400px;">
            <div id="map"></div>
        </div>

        <div class="info-side">
            <div class="status-header">
                <div class="text-info">
                    <h1 id="status-text">On the Way</h1>
                    <p class="eta">ETA: <span id="eta-text"><?php echo $order['estimated_time'] ?? 'Calculating...'; ?></span></p>
                </div>
                <div class="otw-tag">OTW</div>
            </div>

            <div class="rider-profile">
                <img src="https://res.cloudinary.com/dn38jxbeh/image/upload/v1772299469/Motor_Driver_Homepage_ieqima.png" alt="Rider">
                <div class="rider-meta">
                    <h3>Kuya Rider</h3>
                    <p>Yamaha NMAX - ABC 1234</p>
                </div>
                <a href="tel:09123456789" class="call-btn"><i class="fa-solid fa-phone"></i></a>
            </div>
        </div>
    </div>

    <button class="btn-received" id="btnReceived">ORDER RECEIVED</button>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
    const orderId = "<?php echo $order['id']; ?>";
    
    // Kunin ang pinned location mo (Customer) galing PHP
    const custLat = parseFloat("<?php echo $order['cust_lat']; ?>");
    const custLng = parseFloat("<?php echo $order['cust_lng']; ?>");

    let map, riderMarker, routingControl;

    // A. INITIALIZE MAP
    function initMap() {
        // I-set ang view malapit sa bahay ni customer
        map = L.map('map').setView([custLat, custLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Icon para sa Bahay (Customer)
        const houseIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/1239/1239525.png',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        L.marker([custLat, custLng], {icon: houseIcon}).addTo(map).bindPopup("Your House").openPopup();

        // Icon para sa Motor (Rider)
        const motorIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/3198/3198336.png',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });

        // I-setup ang Routing Control (Ang gagawa ng blue line sa kalsada)
        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(custLat, custLng), // Temporary start (Rider)
                L.latLng(custLat, custLng)  // End (Customer)
            ],
            createMarker: function(i, wp) {
                if (i === 0) {
                    riderMarker = L.marker(wp.latLng, {icon: motorIcon});
                    return riderMarker;
                }
                return null; // Ayaw nating lagyan ng extra marker yung bahay
            },
            routeWhileDragging: false,
            addWaypoints: false,
            lineOptions: {
                styles: [{ color: '#4285F4', opacity: 0.8, weight: 6 }]
            }
        }).addTo(map);
    }

    // B. POLLING FUNCTION (Check bawat 3 segundo)
    function trackRider() {
        fetch(`check_order_status.php?id=${orderId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('eta-text').innerText = data.estimated_time;
            
            // I-update ang Line at Marker position ni Rider
            if (data.rider_lat && data.rider_lng) {
                const rLat = parseFloat(data.rider_lat);
                const rLng = parseFloat(data.rider_lng);

                // Baguhin ang waypoints: Rider Location -> Customer House
                routingControl.setWaypoints([
                    L.latLng(rLat, rLng),
                    L.latLng(custLat, custLng)
                ]);

                // I-center ang mapa para makita yung dalawang points
                // map.panTo([rLat, rLng]); // O kaya fitBounds kung gusto mong kita pareho
            }

            if(data.status === 'Delivered') {
                window.location.href = "homepage.php";
            }
        })
        .catch(err => console.log("Waiting for data..."));
    }

    window.onload = function() {
        if(isNaN(custLat) || isNaN(custLng)) {
            alert("Warning: No pinned location found for this order.");
        }
        initMap();
        setInterval(trackRider, 3000); 
    };

    document.getElementById('btnReceived').addEventListener('click', function() {
        if(confirm("Confirm delivery?")) {
            const fd = new FormData();
            fd.append('id', orderId);
            fd.append('status', 'Delivered');
            fetch('update_rider_status.php', { method: 'POST', body: fd })
            .then(() => window.location.href = "homepage.php");
        }
    });
</script>
</body>
</html>