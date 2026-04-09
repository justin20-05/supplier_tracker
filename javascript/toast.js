/** Global guard to prevent double-initialization */
if (typeof window.toastInitialized === 'undefined') {
    window.toastInitialized = true;

    /**
     * Displays a toast notification
     * @param {string} message
     * @param {string} type 
     */
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-5 right-5 z-[100] flex flex-col gap-3';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        
        // Color mapping
        let bgColor = 'bg-green-600'; 
        if (type === 'warning') bgColor = 'bg-yellow-500';
        if (type === 'error') bgColor = 'bg-red-600';
        
        toast.className = `${bgColor} text-white px-6 py-3 rounded-2xl shadow-xl font-bold text-sm transform transition-all duration-300 translate-x-full flex items-center gap-3`;
        
        // Icon selection
        let icon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
        if (type === 'warning' || type === 'error') {
            icon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
        }

        toast.innerHTML = `${icon} <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-2');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
    
    function checkUrlMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('msg')) {
            const msg = urlParams.get('msg');
            switch(msg) {
                case 'added': showToast('Record added successfully!', 'success'); break;
                case 'updated': showToast('Record updated successfully!', 'success'); break;
                case 'deleted': showToast('Record removed successfully!', 'error'); break;
                case 'error': showToast('Something went wrong.', 'error'); break;
                case 'cat_deleted': showToast('Category removed successfully!', 'error'); break;
                case 'warning': showToast('Please check your input.', 'warning'); break;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', checkUrlMessages);
}