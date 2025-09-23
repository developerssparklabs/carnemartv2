<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (5 > $count) {
    return;
}

if (0 === $page) {
    $page = 1;
}
if (!isset($url)) {
    $url = add_query_arg(array());
}
if (eib2bpro_post('q')) {
    $url = add_query_arg(array('s' => eib2bpro_post('q')), $url);
}
?>

<?php

$per_page = eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10);

$pages = paginate_links(
    array(
        'base' => remove_query_arg('pg', $url) . '&pg=%#%',
        'format' => '?pg=%#%',
        'current' => max(1, $page),
        'total' => ceil($count / $per_page),
        'type' => 'array',
        'prev_next' => true,
        'prev_text' => '«',
        'next_text' => '»',
    )
);

$pagination = '<nav class="eib2bpro-navigation d-flex">';
$pagination .= '<div class="pt-3 mt-1 pl-1"><div class="btn-group">
<button type="button" class="btn bg-white btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
  ' . esc_attr(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)) . ' ' . esc_html__('records', 'eib2bpro') . '
</button>
<div class="dropdown-menu">
  <a class="dropdown-item" href="' . remove_query_arg('perpage', $url) . '&perpage=5">5</a>
  <a class="dropdown-item" href="' . remove_query_arg('perpage', $url) . '&perpage=10">10</a>
  <a class="dropdown-item" href="' . remove_query_arg('perpage', $url) . '&perpage=20">20</a>
  <a class="dropdown-item" href="' . remove_query_arg('perpage', $url) . '&perpage=50">50</a>
  <a class="dropdown-item" href="' . remove_query_arg('perpage', $url) . '&perpage=100">100</a>

</div>
</div></div>';

if (is_array($pages)) {
    $paged = (intval(get_query_var('pg')) === 0) ? 1 : intval(get_query_var('pg'));

    $pagination .= '<ul class="eib2bpro-Pagination pagination-sm ml-auto p-2 pagination">';

    foreach ($pages as $page) {
        $pagination .= "<li class='page-item'>" . eib2bpro_r(str_replace('page-numbers', 'page-link', $page)) . "</li>";
    }
}
$pagination .= '</ul></nav>';

echo eib2bpro_r($pagination);
?>