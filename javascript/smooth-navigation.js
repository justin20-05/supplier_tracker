document.addEventListener('DOMContentLoaded', () => {
    const mainContent = document.getElementById('main-content');

    document.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        
        if (link && 
            link.href.includes(window.location.origin) && 
            !link.getAttribute('target') && 
            !link.hasAttribute('download') &&
            link.dataset.noSmoothNav !== 'true' &&
            !link.href.includes('logout.php')) {
            
            e.preventDefault();
            loadPage(link.href);
        }
    });

    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (form.method.toLowerCase() === 'get' && form.dataset.noSmoothNav !== 'true') {
            e.preventDefault();
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            const url = `${form.action || window.location.pathname}?${params}`;
            loadPage(url);
        }
    });

    window.addEventListener('popstate', () => {
        loadPage(window.location.href, false);
    });

    async function loadPage(url, pushState = true) {
        updateActiveLink(url);
        mainContent.style.opacity = '0';

        try {
            const response = await fetch(url);
            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newElement = doc.getElementById('main-content');
            
            const isProductPage = url.includes('product_list.php');

            if (newElement) {
                document.title = doc.title;
                
                mainContent.innerHTML = newElement.innerHTML;

                const scripts = mainContent.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                if (pushState) window.history.pushState({}, '', url);

                mainContent.style.opacity = '1';

                if (url.includes('add_order.php') && typeof initAddOrder === 'function') {
                    initAddOrder();
                }

                if (typeof checkInventoryAlerts === 'function') {
                    checkInventoryAlerts(isProductPage);
                }

            } else {
                window.location.href = url;
            }
        } catch (error) {
            console.error('Smooth navigation failed:', error);
            window.location.href = url;
        }
    }

    function updateActiveLink(url) {
    const navLinks = document.querySelectorAll('.nav-link');
    const targetPage = url.split('/').pop().split('?')[0];

    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (!linkHref) return;

        const linkPage = linkHref.split('/').pop();
        
        if (targetPage === linkPage) {
            link.classList.add('text-blue-600', 'nav-link-active');
            link.classList.remove('text-gray-400');
            
            const icon = link.querySelector('svg');
            if (icon) icon.classList.add('text-blue-600');
        } else {
            link.classList.remove('text-blue-600', 'nav-link-active');
            link.classList.add('text-gray-400');
            
            const icon = link.querySelector('svg');
            if (icon) icon.classList.remove('text-blue-600');
        }
    });
}
});
