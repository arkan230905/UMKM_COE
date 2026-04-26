/* JavaScript untuk Retur Pembelian - Digunakan di kedua halaman */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success messages after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-success');
        alerts.forEach(function(alert) {
            if (bootstrap && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
    
    // Auto-scroll to new retur if exists
    const newReturRow = document.querySelector('tr.table-success');
    if (newReturRow) {
        setTimeout(function() {
            newReturRow.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            // Add a subtle animation to highlight the new row
            newReturRow.style.animation = 'pulse 2s ease-in-out';
        }, 500);
        
        console.log('New retur row found and highlighted');
    }
    
    // Handle refresh button
    const refreshButtons = document.querySelectorAll('[onclick*="reload"]');
    refreshButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;
            
            // Reload page
            setTimeout(function() {
                window.location.reload();
            }, 500);
        });
    });
    
    // Handle retur action forms
    document.querySelectorAll('.retur-action-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default submission first
            
            const button = form.querySelector('button[type="submit"]');
            const action = form.dataset.action;
            const formAction = form.action;
            
            console.log('Form submission intercepted:', {
                action: action,
                formAction: formAction,
                method: form.method,
                csrfToken: form.querySelector('input[name="_token"]')?.value
            });
            
            // Show confirmation
            const confirmMessage = `Yakin ingin mengubah status ke ${action}?`;
            
            if (confirm(confirmMessage)) {
                // Disable button to prevent double submission
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
                
                console.log('Submitting form...');
                
                // Submit form normally (remove preventDefault effect)
                form.submit();
            }
        });
    });
    
    // Handle other action forms with loading states
    const actionForms = document.querySelectorAll('.action-buttons form:not(.retur-action-form)');
    actionForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                // Show loading state
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
                submitButton.disabled = true;
                
                // Re-enable after 5 seconds as fallback
                setTimeout(function() {
                    if (submitButton.disabled) {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }
                }, 5000);
            }
        });
    });
    
    // Console logging untuk debugging
    console.log('Retur Pembelian JavaScript loaded');
    console.log('Total retur rows:', document.querySelectorAll('tbody tr').length);
    console.log('New retur exists:', !!newReturRow);
    console.log('Retur action forms found:', document.querySelectorAll('.retur-action-form').length);
});

// Function untuk handle form submit dengan konfirmasi (fallback untuk inline calls)
function handleFormSubmit(form, action) {
    console.log('Legacy handleFormSubmit called:', {
        action: action,
        formAction: form.action,
        method: form.method,
        csrfToken: form.querySelector('input[name="_token"]')?.value
    });
    
    // Show confirmation
    const confirmed = confirm(`Yakin ingin mengubah status ke ${action}?`);
    
    if (confirmed) {
        // Disable submit button to prevent double submission
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
        }
        
        // Allow form to submit normally
        return true;
    }
    
    return false;
}

// Function untuk AJAX requests (jika diperlukan)
function sendAjaxRequest(url, method = 'POST', data = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: method !== 'GET' ? JSON.stringify(data) : undefined
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        throw error;
    });
}