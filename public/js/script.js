/* --- Logout Modal Logic --- */
document.addEventListener('DOMContentLoaded', function() {
    // Find elements
    const logoutButton = document.getElementById('logout-button');
    const logoutModalOverlay = document.getElementById('logout-modal-overlay');
    const logoutModalClose = document.getElementById('logout-modal-close');
    const logoutCancelButton = document.getElementById('logout-cancel-button');
    const logoutConfirmButton = document.getElementById('logout-confirm-button');
    const logoutForm = document.getElementById('logout-form');

    // Function to show the modal
    function showLogoutModal() {
        if (logoutModalOverlay) {
            logoutModalOverlay.style.display = 'flex';
        }
    }

    // Function to hide the modal
    function hideLogoutModal() {
        if (logoutModalOverlay) {
            logoutModalOverlay.style.display = 'none';
        }
    }

    // --- Add Event Listeners ---

    // When "Logout" in navbar is clicked:
    if (logoutButton) {
        logoutButton.addEventListener('click', function(event) {
            event.preventDefault(); // Stop any default action
            showLogoutModal();
        });
    }

    // When "Cancel" button is clicked:
    if (logoutCancelButton) {
        logoutCancelButton.addEventListener('click', hideLogoutModal);
    }

    // When 'X' (close) button is clicked:
    if (logoutModalClose) {
        logoutModalClose.addEventListener('click', hideLogoutModal);
    }

    // When the dark overlay (background) is clicked:
    if (logoutModalOverlay) {
        logoutModalOverlay.addEventListener('click', function(event) {
            if (event.target === logoutModalOverlay) {
                hideLogoutModal();
            }
        });
    }

    // When "Logout" (Confirm) button is clicked:
    if (logoutConfirmButton && logoutForm) {
        logoutConfirmButton.addEventListener('click', function() {
            // Submit the hidden form
            logoutForm.submit();
        });
    }

});