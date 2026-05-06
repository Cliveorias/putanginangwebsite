document.addEventListener("DOMContentLoaded", () => {
    
    // 1. RENDER OPERATING HOURS
    const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    const hoursBody = document.getElementById("hours-body");

    days.forEach(day => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td style="font-weight: 800; font-size: 0.85rem;">${day}</td>
            <td><input type="text" class="time-input" value="11:00 AM"></td>
            <td><input type="text" class="time-input" value="11:00 PM"></td>
        `;
        hoursBody.appendChild(row);
    });

    // 2. SAVE STORE INFO FUNCTION
    window.saveStoreInfo = () => {
        const btn = document.querySelector(".btn-save-all");
        btn.innerText = "Saving...";
        btn.style.opacity = "0.7";
        
        setTimeout(() => {
            alert("Store Information Updated Successfully, Kabayan!");
            btn.innerText = "Save All Store Info";
            btn.style.opacity = "1";
        }, 1500);
    };

    // 3. APPROVAL LOGIC
    window.approveApp = () => {
        const id = document.getElementById("manual-id").value;
        if(!id) return alert("Paki-enter muna ang ID number!");
        
        if(confirm(`Sigurado ka bang i-aapprove ang Application ID: ${id}?`)) {
            alert("Application Approved! Discount will now be active for this customer.");
        }
    };

    window.declineApp = () => {
        alert("Application Declined. Notification sent to applicant.");
    };
});