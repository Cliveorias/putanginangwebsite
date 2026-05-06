document.addEventListener("DOMContentLoaded", () => {
    // Data para sa mga graphs (Simulated)
    const statsData = {
        orders: [10, 40, 20, 60, 30, 80, 50],
        revenue: [20, 30, 60, 40, 80, 50, 90],
        customers: [5, 15, 10, 30, 20, 45, 40],
        value: [30, 20, 50, 30, 70, 40, 60]
    };

    // Function para mag-draw ng Area Chart gamit ang SVG
    function drawAreaChart(containerId, data, color) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const width = 300;
        const height = 100;
        const maxData = Math.max(...data);
        
        // Calculate points
        const points = data.map((d, i) => {
            const x = (i / (data.length - 1)) * width;
            const y = height - (d / maxData) * height;
            return `${x},${y}`;
        }).join(" ");

        const pathData = `M0,${height} ${points} L${width},${height} Z`;

        container.innerHTML = `
            <svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none" style="width:100%; height:100%;">
                <defs>
                    <linearGradient id="grad-${containerId}" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:${color};stop-opacity:0.8" />
                        <stop offset="100%" style="stop-color:${color};stop-opacity:0" />
                    </linearGradient>
                </defs>
                <path d="${pathData}" fill="url(#grad-${containerId})" />
                <polyline points="${points}" fill="none" stroke="${color}" stroke-width="3" />
            </svg>
        `;
    }

    // Draw all graphs
    drawAreaChart('graph-orders', statsData.orders, '#FF8A65');
    drawAreaChart('graph-revenue', statsData.revenue, '#81C784');
    drawAreaChart('graph-customers', statsData.customers, '#263238');
    drawAreaChart('graph-value', statsData.value, '#FFB74D');
});

document.addEventListener("DOMContentLoaded", () => {
    // 1. Animate Stat Cards (Sequence: 1, 2, 3, 4)
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate-pop');
        }, index * 150); // 150ms delay bawat card
    });

    // 2. Animate Main Chart (After stat cards)
    const mainChart = document.querySelector('.main-chart-box');
    if (mainChart) {
        setTimeout(() => {
            mainChart.classList.add('animate-pop');
        }, 600);
    }

    // 3. Animate Best Selling Box
    const bestSellingBox = document.querySelector('.best-selling-box');
    if (bestSellingBox) {
        setTimeout(() => {
            bestSellingBox.classList.add('animate-pop');
            animateDishes(); // Tawagin ang function para sa listahan
        }, 800);
    }

    // 4. Staggered Animation para sa mga Ulam sa listahan
    function animateDishes() {
        const dishes = document.querySelectorAll('.dish-item');
        dishes.forEach((dish, index) => {
            setTimeout(() => {
                dish.classList.add('animate-slide');
            }, index * 100); // 100ms delay bawat ulam
        });
    }

    // --- (Keep your existing SVG drawing code here) ---
    // drawAreaChart('graph-orders', statsData.orders, '#FF8A65');
    // ... etc
});