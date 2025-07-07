import './bootstrap';

// Alpine.js store for sidebar
document.addEventListener('alpine:init', () => {
    Alpine.store('sidebar', {
        isOpen: true,
        
        toggle() {
            this.isOpen = !this.isOpen;
        },
        
        close() {
            this.isOpen = false;
        },
        
        open() {
            this.isOpen = true;
        },
        
        groupIsCollapsed(group) {
            return false;
        },
        
        toggleGroup(group) {
            // Implementation for group toggling
        }
    });
    
    Alpine.store('theme', 'system');
});