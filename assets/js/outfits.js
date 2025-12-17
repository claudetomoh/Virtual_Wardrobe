let currentOutfitItems = [];

function newOutfit() {
    document.getElementById('builderSection').style.display = 'block';
    clearCanvas();
    document.getElementById('outfitName').value = '';
    document.getElementById('outfitOccasion').value = '';
    document.getElementById('outfitSeason').value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Drag and Drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const draggables = document.querySelectorAll('.item-thumb');
    const dropZones = document.querySelectorAll('.drop-zone');

    draggables.forEach(draggable => {
        draggable.addEventListener('dragstart', handleDragStart);
        draggable.addEventListener('dragend', handleDragEnd);
    });

    dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleDragOver);
        zone.addEventListener('dragleave', handleDragLeave);
        zone.addEventListener('drop', handleDrop);
    });
});

function handleDragStart(e) {
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/html', this.innerHTML);
    e.dataTransfer.setData('item-id', this.dataset.itemId);
    e.dataTransfer.setData('item-name', this.dataset.itemName);
    e.dataTransfer.setData('item-image', this.dataset.itemImage);
    this.style.opacity = '0.5';
}

function handleDragEnd(e) {
    this.style.opacity = '1';
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    this.classList.add('drag-over');
    return false;
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    e.stopPropagation();
    e.preventDefault();

    this.classList.remove('drag-over');

    const itemId = e.dataTransfer.getData('item-id');
    const itemName = e.dataTransfer.getData('item-name');
    const itemImage = e.dataTransfer.getData('item-image');
    const slot = this.dataset.slot;

    // Check if item already exists in outfit
    if (currentOutfitItems.some(item => item.id === itemId)) {
        alert('This item is already in the outfit');
        return;
    }

    // Add to outfit
    currentOutfitItems.push({ id: itemId, name: itemName, image: itemImage, slot: slot });

    // Update UI
    this.classList.add('filled');
    this.innerHTML = `
        <div class="dropped-item">
            <img src="${itemImage || 'assets/images/placeholder.png'}" alt="${itemName}">
            <button class="remove-item" onclick="removeItem('${slot}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    updateSelectedItems();
    return false;
}

function removeItem(slot) {
    currentOutfitItems = currentOutfitItems.filter(item => item.slot !== slot);

    const dropZone = document.querySelector(`.drop-zone[data-slot="${slot}"]`);
    dropZone.classList.remove('filled');
    dropZone.innerHTML = '<div class="placeholder"><i class="fas fa-plus"></i><br>Drop item here</div>';

    updateSelectedItems();
}

function clearCanvas() {
    currentOutfitItems = [];
    document.querySelectorAll('.drop-zone').forEach(zone => {
        zone.classList.remove('filled');
        zone.innerHTML = '<div class="placeholder"><i class="fas fa-plus"></i><br>Drop item here</div>';
    });
    updateSelectedItems();
}

function updateSelectedItems() {
    const itemsDiv = document.getElementById('selectedItems');
    if (currentOutfitItems.length === 0) {
        itemsDiv.innerHTML = 'No items selected';
    } else {
        itemsDiv.innerHTML = currentOutfitItems.map(item =>
            `<div style="padding: 5px 0;">${item.name}</div>`
        ).join('');
    }
}

async function saveOutfit() {
    if (currentOutfitItems.length === 0) {
        alert('Please add at least one item to the outfit');
        return;
    }

    const name = document.getElementById('outfitName').value.trim();
    if (!name) {
        alert('Please enter an outfit name');
        return;
    }

    const data = {
        name: name,
        occasion: document.getElementById('outfitOccasion').value,
        season: document.getElementById('outfitSeason').value,
        items: currentOutfitItems.map(item => item.id)
    };

    try {
        const response = await fetch('api/outfits_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert('Outfit saved successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error saving outfit');
    }
}

async function deleteOutfit(id) {
    if (!confirm('Are you sure you want to delete this outfit?')) return;

    try {
        const response = await fetch('api/outfits_api.php', {
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
        alert('Error deleting outfit');
    }
}

async function viewOutfit(id) {
    try {
        const response = await fetch(`api/outfits_api.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            // Load outfit into builder
            newOutfit();
            document.getElementById('outfitName').value = result.data.outfit.name;
            document.getElementById('outfitOccasion').value = result.data.outfit.occasion || '';
            document.getElementById('outfitSeason').value = result.data.outfit.season || '';

            // Load items into canvas
            clearCanvas();
            result.data.items.forEach((item, index) => {
                const slot = (index + 1).toString();
                const dropZone = document.querySelector(`.drop-zone[data-slot="${slot}"]`);

                if (dropZone) {
                    currentOutfitItems.push({
                        id: item.id,
                        name: item.name,
                        image: item.image_path,
                        slot: slot
                    });

                    dropZone.classList.add('filled');
                    dropZone.innerHTML = `
                        <div class="dropped-item">
                            <img src="${item.image_path || 'assets/images/placeholder.png'}" alt="${item.name}">
                            <button class="remove-item" onclick="removeItem('${slot}')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                }
            });

            updateSelectedItems();
        }
    } catch (error) {
        alert('Error loading outfit');
    }
}