// Palitan ang track_order.js mo nito
document.addEventListener('DOMContentLoaded', () => {
    const mainStatus = document.getElementById('main-status');
    const steps = document.querySelectorAll('.step');
    const orderId = new URLSearchParams(window.location.search).get('id') || "";

    function checkRiderProgress() {
        fetch(`check_order_status.php?id=${orderId}`)
            .then(res => res.json())
            .then(data => {
                // 1. Kapag Arrived na si Rider
                if (data.status === 'Arrived') {
                    mainStatus.innerText = "Kabayan, nasa labas na ang rider!";
                    mainStatus.style.color = "#F4A42B";
                    steps[1].classList.add('completed');
                }

                // 2. Kapag Delivered na (Order Finished)
                if (data.status === 'Delivered') {
                    window.location.href = "success.php?finished=true";
                }
            });
    }

    // Check status every 5 seconds
    if(orderId) setInterval(checkRiderProgress, 5000);
});