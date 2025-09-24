jQuery(document).ready(function($) {
                // Override confirm to always return true
                var originalConfirm = window.confirm;
                window.confirm = function() { return true; };

                $('.calculate-action').trigger('click');

                // Restore original confirm after a short delay
                setTimeout(function() {
                    window.confirm = originalConfirm;
                }, 1000);
            });