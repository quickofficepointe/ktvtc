const menuBtn = document.getElementById("menu-btn");
const closeBtn = document.getElementById("close-btn");
const sideDrawer = document.getElementById("side-drawer");
const overlay = document.getElementById("overlay");

menuBtn.addEventListener("click", () => {
    sideDrawer.classList.remove("-translate-x-full");
    overlay.classList.remove("hidden");
});

closeBtn.addEventListener("click", () => {
    sideDrawer.classList.add("-translate-x-full");
    overlay.classList.add("hidden");
});

overlay.addEventListener("click", () => {
    sideDrawer.classList.add("-translate-x-full");
    overlay.classList.add("hidden");
});

// Toggle dropdowns in mobile menu
document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', () => {
    const menu = btn.nextElementSibling;
    menu.classList.toggle('hidden');
    });
});