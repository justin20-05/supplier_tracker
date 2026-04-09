async function viewItems(orderId) {
    const modal = document.getElementById('itemsModal');
    const body = document.getElementById('modalBody');
    
    modal.classList.remove('hidden');
    body.innerHTML = '<p class="text-center text-gray-400 py-10">Loading items...</p>';

    try {
        const response = await fetch(`../actions/get_order_items.php?order_id=${orderId}`);
        const items = await response.json();

        if (items.length === 0) {
            body.innerHTML = '<p class="text-center text-gray-400 py-10">No items found in this order.</p>';
            return;
        }

        let html = `<table class="w-full text-left text-sm">
            <thead>
                <tr class="text-[10px] font-bold text-gray-400 uppercase border-b"><th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Price</th></tr>
            </thead>
            <tbody>`;
        
        items.forEach(item => {
            html += `<tr class="border-b border-gray-50">
                <td class="py-3 font-bold text-gray-800">${item.product_name}</td>
                <td class="py-3 text-gray-600">${item.quantity}</td>
                <td class="py-3 text-right font-mono text-gray-900">₱${parseFloat(item.unit_price_at_order).toLocaleString()}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        body.innerHTML = html;
    } catch (e) {
        body.innerHTML = '<p class="text-center text-red-500 py-10">Failed to load items.</p>';
    }
}

function closeItemsModal() {
    document.getElementById('itemsModal').classList.add('hidden');
}