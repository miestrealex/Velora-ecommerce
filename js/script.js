
/* =========================
   Variables & DOM Elements 
========================= */


let cartItems = JSON.parse(localStorage.getItem("cartItems")) || [];
let cartCount = 0;

let allProducts = [];

const overlay = document.getElementById("overlay");

const cartIcon = document.getElementById("cart-icon");

const sidebar = document.getElementById("sidebar");

const userIcon = document.getElementById("user-icon");

const loginDropdown = document.getElementById("login-dropdown");

const userMenu = document.getElementById("user-menu");

const userDropdown = document.getElementById("user-dropdown");

const productsContainer = document.getElementById("products");

const search = document.getElementById("search");
const registerDropdown = document.getElementById("register-dropdown");
const openRegister = document.getElementById("open-register");

/* =========================
   Overlay Management 
========================= */

function updateOverlay(){

    const sidebarActive =
        sidebar && sidebar.classList.contains("active");

    const loginActive =
        loginDropdown && loginDropdown.classList.contains("active");

    const userActive =
        userDropdown && userDropdown.classList.contains("active");

    if(
        sidebarActive ||
        loginActive ||
        userActive
    ){

        overlay.classList.add("active");

    } else {

        overlay.classList.remove("active");

    }

}

/* =========================
   Local Storage
========================= */

const savedCart = localStorage.getItem("cartItems");
if (savedCart) {
    cartItems = JSON.parse(savedCart);
    cartItems.forEach(item => {
        cartCount += item.quantity;
    });
}
document.getElementById("cart-count").innerText = cartCount;

/* =========================
   FETCH PRODUCTS
========================= */

fetch("products.php")

    .then(response => response.json())
    .then(products => {

        allProducts = products;

        displayProducts(products);

    })
    .catch(error =>{
        console.error("Error loading products:", error);

    });

/* =========================
   CART
========================= */

updateCart();

function getBadgeClass(badge){

    switch(badge){

        case "SALE":
            return "badge-red";

        case "NEW":
            return "badge-green";

        case "HOT":
            return "badge-orange";

        case "LIMITED":
            return "badge-black";

        default:
            return "";

    }

}

function displayProducts(products) {
    productsContainer.innerHTML = "";
    products.forEach(product => {
        productsContainer.innerHTML += `
        <div class="product-cart">
            ${product.badge ? `
                <span class="promo-badge ${getBadgeClass(product.badge)}">
                    ${product.badge}
                </span>
            ` : ""}
            <div class="product-image">
                <img src="${product.image}" alt="${product.brand} ${product.model}">
            </div>
            <div class="product-info">
                <h3>${product.brand}</h3>
                <h4>${product.model}</h4>
            </div>
            <p class="price">
                CHF ${product.price}
            </p>
            <button onclick="addToCart('${product.brand}', '${product.model}', ${product.price}, '${product.image}')">
                Buy Now
            </button>
        </div>
        `;
    });
}

function addToCart(productBrand, productModel, productPrice, productImage) {
    cartCount++;
    document.getElementById("cart-count").innerText = cartCount;
    const existingProduct = cartItems.find(
        item => item.brand === productBrand &&
                item.model === productModel
    );
    if (existingProduct) {
        existingProduct.quantity++;
    } else {
        cartItems.push({
            brand: productBrand,
            model: productModel,
            price: Number(productPrice),
            image: productImage,
            quantity: 1
        });
    }
    updateCart();
}

function updateCart() {
    const cartItemsDiv = document.getElementById("cart-items");
    cartItemsDiv.innerHTML = "";
    let total = 0;
    cartItems.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        cartItemsDiv.innerHTML += `
        <div class="cart-item">
            <img src="${item.image}" alt="${item.brand} ${item.model}">
            <div class="cart-info">
                <h4>${item.brand}</h4>
                <h3>${item.model}</h3>
                <p>CHF ${item.price}</p>
                <div class="quantity-controls">
                    <button onclick="event.stopPropagation(); decreaseQuantity(${index})">
                        -
                    </button>
                    <span>${item.quantity}</span>
                    <button onclick="event.stopPropagation(); increaseQuantity(${index})">
                        +
                    </button>
                </div>
            </div>
        </div>
        `;
        total += itemTotal;
    });
    document.getElementById("cart-total").innerText =` Total: CHF ${total.toFixed(2)}`;
    localStorage.setItem("cartItems", JSON.stringify(cartItems));
}

function increaseQuantity(index){

    cartItems[index].quantity++;

    cartCount++;

    document.getElementById("cart-count").innerText = cartCount;

    updateCart();

}

function decreaseQuantity(index){

    cartItems[index].quantity--;

    cartCount--;

    if(cartItems[index].quantity <= 0){

        cartItems.splice(index, 1);

    }

    document.getElementById("cart-count").innerText = cartCount;

    updateCart();

}

openRegister.addEventListener("click", function(event){
    event.preventDefault();
    event.stopPropagation();
    
    loginDropdown.classList.remove("active");
    registerDropdown.classList.add("active");
    updateOverlay();
});

/* =========================
   BUTTONS
========================= */

cartIcon.onclick = function(event){

    event.stopPropagation();

    // FECHAR OUTROS
    loginDropdown.classList.remove("active");
    if (userDropdown){
        userDropdown.classList.remove("active");
    }
    

    // TOGGLE CARRINHO
    sidebar.classList.toggle("active");

    updateOverlay();

}

if(userIcon){

    userIcon.onclick = function(event){

        event.stopPropagation();

        // FECHAR OUTROS
        sidebar.classList.remove("active");
        if(userDropdown){
            userDropdown.classList.remove("active");
        }

        // TOGGLE LOGIN
        loginDropdown.classList.toggle("active");

        updateOverlay();

    }

}

if(userMenu){

    userMenu.onclick = function(event){

        event.stopPropagation();

        // FECHAR OUTROS
        sidebar.classList.remove("active");
        loginDropdown.classList.remove("active");

        // TOGGLE USER MENU
        userDropdown.classList.toggle("active");

        updateOverlay();

    }

}
/* =========================
   FECHAR AO CLICAR FORA
========================= */

document.body.addEventListener("click", function(event){

    // CARRINHO
    if(
        cartIcon.contains(event.target) ||
        sidebar.contains(event.target)
    ){
        return;
    }

    // LOGIN
    if(
        userIcon &&
        (
            userIcon.contains(event.target) ||
            loginDropdown.contains(event.target) ||
            registerDropdown.contains(event.target)
        )
    ){
        return;
    }

    // USER MENU
    if(
        userMenu &&
        (
            userMenu.contains(event.target) ||
            userDropdown.contains(event.target)
        )
    ){
        return;
    }

    // FECHAR TUDO
    sidebar.classList.remove("active");

    loginDropdown.classList.remove("active");

    if(registerDropdown){
        registerDropdown.classList.remove("active");
    }

    if(userDropdown){
        userDropdown.classList.remove("active");
    }
    updateOverlay();

});

/* =========================
   SEARCH
========================= */

search.addEventListener("input", () => {

    const searchValue = search.value.toLowerCase();

    const filteredProducts = allProducts.filter(product => {

        return product.brand.toLowerCase().includes(searchValue) ||
               product.model.toLowerCase().includes(searchValue);

    });

    displayProducts(filteredProducts);

});

