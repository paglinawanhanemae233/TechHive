<?php
/**
 * TechHive Shopping Cart
 * Displays cart items and manages cart operations
 */

require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - TechHive</title>
    <style>
        :root {
            --primary-indigo: #4A088C;
            --secondary-blue: #120540;
            --accent-blue: #433C73;
            --light-purple: #AEA7D9;
            --neutral-blue: #727FA6;
            --white: #ffffff;
            --black: #000000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: var(--secondary-blue);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: var(--primary-indigo);
            color: var(--white);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--white);
            text-decoration: none;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-icon {
            background: rgba(255,255,255,0.2);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .page-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title h1 {
            font-size: 2.5rem;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: var(--neutral-blue);
            font-size: 1.1rem;
        }

        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .cart-items {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-size: 2rem;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }

        .item-price {
            font-size: 1.1rem;
            color: var(--primary-indigo);
            font-weight: bold;
        }

        .item-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            background: var(--accent-blue);
            color: var(--white);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--primary-indigo);
            transform: scale(1.1);
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.5rem;
            font-weight: bold;
        }

        .remove-btn {
            background: #dc3545;
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .cart-summary {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .summary-row.total {
            border-top: 2px solid #eee;
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-indigo);
            margin-top: 1rem;
        }

        .checkout-btn {
            width: 100%;
            background: var(--primary-indigo);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .checkout-btn:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
        }

        .empty-cart {
            text-align: center;
            background: var(--white);
            border-radius: 15px;
            padding: 4rem 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .empty-cart-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-cart h2 {
            color: var(--secondary-blue);
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: var(--neutral-blue);
            margin-bottom: 2rem;
        }

        .continue-shopping {
            background: var(--primary-indigo);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .continue-shopping:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .item-controls {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <nav class="nav">
            <a href="index.html" class="logo">TechHive</a>
            <div class="nav-actions">
                <a href="index.html" class="cart-icon">🛒 <span class="cart-count">0</span></a>
            </div>
        </nav>
    </div>

    <div class="container">
        <div class="page-title">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <div id="cart-content">
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="index.html" class="continue-shopping">Continue Shopping</a>
            </div>
        </div>
    </div>

    <script>
        // Load cart from localStorage
        let cart = JSON.parse(localStorage.getItem('techhive_cart')) || [];
        let cartCount = 0;
        
        // Debug logging
        console.log('Cart loaded from localStorage:', cart);
        console.log('Cart length:', cart.length);
        console.log('localStorage techhive_cart:', localStorage.getItem('techhive_cart'));

        // Update cart count display
        function updateCartCount() {
            cartCount = cart.reduce((total, item) => total + item.quantity, 0);
            const cartIcon = document.querySelector('.cart-count');
            if (cartIcon) {
                cartIcon.textContent = cartCount;
            }
        }

        // Load cart items
        function loadCartItems() {
            const cartContent = document.getElementById('cart-content');
            
            console.log('Loading cart items, cart length:', cart.length);
            console.log('Cart items:', cart);
            
            if (cart.length === 0) {
                cartContent.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <h2>Your cart is empty</h2>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="index.html" class="continue-shopping">Continue Shopping</a>
                    </div>
                `;
                return;
            }

            let total = 0;
            let cartItemsHTML = `
                <div class="cart-container">
                    <div class="cart-items">
            `;

            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                cartItemsHTML += `
                    <div class="cart-item" data-product-id="${item.id}">
                        <div class="item-image">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '📦'}
                        </div>
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">₱${item.price.toLocaleString()}</div>
                        </div>
                        <div class="item-controls">
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                                <input type="number" class="quantity-input" value="${item.quantity}" 
                                       onchange="updateQuantity('${item.id}', this.value)" min="1">
                                <button class="quantity-btn" onclick="updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                            </div>
                            <button class="remove-btn" onclick="removeItem('${item.id}')">Remove</button>
                        </div>
                    </div>
                `;
            });

            cartItemsHTML += `
                    </div>
                    <div class="cart-summary">
                        <h3 class="summary-title">Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>₱${total.toLocaleString()}</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (8%):</span>
                            <span>₱${(total * 0.08).toLocaleString()}</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>${total > 10000 ? 'FREE' : '₱100'}</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>₱${(total + (total * 0.08) + (total > 10000 ? 0 : 100)).toLocaleString()}</span>
                        </div>
                        <button class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
                    </div>
                </div>
            `;

            cartContent.innerHTML = cartItemsHTML;
        }

        // Update quantity
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) {
                removeItem(productId);
                return;
            }

            const item = cart.find(item => item.id === productId);
            if (item) {
                item.quantity = parseInt(newQuantity);
                localStorage.setItem('techhive_cart', JSON.stringify(cart));
                loadCartItems();
                updateCartCount();
            }
        }

        // Remove item
        function removeItem(productId) {
            cart = cart.filter(item => item.id !== productId);
            localStorage.setItem('techhive_cart', JSON.stringify(cart));
            loadCartItems();
            updateCartCount();
        }

        // Proceed to checkout
        function proceedToCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            window.location.href = 'checkout.php';
        }

        // Initialize cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCartItems();
            updateCartCount();
        });
    </script>
</body>
</html>