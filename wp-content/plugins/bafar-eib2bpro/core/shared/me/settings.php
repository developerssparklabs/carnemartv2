<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<div class="p-5 mt-4 eib2bpro-me-settings-screen">
    <?php eib2bpro_form(['do' => 'me-settings-save']) ?>
    <?php eib2bpro_ui('media', 'eib2bpro_avatar', get_user_meta(get_current_user_id(), 'eib2bpro_avatar', true))
    ?>
    <div class="text-right mt-4"><?php eib2bpro_save() ?></div>
    </form>
</div>