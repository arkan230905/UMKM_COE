// Auto Reset Data System
// Untuk multi-perusahaan - reset data saat user berbeda login

class AutoResetSystem {
    constructor() {
        this.currentUser = null;
        this.lastUserId = null;
        this.sessionKey = 'auto_reset_user_id';
        this.init();
    }

    init() {
        // Cek user setiap 5 detik
        setInterval(() => {
            this.checkUserChange();
        }, 5000);

        // Cek saat page load
        this.checkUserChange();
        
        // Cek saat page focus
        window.addEventListener('focus', () => {
            this.checkUserChange();
        });
    }

    async checkUserChange() {
        try {
            const response = await fetch('/auto-reset/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.handleResetResult(result);
            }
        } catch (error) {
            console.error('Auto Reset Error:', error);
        }
    }

    handleResetResult(result) {
        switch (result.action) {
            case 'data_reset':
                this.showSuccessMessage('Data berhasil direset untuk perusahaan baru!');
                this.logResetEvent(result);
                // Reload page setelah 2 detik
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                break;
                
            case 'saved_session':
                console.log('Session saved for user:', result.current_user_id);
                break;
                
            case 'no_action':
                console.log('Same user, no reset needed');
                break;
        }
    }

    showSuccessMessage(message) {
        // Buat notifikasi sukses
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        notification.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <strong><i class="fas fa-check-circle"></i> Sukses!</strong> ${message}
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove setelah 5 detik
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    logResetEvent(result) {
        console.log('Auto Reset Event:', {
            action: result.action,
            previous_user_id: result.previous_user_id,
            current_user_id: result.current_user_id,
            timestamp: new Date().toISOString()
        });
    }

    // Method untuk manual reset (jika diperlukan)
    async manualReset() {
        if (confirm('Apakah Anda yakin ingin mereset semua data?')) {
            try {
                const response = await fetch('/auto-reset/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                this.handleResetResult(result);
            } catch (error) {
                console.error('Manual Reset Error:', error);
                alert('Gagal mereset data. Silakan coba lagi.');
            }
        }
    }

    // Method untuk melihat history reset
    async viewResetHistory() {
        try {
            const response = await fetch('/auto-reset/history');
            const result = await response.json();
            
            if (result.success) {
                this.showHistoryModal(result.data);
            }
        } catch (error) {
            console.error('Get History Error:', error);
        }
    }

    showHistoryModal(historyData) {
        // Buat modal untuk menampilkan history
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">History Auto Reset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>User Sebelumnya</th>
                                        <th>User Saat Ini</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${historyData.map(log => `
                                        <tr>
                                            <td>${new Date(log.timestamp).toLocaleString('id-ID')}</td>
                                            <td>${log.previous_user_id || 'N/A'}</td>
                                            <td>${log.current_user_id}</td>
                                            <td><span class="badge bg-info">${log.message}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Tampilkan modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Hapus modal saat ditutup
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }
}

// Initialize system saat page load
document.addEventListener('DOMContentLoaded', function() {
    window.autoResetSystem = new AutoResetSystem();
    
    });
