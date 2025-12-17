function filterCategory(category) {
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    window.location = url;
}

function searchItems(query) {
    const url = new URL(window.location);
    if (query) {
        url.searchParams.set('search', query);
    } else {
        url.searchParams.delete('search');
    }
    window.location = url;
}

async function saveItem() {
    const form = document.getElementById('itemForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('api/wardrobe_api.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error saving item');
    }
}

async function editItem(id) {
    try {
        const response = await fetch(`api/wardrobe_api.php?action=get&id=${id}`);
        const result = await response.json();

        if (result.success) {
            const item = result.data;
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemCategory').value = item.category;
            document.getElementById('itemColor').value = item.color || '';
            document.getElementById('itemSeason').value = item.season || '';
            document.getElementById('modalTitle').textContent = 'Edit Item';

            const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
            modal.show();
        }
    } catch (error) {
        alert('Error loading item');
    }
}

async function deleteItem(id) {
    if (!confirm('Are you sure you want to delete this item?')) return;

    try {
        const response = await fetch('api/wardrobe_api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error deleting item');
    }
}

async function toggleFavorite(id, button) {
    try {
        const response = await fetch('api/wardrobe_api.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'favorite', id })
        });

        const result = await response.json();

        if (result.success) {
            button.classList.toggle('active');
        }
    } catch (error) {
        alert('Error updating favorite');
    }
}

// Reset form when modal closes
document.getElementById('addItemModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Item';
});