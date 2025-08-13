/**
 * Universal 24-Hour Manager
 * Mengelola fitur edit/delete yang expired setelah 24 jam
 * Bisa digunakan untuk expenses dan incomes
 */

class Universal24HManager {
    constructor(options = {}) {
        this.options = {
            type: options.type || 'expense', // 'expense' atau 'income'
            buttonGroupClass: options.buttonGroupClass || '.edit-delete-buttons',
            expiredMessageClass: options.expiredMessageClass || '.expired-message',
            editBtnClass: options.editBtnClass || '.edit-btn',
            deleteBtnClass: options.deleteBtnClass || '.delete-btn',
            modalDeleteBtnClass: options.modalDeleteBtnClass || '.modal-delete-btn',
            deleteFormClass: options.deleteFormClass || '.delete-form',
            countdownInfoClass: options.countdownInfoClass || '.countdown-info',
            countdownTextClass: options.countdownTextClass || '.countdown-text',
            idAttribute: options.idAttribute || 'data-' + this.options.type + '-id',
            modalPrefix: options.modalPrefix || 'delete' + this.capitalize(this.options.type) + 'Modal',
            ...options
        };
        
        this.init();
    }

    init() {
        this.updateAllButtons();
        this.startPeriodicUpdate();
        this.startCountdownUpdate();
        this.showInitialNotifications();
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Cek apakah sudah lebih dari 24 jam
    isExpired(createdAt) {
        const created = new Date(createdAt);
        const now = new Date();
        const diffInHours = (now - created) / (1000 * 60 * 60);
        return diffInHours >= 24;
    }

    // Hitung sisa waktu
    getRemainingTime(createdAt) {
        const created = new Date(createdAt);
        const expiry = new Date(created.getTime() + (24 * 60 * 60 * 1000));
        const now = new Date();
        const remaining = expiry - now;
        
        if (remaining <= 0) return null;
        
        const hours = Math.floor(remaining / (1000 * 60 * 60));
        const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
        
        return { hours, minutes, seconds, total: remaining };
    }

    // Format waktu yang tersisa
    formatTimeRemaining(timeObj) {
        if (!timeObj) return '';
        
        if (timeObj.hours > 0) {
            return `${timeObj.hours}j ${timeObj.minutes}m`;
        } else if (timeObj.minutes > 0) {
            return `${timeObj.minutes}m ${timeObj.seconds}s`;
        } else {
            return `${timeObj.seconds}s`;
        }
    }

    // Update semua button
    updateAllButtons() {
        const editDeleteButtons = document.querySelectorAll(this.options.buttonGroupClass);
        
        editDeleteButtons.forEach(buttonGroup => {
            this.updateButtonGroup(buttonGroup);
        });
    }

    // Update grup button individual
    updateButtonGroup(buttonGroup) {
        const createdAt = buttonGroup.getAttribute('data-created');
        const itemId = buttonGroup.getAttribute(this.options.idAttribute);
        const editBtn = buttonGroup.querySelector(this.options.editBtnClass);
        const deleteBtn = buttonGroup.querySelector(this.options.deleteBtnClass);
        const expiredMsg = buttonGroup.parentElement.querySelector(this.options.expiredMessageClass);
        
        if (this.isExpired(createdAt)) {
            this.handleExpiredButtons(itemId, editBtn, deleteBtn, expiredMsg);
        } else {
            this.handleActiveButtons(createdAt, itemId, editBtn, deleteBtn, expiredMsg);
        }
    }

    // Handle button yang sudah expired
    handleExpiredButtons(itemId, editBtn, deleteBtn, expiredMsg) {
        // Sembunyikan button edit dan delete
        if (editBtn) {
            editBtn.style.display = 'none';
            editBtn.disabled = true;
        }
        if (deleteBtn) {
            deleteBtn.style.display = 'none';
            deleteBtn.disabled = true;
        }
        if (expiredMsg) {
            expiredMsg.style.display = 'block';
        }
        
        // Disable modal delete button
        this.disableModalDeleteButton(itemId);
    }

    // Handle button yang masih aktif
    handleActiveButtons(createdAt, itemId, editBtn, deleteBtn, expiredMsg) {
        const remaining = this.getRemainingTime(createdAt);
        
        // Pastikan button terlihat
        if (editBtn) editBtn.style.display = 'inline-block';
        if (deleteBtn) deleteBtn.style.display = 'inline-block';
        if (expiredMsg) expiredMsg.style.display = 'none';
        
        // Update countdown di modal
        this.updateModalCountdown(itemId, remaining);
        
        // Add warning style jika tinggal sedikit waktu
        this.addWarningStyle(remaining, editBtn, deleteBtn);
    }

    // Disable button delete di modal
    disableModalDeleteButton(itemId) {
        const modal = document.querySelector(`#${this.options.modalPrefix}${itemId}`);
        if (modal) {
            const modalDeleteBtn = modal.querySelector(this.options.modalDeleteBtnClass);
            const deleteForm = modal.querySelector(this.options.deleteFormClass);
            const countdownInfo = modal.querySelector(this.options.countdownInfoClass);
            const countdownText = modal.querySelector(this.options.countdownTextClass);
            
            if (modalDeleteBtn) {
                modalDeleteBtn.disabled = true;
                modalDeleteBtn.textContent = 'Expired (24 jam terlewati)';
                modalDeleteBtn.classList.remove('btn-danger');
                modalDeleteBtn.classList.add('btn-secondary');
            }
            
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Tidak dapat menghapus data yang sudah lebih dari 24 jam!');
                });
            }
            
            if (countdownInfo && countdownText) {
                countdownInfo.style.display = 'block';
                countdownInfo.classList.remove('alert-info');
                countdownInfo.classList.add('alert-danger');
                countdownText.textContent = 'Waktu edit/hapus telah habis (lebih dari 24 jam)';
            }
        }
    }

    // Update countdown di modal
    updateModalCountdown(itemId, remaining) {
        const modal = document.querySelector(`#${this.options.modalPrefix}${itemId}`);
        if (modal && remaining) {
            const countdownInfo = modal.querySelector(this.options.countdownInfoClass);
            const countdownText = modal.querySelector(this.options.countdownTextClass);
            
            if (countdownInfo && countdownText) {
                countdownInfo.style.display = 'block';
                
                if (remaining.total <= (2 * 60 * 60 * 1000)) { // Kurang dari 2 jam
                    countdownInfo.classList.remove('alert-info');
                    countdownInfo.classList.add('alert-warning');
                } else {
                    countdownInfo.classList.remove('alert-warning');
                    countdownInfo.classList.add('alert-info');
                }
                
                countdownText.textContent = `Waktu edit/hapus tersisa: ${this.formatTimeRemaining(remaining)}`;
            }
        }
    }

    // Tambah warning style
    addWarningStyle(remaining, editBtn, deleteBtn) {
        if (!remaining) return;
        
        const isNearExpiry = remaining.total <= (2 * 60 * 60 * 1000); // Kurang dari 2 jam
        const isVeryNear = remaining.total <= (30 * 60 * 1000); // Kurang dari 30 menit
        
        // Tentukan warna berdasarkan tipe
        const warningClass = this.options.type === 'income' ? 'btn-outline-warning' : 'btn-outline-warning';
        const dangerClass = this.options.type === 'income' ? 'btn-outline-danger' : 'btn-outline-danger';
        const normalEditClass = this.options.type === 'income' ? 'btn-warning' : 'btn-warning';
        const normalDeleteClass = this.options.type === 'income' ? 'btn-danger' : 'btn-danger';
        
        if (editBtn) {
            editBtn.classList.remove('btn-warning', 'btn-outline-warning', 'pulse-animation');
            
            if (isVeryNear) {
                editBtn.classList.add(warningClass, 'pulse-animation');
                editBtn.title = `Edit (SEGERA EXPIRED: ${this.formatTimeRemaining(remaining)})`;
            } else if (isNearExpiry) {
                editBtn.classList.add(warningClass);
                editBtn.title = `Edit (${this.formatTimeRemaining(remaining)} tersisa)`;
            } else {
                editBtn.classList.add(normalEditClass);
                editBtn.title = 'Edit';
            }
        }
        
        if (deleteBtn) {
            deleteBtn.classList.remove('btn-danger', 'btn-outline-danger', 'pulse-animation');
            
            if (isVeryNear) {
                deleteBtn.classList.add(dangerClass, 'pulse-animation');
                deleteBtn.title = `Hapus (SEGERA EXPIRED: ${this.formatTimeRemaining(remaining)})`;
            } else if (isNearExpiry) {
                deleteBtn.classList.add(dangerClass);
                deleteBtn.title = `Hapus (${this.formatTimeRemaining(remaining)} tersisa)`;
            } else {
                deleteBtn.classList.add(normalDeleteClass);
                deleteBtn.title = 'Hapus';
            }
        }
    }

    // Start update berkala
    startPeriodicUpdate() {
        // Update setiap menit
        setInterval(() => {
            this.updateAllButtons();
        }, 60000);
    }

    // Start countdown update (untuk detik)
    startCountdownUpdate() {
        setInterval(() => {
            const countdownTexts = document.querySelectorAll(this.options.countdownTextClass);
            countdownTexts.forEach(text => {
                const modal = text.closest('.modal');
                if (modal && modal.classList.contains('show')) { // Hanya update jika modal terbuka
                    const itemId = modal.id.replace(this.options.modalPrefix, '');
                    const buttonGroup = document.querySelector(`[${this.options.idAttribute}="${itemId}"]`);
                    if (buttonGroup) {
                        const createdAt = buttonGroup.getAttribute('data-created');
                        const remaining = this.getRemainingTime(createdAt);
                        if (remaining) {
                            text.textContent = `Waktu edit/hapus tersisa: ${this.formatTimeRemaining(remaining)}`;
                        } else {
                            text.textContent = 'Waktu edit/hapus telah habis!';
                            const countdownInfo = text.parentElement;
                            countdownInfo.classList.remove('alert-info', 'alert-warning');
                            countdownInfo.classList.add('alert-danger');
                        }
                    }
                }
            });
        }, 1000); // Update setiap detik untuk countdown yang akurat
    }

    // Show notifications untuk items yang akan expired
    showExpiryNotifications() {
        const buttonGroups = document.querySelectorAll(this.options.buttonGroupClass);
        const nearExpiry = [];
        
        buttonGroups.forEach(buttonGroup => {
            const createdAt = buttonGroup.getAttribute('data-created');
            const itemId = buttonGroup.getAttribute(this.options.idAttribute);
            const remaining = this.getRemainingTime(createdAt);
            
            if (remaining && remaining.total <= (30 * 60 * 1000)) { // 30 menit
                nearExpiry.push({
                    id: itemId,
                    remaining: remaining,
                    timeLeft: this.formatTimeRemaining(remaining)
                });
            }
        });
        
        if (nearExpiry.length > 0) {
            const itemType = this.options.type === 'income' ? 'pemasukan' : 'pengeluaran';
            const message = nearExpiry.length === 1 
                ? `1 ${itemType} akan expired dalam ${nearExpiry[0].timeLeft}`
                : `${nearExpiry.length} ${itemType} akan expired segera`;
                
            this.showToast(message, 'warning');
        }
    }

    // Show initial notifications
    showInitialNotifications() {
        // Delay sedikit untuk memastikan DOM sudah ready
        setTimeout(() => {
            this.showExpiryNotifications();
        }, 2000);
        
        // Kemudian show setiap 10 menit
        setInterval(() => {
            this.showExpiryNotifications();
        }, 10 * 60 * 1000);
    }

    // Method untuk show toast notification
    showToast(message, type = 'info') {
        // Create toast container jika belum ada
        let toastContainer = document.querySelector('.toast-container-24h');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container-24h';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            `;
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show`;
        toast.style.cssText = `
            margin-bottom: 10px;
            min-width: 300px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        `;
        
        const icon = type === 'warning' ? 'exclamation-triangle' : 
                    type === 'danger' ? 'exclamation-circle' : 'info-circle';
        
        toast.innerHTML = `
            <i class="fas fa-${icon}"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove setelah 5 detik
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }

    // Method untuk get status semua items
    getAllItemStatuses() {
        const buttonGroups = document.querySelectorAll(this.options.buttonGroupClass);
        const statuses = [];
        
        buttonGroups.forEach(buttonGroup => {
            const itemId = buttonGroup.getAttribute(this.options.idAttribute);
            const createdAt = buttonGroup.getAttribute('data-created');
            const remaining = this.getRemainingTime(createdAt);
            const expired = this.isExpired(createdAt);
            
            statuses.push({
                id: itemId,
                type: this.options.type,
                createdAt,
                remaining,
                expired,
                canEdit: !expired,
                canDelete: !expired,
                timeLeft: remaining ? this.formatTimeRemaining(remaining) : '0s'
            });
        });
        
        return statuses;
    }

    // Method untuk force refresh
    forceRefresh() {
        this.updateAllButtons();
    }

    // Method untuk debug/logging
    logStatus() {
        const statuses = this.getAllItemStatuses();
        console.log(`${this.capitalize(this.options.type)} 24H Status:`, statuses);
        return statuses;
    }

    // Method untuk get summary statistics
    getSummaryStats() {
        const statuses = this.getAllItemStatuses();
        const total = statuses.length;
        const expired = statuses.filter(s => s.expired).length;
        const nearExpiry = statuses.filter(s => !s.expired && s.remaining && s.remaining.total <= (2 * 60 * 60 * 1000)).length;
        const urgent = statuses.filter(s => !s.expired && s.remaining && s.remaining.total <= (30 * 60 * 1000)).length;
        
        return {
            total,
            expired,
            active: total - expired,
            nearExpiry,
            urgent,
            type: this.options.type
        };
    }
}

// CSS untuk animations dan styling
const style = document.createElement('style');
style.textContent = `
    .edit-delete-buttons {
        transition: all 0.3s ease;
    }

    .expired-message {
        padding: 2px 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        font-size: 0.75rem;
    }

    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
        transform: scale(1.05);
    }

    .countdown-info {
        font-size: 0.9em;
    }

    @keyframes pulse {
        0% { 
            opacity: 1; 
            transform: scale(1); 
        }
        50% { 
            opacity: 0.7; 
            transform: scale(0.95); 
        }
        100% { 
            opacity: 1; 
            transform: scale(1); 
        }
    }

    .pulse-animation {
        animation: pulse 1.5s infinite;
    }

    .toast-container-24h .alert {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .expired-message:hover {
        background-color: #e9ecef;
        cursor: help;
    }

    .expired-message::after {
        content: attr(title);
        position: absolute;
        background: #333;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
    }

    .expired-message:hover::after {
        opacity: 1;
    }

    /* Styling khusus untuk different types */
    .income-expired {
        border-color: #28a745;
        color: #155724;
    }

    .expense-expired {
        border-color: #dc3545;
        color: #721c24;
    }
`;
document.head.appendChild(style);

// Factory function untuk membuat manager
function create24HManager(type, customOptions = {}) {
    const defaultOptions = {
        income: {
            type: 'income',
            idAttribute: 'data-income-id',
            modalPrefix: 'deleteIncomeModal'
        },
        expense: {
            type: 'expense',
            idAttribute: 'data-expense-id',
            modalPrefix: 'deleteExpenseModal'
        }
    };
    
    const options = {
        ...defaultOptions[type],
        ...customOptions
    };
    
    return new Universal24HManager(options);
}

// Auto-initialize berdasarkan page context
document.addEventListener('DOMContentLoaded', function() {
    // Detect page type berdasarkan URL atau elemen yang ada
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('income')) {
        window.incomes24HManager = create24HManager('income');
        console.log('Incomes 24H Manager initialized');
    } else if (currentPath.includes('expense')) {
        window.expenses24HManager = create24HManager('expense');
        console.log('Expenses 24H Manager initialized');
    }
    
    // Global helper function
    window.get24HStatus = function() {
        if (window.incomes24HManager) {
            console.log('Incomes Status:', window.incomes24HManager.getSummaryStats());
        }
        if (window.expenses24HManager) {
            console.log('Expenses Status:', window.expenses24HManager.getSummaryStats());
        }
    };
});

// Export untuk digunakan di tempat lain
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Universal24HManager, create24HManager };
}