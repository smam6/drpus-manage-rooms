<?php

use DrPlus\Utils\Options;

$general_search_no_results = Options::get_options( [
	'general_search_no_results'	=> __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'drplus' ),
] )['general_search_no_results'];
echo '<div class="empty-page">';
echo '<i class="empty-page-icon drplus-icon-search-cross"></i>';
echo '<p class="empty-page-text no-results">' . $general_search_no_results . '</p>';
echo '</div>';