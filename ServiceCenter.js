document.addEventListener("DOMContentLoaded", () => {
    // --- 1. DATA (BLANGKO NA) ---
    // Wala nang dummy data dito para hindi pumatong sa system mo
    let inquiries = []; 

    // --- 2. RESERVATION FUNCTIONS ---
    window.filterReservations = (status) => {
        const rows = document.querySelectorAll('.res-row'); 
        
        rows.forEach(row => {
            if (status === 'All' || row.getAttribute('data-status') === status) {
                row.style.display = ''; // Ipakita
            } else {
                row.style.display = 'none'; // Itago
            }
        });
    };

    // --- 3. INBOX FUNCTIONS ---
    window.renderInquiries = () => {
        const list = document.getElementById("inquiry-list");
        const badge = document.getElementById("inbox-badge");
        list.innerHTML = "";
        badge.innerText = `${inquiries.length} new`;

        inquiries.forEach((inq, index) => {
            const row = document.createElement("tr");
            row.style.cursor = "pointer";
            row.onclick = () => selectInquiry(index);
            row.innerHTML = `
                <td>${inq.sender}</td>
                <td>${inq.subject}</td>
                <td>${inq.date}</td>
                <td>Pending</td>
            `;
            list.appendChild(row);
        });
    };

    let selectedInquiryIndex = null;
    window.selectInquiry = (index) => {
        selectedInquiryIndex = index;
        const inq = inquiries[index];
        document.getElementById("reply-subject").value = inq.subject;
        document.getElementById("customer-inquiry").value = inq.message;
        document.getElementById("admin-reply").value = "";
    };

    window.sendAdminReply = () => {
        const reply = document.getElementById("admin-reply").value;
        if (!reply || selectedInquiryIndex === null) {
            alert("Pili ka muna ng message at mag-type ng reply, Kabayan!");
            return;
        }
        alert("Reply sent to " + inquiries[selectedInquiryIndex].sender);
        inquiries.splice(selectedInquiryIndex, 1); 
        selectedInquiryIndex = null;
        document.getElementById("reply-subject").value = "";
        document.getElementById("customer-inquiry").value = "";
        document.getElementById("admin-reply").value = "";
        renderInquiries();
    };

    // Initial Launch
    renderInquiries();
});