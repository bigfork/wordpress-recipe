<?php

if (!class_exists('GFForms')) {
    return;
}

// Fix for https://github.com/roots/acorn/issues/198
add_action('admin_footer', function () {
    if (!isset($_GET['page']) || $_GET['page'] !== 'gf_edit_forms') {
        return;
    }

    echo <<<HTML
<script type="text/javascript">
    document.querySelector('select[name="_gform_setting_event"]')?.setAttribute('id', 'event');
</script>
HTML;
}, 100);
