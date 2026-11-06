<div class="map-popup-overlay"></div>
<section class="map-popup" role="dialog" aria-modal="true" aria-labelledby="map-popup-title">
	<header class="map-popup-head">
		<button class="map-popup-close" aria-label="<?php esc_attr_e( 'Close', 'drplus' ) ?>" title="<?php esc_attr_e( 'Close', 'drplus' ) ?>">
			<i class="drplus-icon-cross" aria-hidden="true"></i>
		</button>

		<h4 id="map-popup-title" class="map-popup-title"><?php esc_html_e( 'View location on map', 'drplus' ) ?></h4>

		<button class="map-popup-maximize" aria-label="<?php esc_attr_e( 'View larger map', 'drplus' ) ?>" title="<?php esc_attr_e( 'Fullscreen', 'drplus' ) ?>">
			<i class="drplus-icon-maximize-2" aria-hidden="true"></i>
		</button>
	</header>

	<main class="map-popup-body">
		<iframe
			name="map-popup-iframe"
			class="map-popup-iframe"
			frameborder="0"
			marginheight="0" 
            marginwidth="0"
			scrolling="auto"
			loading="lazy"
			referrerpolicy="no-referrer-when-downgrade"
			sandbox="allow-scripts allow-same-origin allow-popups allow-forms"
			allowfullscreen
		></iframe>
	</main>
</section>