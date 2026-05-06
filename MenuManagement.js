document.addEventListener("DOMContentLoaded", () => {
    // 1. SELECTORS (Kukunin ang mga elements sa HTML)
    const tableBody = document.getElementById("menu-table-body");
    const categoryDropdown = document.getElementById("category-dropdown-filter");

    // 2. FILTER FUNCTION (Dito natin aayusin ang pagpapakita ng ulam)
    // Sa halip na i-overwrite ang table, itatago na lang natin ang hindi kasama sa category
    window.renderMenu = (filter = "All") => {
        const rows = tableBody.getElementsByTagName("tr"); // Kunin ang lahat ng rows mula sa PHP
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            // Kukunin ang category text sa ika-3 column ng table
            const itemCategory = row.cells[2].textContent.trim(); 

            if (filter === "All" || itemCategory === filter) {
                row.style.display = ""; // Ipakita
                row.classList.add("row-animate"); // I-retain ang animation mo[cite: 11]
            } else {
                row.style.display = "none"; // Itago
            }
        }
    }

    // 3. CATEGORY CLICK FILTER (Para sa category management list)[cite: 11]
    window.filterByCategory = (cat) => {
        categoryDropdown.value = cat;
        renderMenu(cat);
    };

    // 4. DROPDOWN LISTENER (Kapag binago ang selection sa taas)[cite: 11]
    categoryDropdown.addEventListener("change", (e) => {
        renderMenu(e.target.value);
    });

    // Paalala: Tinanggal natin ang 'menuData' at 'categories' arrays dahil[cite: 11]
    // sila ang dahilan kung bakit hindi tumutugma ang images sa database mo.[cite: 11]
    
    // Initial Load - I-run ang filter para sa 'All'
    renderMenu("All");
});