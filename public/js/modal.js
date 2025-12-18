/**
 * Professional Modal Dialog System
 * Replaces browser's alert(), confirm(), and prompt() with custom styled modals
 */

const Modal = {
        /**
         * Show a confirmation dialog
         * @param {string} message - The message to display
         * @param {string} type - 'danger', 'warning', 'success', or 'info'
         * @returns {Promise<boolean>} - Resolves to true if confirmed, false if cancelled
         */
        confirm(message, type = 'warning') {
            return new Promise((resolve) => {
                const modal = this._createModal(message, type, [
                    { text: 'Cancel', class: 'btn-secondary', value: false },
                    { text: 'Confirm', class: this._getButtonClass(type), value: true }
                ]);

                modal.addEventListener('click', (e) => {
                    if (e.target.classList.contains('modal-btn')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = e.target.dataset.value === 'true';
                        this._closeModal(modal);
                        resolve(value);
                    }
                });
            });
        },

        /**
         * Show a prompt dialog for text input
         * @param {string} message - The message to display
         * @param {string} defaultValue - Default input value
         * @param {string} placeholder - Input placeholder
         * @returns {Promise<string|null>} - Resolves to input value or null if cancelled
         */
        prompt(message, defaultValue = '', placeholder = '') {
            return new Promise((resolve) => {
                const inputHtml = `<input type="text" class="modal-input" value="${this._escape(defaultValue)}" placeholder="${this._escape(placeholder)}" autofocus>`;

                const modal = this._createModal(message, 'info', [
                    { text: 'Cancel', class: 'btn-secondary', value: null },
                    { text: 'OK', class: 'btn-primary', value: 'submit' }
                ], inputHtml);

                const input = modal.querySelector('.modal-input');
                input.focus();
                input.select();

                const handleSubmit = () => {
                    const value = input.value.trim();
                    this._closeModal(modal);
                    resolve(value || null);
                };

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleSubmit();
                    } else if (e.key === 'Escape') {
                        this._closeModal(modal);
                        resolve(null);
                    }
                });

                modal.addEventListener('click', (e) => {
                    if (e.target.classList.contains('modal-btn')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.dataset.value === 'submit') {
                            handleSubmit();
                        } else {
                            this._closeModal(modal);
                            resolve(null);
                        }
                    }
                });
            });
        },

        /**
         * Show an alert dialog
         * @param {string} message - The message to display
         * @param {string} type - 'danger', 'warning', 'success', or 'info'
         * @returns {Promise<void>}
         */
        alert(message, type = 'info') {
            return new Promise((resolve) => {
                const modal = this._createModal(message, type, [
                    { text: 'OK', class: 'btn-primary', value: true }
                ]);

                modal.addEventListener('click', (e) => {
                    if (e.target.classList.contains('modal-btn')) {
                        e.preventDefault();
                        e.stopPropagation();
                        this._closeModal(modal);
                        resolve();
                    }
                });
            });
        },

        /**
         * Create modal element
         * @private
         */
        _createModal(message, type, buttons, extraContent = '') {
            const modal = document.createElement('div');
            modal.className = 'custom-modal-overlay';

            const icon = this._getIcon(type);
            const color = this._getColor(type);

            modal.innerHTML = `
      <div class="custom-modal">
        <div class="custom-modal-header" style="background-color: ${color}">
          <i class="${icon}"></i>
        </div>
        <div class="custom-modal-body">
          <p class="custom-modal-message">${this._escape(message)}</p>
          ${extraContent}
        </div>
        <div class="custom-modal-footer">
          ${buttons.map(btn => 
            `<button class="btn ${btn.class} modal-btn" data-value="${btn.value}">${btn.text}</button>`
          ).join('')}
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('show'), 10);
    
    // Prevent modal from closing when clicking inside modal content (but not buttons)
    const modalContent = modal.querySelector('.custom-modal');
    if (modalContent) {
      modalContent.addEventListener('click', (e) => {
        // Only stop propagation if NOT clicking a button
        if (!e.target.classList.contains('modal-btn')) {
          e.stopPropagation();
        }
      });
    }
    
    return modal;
  },

  /**
   * Close and remove modal
   * @private
   */
  _closeModal(modal) {
    modal.classList.remove('show');
    setTimeout(() => modal.remove(), 300);
  },

  /**
   * Get button class based on type
   * @private
   */
  _getButtonClass(type) {
    const classes = {
      danger: 'btn-danger',
      warning: 'btn-warning',
      success: 'btn-success',
      info: 'btn-primary'
    };
    return classes[type] || 'btn-primary';
  },

  /**
   * Get icon based on type
   * @private
   */
  _getIcon(type) {
    const icons = {
      danger: 'fa-solid fa-triangle-exclamation',
      warning: 'fa-solid fa-exclamation-circle',
      success: 'fa-solid fa-check-circle',
      info: 'fa-solid fa-info-circle'
    };
    return icons[type] || icons.info;
  },

  /**
   * Get color based on type
   * @private
   */
  _getColor(type) {
    const colors = {
      danger: '#dc3545',
      warning: '#ffc107',
      success: '#28a745',
      info: '#17a2b8'
    };
    return colors[type] || colors.info;
  },

  /**
   * Escape HTML to prevent XSS
   * @private
   */
  _escape(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
};

// Make it globally available
window.Modal = Modal;