document.addEventListener("DOMContentLoaded", () => {
    
    // --- 1. ADD STAFF / REGISTER LOGIC ---
    const addBtn = document.getElementById('add-staff-btn');
    
    addBtn?.addEventListener('click', () => {
        // Kunin ang lahat ng bagong fields
        const name = document.getElementById('staff-name').value;
        const role = document.getElementById('staff-role').value;
        const email = document.getElementById('staff-email').value;
        const contact = document.getElementById('staff-contact').value;
        const password = document.getElementById('staff-password').value;
        const confirmPassword = document.getElementById('staff-confirm-password').value;

        // 1. Validation: Bawal ang may kulang[cite: 9]
        if (!name || !role || !email || !contact || !password || !confirmPassword) {
            alert("Kabayan, pakikumpleto ang lahat ng impormasyon para sa staff account.");
            return;
        }

        // 2. Password Match Check (Gaya ng sa createacc.html)[cite: 9]
        if (password !== confirmPassword) {
            alert("Hindi magkatugma ang password. Paki-check ulit.");
            return;
        }

        // 3. Ihanda ang data para sa database[cite: 9]
        const formData = new FormData();
        formData.append('name', name);
        formData.append('role', role);
        formData.append('email', email);
        formData.append('contact', contact);
        formData.append('password', password);
        formData.append('action', 'register_staff');

        // 4. Ipadala sa PHP (Kailangan mo ng register_staff.php)[cite: 9]
        fetch('process_staff.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "success") {
                alert("Staff account registered successfully!");
                location.reload(); // Refresh para lumabas sa directory table
            } else {
                alert("Error: " + data);
            }
        })
        .catch(err => {
            console.error("Registration error:", err);
            alert("Nagkaroon ng problema sa pag-save. Subukan muli.");
        });
    });

    // --- 2. DELETE STAFF LOGIC ---
    // Ang index ay pinalitan na natin ng actual Database ID
    window.deleteStaff = (id) => {
        if (confirm("Sigurado ka bang tatanggalin ang staff account na ito? Hindi na ito mababawi.")) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', 'delete_staff');

            fetch('process_staff.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data.trim() === "success") {
                    location.reload(); 
                } else {
                    alert("Error in deletion: " + data);
                }
            });
        }
    };
});