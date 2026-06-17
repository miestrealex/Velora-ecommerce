/*
|--------------------------------------------------------------------------
| Tab Management
|--------------------------------------------------------------------------
| Handles opening and closing admin dashboard sections.
*/
function Showtab(tabId) {
    const tab = document.getElementById(tabId);
    const tabsContent = document.getElementById("tabs-content");
    // Close the selected tab if it is already open
    if (tab.style.display === "block") {
        tab.style.display = "none";
        tabsContent.style.display = "none";
        document.getElementById("search-wrapper").classList.remove("active");
        document.getElementById("add-product-wrapper").classList.remove("active");
        document.getElementById("edit-product-wrapper").classList.remove("edit-active");
        document.getElementById("add-category-wrapper").classList.remove("active");
        document.getElementById("categories-wrapper").classList.remove("active");
        return;
    }
    // Hide all sections
    document.getElementById("search-products").style.display = "none";
    document.getElementById("add-product").style.display = "none";
    document.getElementById("add-category").style.display = "none";
    document.getElementById("categories").style.display = "none";
    // Remove active states
    document.getElementById("search-wrapper").classList.remove("active");
    document.getElementById("add-product-wrapper").classList.remove("active");
    document.getElementById("edit-product-wrapper").classList.remove("active");
    document.getElementById("add-category-wrapper").classList.remove("active");
    document.getElementById("categories-wrapper").classList.remove("active");
    // Disable edit mode
    document.body.classList.remove("edit-mode");
    document.getElementById("edit-product-wrapper").classList.remove("edit-active");
    // Open selected section
    tabsContent.style.display = "block";
    tab.style.display = "block";
    // Activate corresponding navigation item
    if (tabId === "search-products") {
        document.getElementById("search-wrapper").classList.add("active");
    }
    if (tabId === "add-product") {
        document.getElementById("add-product-wrapper").classList.add("active");
    }
    if (tabId === "add-category") {
        document.getElementById("add-category-wrapper").classList.add("active");
    }
    if (tabId === "categories") {
        document.getElementById("categories-wrapper").classList.add("active");
    }
}
/*
|--------------------------------------------------------------------------
| Outside Click Handler
|--------------------------------------------------------------------------
| Closes all dashboard sections when clicking outside.
*/
document.addEventListener("click", function (event) {
    const tabsContent = document.getElementById("tabs-content");
    const adminTabs = document.getElementById("admin-tabs");
    if (
        adminTabs.contains(event.target) ||
        tabsContent.contains(event.target)
    ) {
        return;
    }
    // Hide all sections
    document.getElementById("search-products").style.display = "none";
    document.getElementById("add-product").style.display = "none";
    document.getElementById("add-category").style.display = "none";
    document.getElementById("categories").style.display = "none";
    // Hide container
    tabsContent.style.display = "none";
    // Remove active states
    document.getElementById("search-wrapper").classList.remove("active");
    document.getElementById("add-product-wrapper").classList.remove("active");
    document.getElementById("add-category-wrapper").classList.remove("active");
    document.getElementById("categories-wrapper").classList.remove("active");
});
/*
|--------------------------------------------------------------------------
| Product Image Preview
|--------------------------------------------------------------------------
| Displays a preview of the selected product image.
*/
document.getElementById("image-upload").addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
        document.getElementById("file-name").textContent = file.name;
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById("image-preview");
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});
/*
|--------------------------------------------------------------------------
| Edit Mode
|--------------------------------------------------------------------------
| Enables or disables product editing mode.
*/
let editMode = false;
function toggleEditMode() {
    // Hide all sections
    document.getElementById("search-products").style.display = "none";
    document.getElementById("add-product").style.display = "none";
    document.getElementById("add-category").style.display = "none";
    document.getElementById("categories").style.display = "none";
    // Remove active states
    document.getElementById("search-wrapper").classList.remove("active");
    document.getElementById("add-product-wrapper").classList.remove("active");
    document.getElementById("add-category-wrapper").classList.remove("active");
    document.getElementById("categories-wrapper").classList.remove("active");
    editMode = !editMode;
    document.body.classList.toggle("edit-mode");
    document.getElementById("edit-product-wrapper").classList.toggle("edit-active");
}
/*
|--------------------------------------------------------------------------
| Edit Product Modal
|--------------------------------------------------------------------------
| Closes the edit modal and returns to the admin page.
*/
function closeEditModal() {
    window.location.href = "admin.php";
}
/*
|--------------------------------------------------------------------------
| Edit Image Preview
|--------------------------------------------------------------------------
| Displays a preview of the new image before updating.
*/
document.getElementById("edit-image-upload").addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("edit-image-preview").src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});