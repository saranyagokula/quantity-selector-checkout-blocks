document.addEventListener('DOMContentLoaded', function() {
    const { registerCheckoutFilters } = window.wc.blocksCheckout;

    // Function to modify the item name and include a quantity selector and delete icon
    const modifyItemName = (defaultValue, extensions, args) => {
        const isSummaryContext = args?.context === 'summary';
        
        if (!isSummaryContext) {
            return defaultValue;
        }

        // Retrieve the current quantity of the cart item
        const quantity = args?.cartItem?.quantity || 1;

        // Create the HTML for the quantity selector and delete icon
        const quantitySelector = `
            <div class="quantity-selector">
                <label for="quantity-${args.cartItem.id}">Quantity:</label>
                <input type="number" id="quantity-${args.cartItem.id}" name="quantity-${args.cartItem.id}" 
                    value="${quantity}" min="1" max="${args.cartItem.quantity_limits?.maximum || 10}" 
                    data-item-id="${args.cartItem.id}" 
                    class="quantity-input" />
                <span class="delete-icon" data-item-id="${args.cartItem.id}" title="Remove Item">&times;</span>
            </div>
        `;

        // Return the modified item name with the quantity selector and delete icon
        return `${defaultValue}${quantitySelector}`;
    };

    // Register the filter
    registerCheckoutFilters('quantity-selector', {
        itemName: modifyItemName,
    });

    // Handle quantity change and send data to the server
    document.addEventListener('change', function(event) {
        if (event.target.classList.contains('quantity-input')) {
            const itemId = event.target.getAttribute('data-item-id');
            const quantity = event.target.value;

            // Call extensionCartUpdate to send data to the server
            const { extensionCartUpdate } = window.wc.blocksCheckout;
            extensionCartUpdate({
                namespace: 'quantity-selector',
                data: {
                    itemId: itemId,
                    quantity: quantity
                },
            });
        }
    });

    // Handle delete icon click and send data to the server
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-icon')) {
            const itemId = event.target.getAttribute('data-item-id');

            // Call extensionCartUpdate to remove the item
            const { extensionCartUpdate } = window.wc.blocksCheckout;
            extensionCartUpdate({
                namespace: 'quantity-selector',
                data: {
                    itemId: itemId,
                    action: 'delete'
                },
            });
        }
    });
});
