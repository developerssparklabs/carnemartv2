<?php
function my_acf_admin_head()
{
?>
    <style type="text/css">
        .mensaje-importante {
            background-color: #fed6cf !important;
            color: #cf0618 !important;
        }
    </style>
<?php
}
add_action('acf/input/admin_head', 'my_acf_admin_head');
