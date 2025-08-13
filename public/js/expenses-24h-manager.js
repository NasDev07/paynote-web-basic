/**
 * Expenses 24-Hour Manager
 * Mengelola fitur edit/delete yang expired setelah 24 jam
 */

class Expenses24HManager {
    constructor() {
        this.init();
    }

    init() {
        this.updateAllButtons();
        this.startPeriodicUpdate();
        this.startCountdownUpdate();
        this.addExpiryWarnings();
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
        const editDeleteButtons = document.querySelectorAll('.edit-delete-buttons');
        
        editDeleteButtons.forEach(buttonGroup => {
            this.updateButtonGroup(buttonGroup);
        });
    }

    // Update grup button individual
    updateButtonGroup(buttonGroup) {
        const createdAt = buttonGroup.getAttribute('data-created');
        const expenseId = buttonGroup.getAttribute('data-expense-id');
        const editBtn = buttonGroup.querySelector('.edit-btn');
        const deleteBtn = buttonGroup.querySelector('.delete-btn');
        const expiredMsg = buttonGroup.parentElement.querySelector('.expired-message');
        
        if (this.isExpired(createdAt)) {
            this.handleExpiredButtons(expenseId, editBtn, deleteBtn, expiredMsg);
        } else {
            this.handleActiveButtons(createdAt, expenseId, editBtn, deleteBtn, expiredMsg);
        }
    }

    // Handle button yang sudah expired
    handleExpiredButtons(expenseId, editBtn, deleteBtn, expiredMsg) {
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
        this.disableModalDeleteButton(expenseId);
    }

    // Handle button yang masih aktif
    handleActiveButtons(createdAt, expenseId, editBtn, deleteBtn, expiredMsg) {
        const remaining = this.getRemainingTime(createdAt);
        
        // Pastikan button terlihat
        if (editBtn) editBtn.style.display = 'inline-block';
        if (deleteBtn) deleteBtn.style.display = 'inline-block';
        if (expiredMsg) expiredMsg.style.display = 'none';
        
        // Update countdown di modal
        this.updateModalCountdown(expenseId, remaining);
        
        // Add warning style jika tinggal sedikit waktu
        this.addWarningStyle(remaining, editBtn, deleteBtn);
    }

    // Disable button delete di modal
    disableModalDeleteButton(expenseId) {
        const modal = document.querySelector(`#deleteExpenseModal${expenseId}`);
        if (modal) {
            const modalDeleteBtn = modal.querySelector('.modal-delete-btn');
            const deleteForm = modal.querySelector('.delete-form');
            const countdownInfo = modal.querySelector('.countdown-info');
            const countdownText = modal.querySelector('.countdown-text');
            
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
    updateModalCountdown(expenseId, remaining) {
        const modal = document.querySelector(`#deleteExpenseModal${expenseId}`);
        if (modal && remaining) {
            const countdownInfo = modal.querySelector('.countdown-info');
            const countdownText = modal.querySelector('.countdown-text');
            
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
        
        if (editBtn) {
            editBtn.classList.remove('btn-warning', 'btn-outline-warning', 'pulse-animation');
            
            if (isVeryNear) {
                editBtn.classList.add('btn-outline-warning', 'pulse-animation');
                editBtn.title = `Edit (SEGERA EXPIRED: ${this.formatTimeRemaining(remaining)})`;
            } else if (isNearExpiry) {
                editBtn.classList.add('btn-outline-warning');
                editBtn.title = `Edit (${this.formatTimeRemaining(remaining)} tersisa)`;
            } else {
                editBtn.classList.add('btn-warning');
                editBtn.title = 'Edit';
            }
        }
        
        if (deleteBtn) {
            deleteBtn.classList.remove('btn-danger', 'btn-outline-danger', 'pulse-animation');
            
            if (isVeryNear) {
                deleteBtn.classList.add('btn-outline-danger', 'pulse-animation');
                deleteBtn.title = `Hapus (SEGERA EXPIRED: ${this.formatTimeRemaining(remaining)})`;
            } else if (isNearExpiry) {
                deleteBtn.classList.add('btn-outline-danger');
                deleteBtn.title = `Hapus (${this.formatTimeRemaining(remaining)} tersisa)`;
            } else {
                deleteBtn.classList.add('btn-danger');
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
            const countdownTexts = document.querySelectorAll('.countdown-text');
            countdownTexts.forEach(text => {
                const modal = text.closest('.modal');
                if (modal && modal.classList.contains('show')) { // Hanya update jika modal terbuka
                    const expenseId = modal.id.replace('deleteExpenseModal', '');
                    const buttonGroup = document.querySelector(`[data-expense-id="${expenseId}"]`);
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

    // Add expiry warnings
    addExpiryWarnings() {
        setInterval(() => {
            this.updateAllButtons();
        }, 30000); // Update setiap 30 detik untuk warning
    }

    // Method untuk force refresh (jika diperlukan)
    forceRefresh() {
        this.updateAllButtons();
    }

    // Method untuk cek status spesifik expense
    getExpenseStatus(expenseId) {
        const buttonGroup = document.querySelector(`[data-expense-id="${expenseId}"]`);
        if (!buttonGroup) return null;
        
        const createdAt = buttonGroup.getAttribute('data-created');
        const remaining = this.getRemainingTime(createdAt);
        const expired = this.isExpired(createdAt);
        
        return {
            expenseId,
            createdAt,
            remaining,
            expired,
            canEdit: !expired,
            canDelete: !expired,
            timeLeft: remaining ? this.formatTimeRemaining(remaining) : '0s'
        };
    }

    // Method untuk get semua status
    getAllExpenseStatuses() {
        const buttonGroups = document.querySelectorAll('.edit-delete-buttons');
        const statuses = [];
        
        buttonGroups.forEach(buttonGroup => {
            const expenseId = buttonGroup.getAttribute('data-expense-id');
            const status = this.getExpenseStatus(expenseId);
            if (status) {
                statuses.push(status);
            }
        });
        
        return statuses;
    }

    // Method untuk show notification jika ada yang akan expired
    showExpiryNotifications() {
        const statuses = this.getAllExpenseStatuses();
        const nearExpiry = statuses.filter(status => 
            !status.expired && 
            status.remaining && 
            status.remaining.total <= (30 * 60 * 1000) // 30 menit
        );
        
        if (nearExpiry.length > 0) {
            const message = nearExpiry.length === 1 
                ? `1 pengeluaran akan expired dalam ${nearExpiry[0].timeLeft}`
                : `${nearExpiry.length} pengeluaran akan expired segera`;
                
            this.showToast(message, 'warning');
        }
    }

    // Method untuk show toast notification
    showToast(message, type = 'info') {
        // Create toast element jika belum ada
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
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
        `;
        toast.innerHTML = `
            <i class="fas fa-${type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
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

    // Method untuk debug/logging
    logStatus() {
        const statuses = this.getAllExpenseStatuses();
        console.log('Expenses Status:', statuses);
        return statuses;
    }
}

// CSS untuk animations
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
    }

    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
        transform: scale(1.05);
    }

    .countdown-info {
        font-size: 0.9em;
    }

    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }

    .pulse-animation {
        animation: pulse 1.5s infinite;
    }

    .toast-container .alert {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Loading state untuk buttons */
    .btn-loading {
        position: relative;
        pointer-events: none;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Hover effects untuk expired items */
    .expired-message:hover {
        background-color: #e9ecef;
        cursor: help;
    }
`;
document.head.appendChild(style);

// Initialize saat DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize manager
    window.expensesManager = new Expenses24HManager();
    
    // Show notifications setiap 10 menit
    setInterval(() => {
        window.expensesManager.showExpiryNotifications();
    }, 10 * 60 * 1000);
    
    // Log status untuk debugging (bisa dikomentari di production)
    if (console && console.log) {
        setTimeout(() => {
            console.log('Expenses 24H Manager initialized');
            // window.expensesManager.logStatus();
        }, 1000);
    }
});

// Export untuk digunakan di tempat lain jika diperlukan
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Expenses24HManager;
}