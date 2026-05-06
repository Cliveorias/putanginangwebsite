document.addEventListener("DOMContentLoaded", () => {
    
    // --- 1. TOGGLE FULL REPORT ---
    const generateBtn = document.getElementById("generate-report-btn");
    const insightsSection = document.getElementById("insights-container");

    generateBtn.addEventListener("click", () => {
        const isShowing = insightsSection.classList.toggle("show");
        generateBtn.innerText = isShowing ? "Hide Full Report" : "Generate Full Report";
    });

    // --- 2. SALES TAB SWITCHING ---
    const salesData = {
        Daily: { earnings: "₱12,450.00", orders: "45", avg: "₱276", chart: [80, 40, 90, 60, 110, 70, 100] },
        Weekly: { earnings: "₱145,256.00", orders: "410", avg: "₱468", chart: [50, 90, 70, 130, 85, 110, 140] },
        Monthly: { earnings: "₱620,800.00", orders: "1,850", avg: "₱335", chart: [60, 110, 50, 150, 90, 120, 130] }
    };

    window.updateSalesTab = (tab) => {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.toggle('active', btn.innerText === tab);
        });

        // Tukuyin kung ano ang itatawag sa X-axis (Day, Wek, o Mon)
        let prefix = "Day";
        if (tab === "Weekly") prefix = "Wek";
        if (tab === "Monthly") prefix = "Mon";

        const data = salesData[tab];
        document.getElementById('stat-earnings').innerText = data.earnings;
        document.getElementById('stat-orders').innerText = data.orders;
        document.getElementById('stat-avg').innerText = data.avg;
        document.getElementById('chart-title').innerText = `${tab.toUpperCase()} EARNINGS`;

        // Ipasa ang prefix at tab name sa drawing function
        drawSalesChart(data.chart, prefix, tab);
    };

   // --- FIXED DYNAMIC CHART DRAWING ---
function drawSalesChart(dataPoints, labelPrefix, viewType) {
    const container = document.getElementById("earnings-graph");
    if (!container) return;

    const width = 600;
    const height = 200;
    const padding = { top: 50, right: 30, bottom: 50, left: 80 }; // Tinaasan ang top padding
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    
    // Gawin nating 200 ang maxVal para may sapat na "hinga" ang labels sa taas
    const maxVal = 200; 

    const getX = (i) => padding.left + (i * (chartWidth / (dataPoints.length - 1)));
    const getY = (val) => padding.top + (chartHeight - (val / maxVal) * chartHeight);

    const points = dataPoints.map((val, i) => `${getX(i)},${getY(val)}`).join(" ");
    const areaPath = `M ${getX(0)} ${padding.top + chartHeight} ${points} L ${getX(dataPoints.length - 1)} ${padding.top + chartHeight} Z`;

    // FIXED: Evenly spaced grid values para hindi mag-overlap (0, 50, 100, 150, 200)
    const gridValues = [0, 50, 100, 150, 200];
    const gridLines = gridValues.map(v => {
        const yPos = getY(v);
        return `
            <line x1="${padding.left}" y1="${yPos}" x2="${width - padding.right}" y2="${yPos}" stroke="#eee" stroke-width="1" />
            <text x="${padding.left - 15}" y="${yPos + 4}" text-anchor="end" font-size="11" font-weight="700" fill="#666">₱${v === 0 ? '0' : v + ',000'}</text>
        `;
    }).join("");

    container.innerHTML = `
        <svg viewBox="0 0 ${width} ${height}" style="width:100%; height:100%; font-family: 'Poppins', sans-serif;">
            <defs>
                <linearGradient id="chartGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#FFBD59;stop-opacity:0.4" />
                    <stop offset="100%" style="stop-color:#FFBD59;stop-opacity:0" />
                </linearGradient>
            </defs>

            <!-- Grid Lines & Y-Axis Labels -->
            ${gridLines}

            <!-- Area Fill -->
            <path d="${areaPath}" fill="url(#chartGrad)" />

            <!-- Trend Line -->
            <polyline points="${points}" fill="none" stroke="#A67C00" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />

            <!-- Data Points & Labels -->
            ${dataPoints.map((val, i) => {
                const cx = getX(i);
                const cy = getY(val);
                return `
                    <circle cx="${cx}" cy="${cy}" r="5" fill="#fff" stroke="#A67C00" stroke-width="2" />
                    <text x="${cx}" y="${cy - 12}" text-anchor="middle" font-size="12" font-weight="900" fill="#000">₱${val}</text>
                    
                    <!-- X-Axis Labels -->
                    <text x="${cx}" y="${height - 10}" text-anchor="middle" font-size="11" font-weight="700" fill="#333">
                        ${i === 0 ? viewType : labelPrefix + ' ' + i}
                    </text>
                `;
            }).join("")}
            
            <line x1="${padding.left}" y1="${padding.top + chartHeight}" x2="${width - padding.right}" y2="${padding.top + chartHeight}" stroke="#ccc" stroke-width="1" />
        </svg>
    `;
}

    // --- 4. TOP DISH INSIGHTS ---
    const topDishes = [
        { name: "Sisig", percent: 22.00, img: "https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501436/Tokwat_Lechon_l69imm.png" },
        { name: "Chicken Adobo", percent: 16.00, img: "https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501436/Tokwat_Lechon_l69imm.png" },
        { name: "Bicol Express", percent: 12.50, img: "https://res.cloudinary.com/dn38jxbeh/image/upload/v1773501436/Tokwat_Lechon_l69imm.png" }
    ];

    function renderDishInsights() {
        const container = document.getElementById("dish-insights-list");
        if (!container) return;
        container.innerHTML = topDishes.map(dish => `
            <div class="insight-item">
                <img src="${dish.img}" class="insight-img">
                <div class="insight-details">
                    <div class="insight-top-row"><span>${dish.name}</span><span>${dish.percent.toFixed(2)}%</span></div>
                    <div class="progress-container"><div class="progress-fill" style="width: ${dish.percent}%"></div></div>
                </div>
            </div>`).join("");
    }

    // Initialize
    renderDishInsights();
    updateSalesTab('Daily'); // Default view
});