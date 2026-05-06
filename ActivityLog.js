document.addEventListener("DOMContentLoaded", function() {
    
    // Example 1: Pag-click sa Notifications at Messages icon
    const notifIcon = document.getElementById("notif-icon");
    const msgIcon = document.getElementById("msg-icon");

    if (notifIcon) {
        notifIcon.addEventListener("click", function() {
            alert("Wala ka pang bagong notifications, Admin!");
        });
    }

    if (msgIcon) {
        msgIcon.addEventListener("click", function() {
            alert("Babasahin ang mga bagong messages...");
        });
    }

    // Example 2: Active Class toggler para sa Sidebar Links
    // Kung iisang file lang ginagamit mo sa lahat ng page at PHP include ang sidebar,
    // pwede itong gamitin para mag-highlight kung anong page ka currently.
    const navLinks = document.querySelectorAll(".nav-links li a");
    
    navLinks.forEach(link => {
        link.addEventListener("click", function() {
            // Tanggalin ang 'active' class sa lahat
            navLinks.forEach(l => l.classList.remove("active"));
            // Idagdag ang 'active' class sa kinlik na link
            this.classList.add("active");
        });
    });

});