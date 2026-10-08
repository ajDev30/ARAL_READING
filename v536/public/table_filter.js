function initTailwindTable(tableId, searchId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    const searchInput = document.getElementById(searchId);
    
    // Setup Search
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Setup Sorting
    const headers = table.querySelectorAll('thead th');
    headers.forEach((header, index) => {
        if(header.classList.contains('no-sort')) return;
        header.style.cursor = 'pointer';
        header.innerHTML += ' <i class="fas fa-sort text-slate-300 ml-1 text-[10px]"></i>';
        
        let asc = true;
        header.addEventListener('click', () => {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Reset icons
            headers.forEach(h => {
                const icon = h.querySelector('i.fa-sort, i.fa-sort-up, i.fa-sort-down');
                if(icon) { icon.className = 'fas fa-sort text-slate-300 ml-1 text-[10px]'; }
            });
            
            // Set current icon
            const currentIcon = header.querySelector('i');
            if (currentIcon) {
                currentIcon.className = asc ? 'fas fa-sort-up text-blue-500 ml-1 text-[10px]' : 'fas fa-sort-down text-blue-500 ml-1 text-[10px]';
            }

            rows.sort((a, b) => {
                const aCol = a.cells[index].textContent.trim();
                const bCol = b.cells[index].textContent.trim();
                
                // Try numeric sort
                const aNum = parseFloat(aCol.replace(/[^0-9.-]/g, ''));
                const bNum = parseFloat(bCol.replace(/[^0-9.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return asc ? aNum - bNum : bNum - aNum;
                }
                return asc ? aCol.localeCompare(bCol) : bCol.localeCompare(aCol);
            });

            rows.forEach(row => tbody.appendChild(row));
            asc = !asc;
        });
    });
}
