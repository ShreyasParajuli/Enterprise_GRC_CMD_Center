    </div><!-- Close flex-1 wrapper -->

    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3"></div>

    <!-- Global Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-brand-dark/50 backdrop-blur-sm transition-opacity" onclick="closeConfirmModal()"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-brand-dark/10">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-bold leading-6 text-brand-dark" id="modal-title">Confirm Action</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-brand-dark/70" id="modal-message">Are you sure you want to proceed with this action?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-brand-dark/5">
                        <button type="button" id="modal-confirm-btn" class="inline-flex w-full justify-center rounded-lg bg-brand-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 sm:ml-3 sm:w-auto transition-colors">Confirm</button>
                        <button type="button" onclick="closeConfirmModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-brand-dark shadow-sm ring-1 ring-inset ring-brand-dark/20 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      lucide.createIcons();
      
      // Toast System
      function showToast(message, type = 'success') {
          const container = document.getElementById('toast-container');
          const toast = document.createElement('div');
          
          let bgColor, iconColor, iconName;
          
          if (type === 'success') {
              bgColor = 'bg-white border-[#BBC7B6]';
              iconColor = 'text-[#BBC7B6]';
              iconName = 'check-circle';
          } else if (type === 'error') {
              bgColor = 'bg-white border-red-500';
              iconColor = 'text-red-500';
              iconName = 'alert-octagon';
          } else if (type === 'warning') {
              bgColor = 'bg-white border-orange-400';
              iconColor = 'text-orange-500';
              iconName = 'alert-triangle';
          } else {
              bgColor = 'bg-white border-blue-400';
              iconColor = 'text-blue-500';
              iconName = 'info';
          }
          
          toast.className = `flex items-center w-full max-w-xs p-4 text-[#232426] rounded-lg shadow-lg border-l-4 ${bgColor} transform transition-all duration-300 translate-y-10 opacity-0`;
          
          toast.innerHTML = `
              <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg bg-slate-50 ${iconColor}">
                  <i data-lucide="${iconName}" class="w-5 h-5"></i>
              </div>
              <div class="ml-3 text-sm font-semibold">${message}</div>
              <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                  <i data-lucide="x" class="w-4 h-4"></i>
              </button>
          `;
          
          container.appendChild(toast);
          lucide.createIcons({root: toast});
          
          // Animate in
          requestAnimationFrame(() => {
              toast.classList.remove('translate-y-10', 'opacity-0');
          });
          
          // Auto remove
          setTimeout(() => {
              toast.classList.add('opacity-0', 'translate-x-10');
              setTimeout(() => toast.remove(), 300);
          }, 5000);
      }
      
      // Global Modal System
      let currentConfirmCallback = null;
      
      function confirmAction(message, callback) {
          document.getElementById('modal-message').innerText = message;
          document.getElementById('confirmModal').classList.remove('hidden');
          currentConfirmCallback = callback;
      }
      
      function closeConfirmModal() {
          document.getElementById('confirmModal').classList.add('hidden');
          currentConfirmCallback = null;
      }
      
      document.getElementById('modal-confirm-btn').addEventListener('click', function() {
          if(currentConfirmCallback) {
              closeConfirmModal();
              currentConfirmCallback();
          }
      });

      // Loading States for forms
      document.querySelectorAll('form').forEach(form => {
          form.addEventListener('submit', function(e) {
              if (this.dataset.preventLoading) return; // Opt-out via data attribute
              const btn = this.querySelector('button[type="submit"]');
              if (btn && !btn.disabled) {
                  btn.disabled = true;
                  btn.dataset.originalText = btn.innerHTML;
                  btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin inline"></i> Processing...';
                  lucide.createIcons({root: btn});
              }
          });
      });
    </script>
    
    <?php
    // Read session flashes into Toasts automatically
    $toastTypes = ['success', 'error', 'warning', 'info'];
    $toastScript = "";
    foreach($toastTypes as $type) {
        $sessionKey = $type;
        // Map old generic 'success_msg' and 'error_msg'
        if ($type === 'success' && isset($_SESSION['success_msg'])) { $_SESSION['success'] = $_SESSION['success_msg']; unset($_SESSION['success_msg']); }
        if ($type === 'error' && isset($_SESSION['error_msg'])) { $_SESSION['error'] = $_SESSION['error_msg']; unset($_SESSION['error_msg']); }
        
        if (isset($_SESSION[$sessionKey])) {
            $msg = addslashes($_SESSION[$sessionKey]);
            $toastScript .= "showToast('{$msg}', '{$type}');\n";
            unset($_SESSION[$sessionKey]);
        }
    }
    if (!empty($toastScript)) {
        echo "<script>document.addEventListener('DOMContentLoaded', function() { {$toastScript} });</script>";
    }
    ?>

    <!-- Custom Script -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
