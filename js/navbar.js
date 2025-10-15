const sidebar = document.querySelector(".sidebar");
const sidebarToggler = document.querySelector(".sidebar-toggler");
const menuToggler = document.querySelector(".menu-toggler");
const mainContent = document.querySelector(".main-content");

const collapsedSidebarHeight = "56px";

// Función para ajustar el contenido principal
const adjustMainContent = () => {
    if (window.innerWidth >= 1024) {
        // Modo desktop
        if (sidebar.classList.contains("collapsed")) {
            mainContent.style.marginLeft = "117px";
            mainContent.style.width = "calc(100% - 117px)";
            mainContent.style.marginTop = "0";
        } else {
            mainContent.style.marginLeft = "302px";
            mainContent.style.width = "calc(100% - 302px)";
            mainContent.style.marginTop = "0";
        }
    } else {
        // Modo móvil/tablet
        mainContent.style.marginLeft = "0";
        mainContent.style.width = "100%";
        
        // Ajustar margin-top según si el menú está activo
        if (sidebar.classList.contains("menu-active")) {
            const sidebarHeight = sidebar.scrollHeight;
            // Usamos un cálculo más preciso
            mainContent.style.marginTop = `${sidebarHeight + 20}px`;
        } else {
            // Cuando el menú está colapsado en móvil
            mainContent.style.marginTop = "76px"; // 56px (altura sidebar) + 20px (margen)
        }
    }
};

sidebarToggler.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    adjustMainContent();
});

const toggleMenu = (isMenuActive) => {
    if (window.innerWidth < 1024) {
        sidebar.style.height = isMenuActive ? `${sidebar.scrollHeight}px` : collapsedSidebarHeight;
        menuToggler.querySelector("span").innerText = isMenuActive ? "close" : "menu";
    }
    adjustMainContent();
}

menuToggler.addEventListener("click", () => {
    const wasActive = sidebar.classList.contains("menu-active");
    sidebar.classList.toggle("menu-active");
    toggleMenu(!wasActive);
});

window.addEventListener("resize", () => {
    if (window.innerWidth >= 1024) {
        // Modo desktop - resetear a estado normal
        sidebar.style.height = "calc(100vh - 32px)";
        sidebar.classList.remove("menu-active");
        menuToggler.querySelector("span").innerText = "menu";
        // Asegurar que no tenga estilos inline conflictivos
        sidebar.style.height = "";
    } else {
        // Modo móvil
        sidebar.classList.remove("collapsed");
        sidebar.style.height = collapsedSidebarHeight;
        sidebar.classList.remove("menu-active");
        menuToggler.querySelector("span").innerText = "menu";
    }
    adjustMainContent();
});

// Ajuste inicial al cargar la página
window.addEventListener("load", adjustMainContent);
document.addEventListener("DOMContentLoaded", adjustMainContent);

// Ajustar también cuando termina la transición del sidebar
sidebar.addEventListener('transitionend', adjustMainContent);