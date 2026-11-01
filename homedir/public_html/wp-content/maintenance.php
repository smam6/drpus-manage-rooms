<?php

//  ATTENTION!
//
//  DO NOT MODIFY THIS FILE BECAUSE IT WAS GENERATED AUTOMATICALLY,
//  SO ALL YOUR CHANGES WILL BE LOST THE NEXT TIME THE FILE IS GENERATED.
//  IF YOU REQUIRE TO APPLY CUSTOM MODIFICATIONS, PERFORM THEM IN THE FOLLOWING FILE:
//  /home/aghazga1/public_html/wp-content/maintenance/template.phtml


$protocol = $_SERVER['SERVER_PROTOCOL'];
if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
    $protocol = 'HTTP/1.0';
}

header("{$protocol} 503 Service Unavailable", true, 503);
header('Content-Type: text/html; charset=utf-8');
header('Retry-After: 600');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link rel="icon" href="https://aghazgarclinic.ir/wp-content/uploads/2026/03/cropped-logo1-32x32.png">
    <link rel="stylesheet" href="https://aghazgarclinic.ir/wp-content/maintenance/assets/styles.css?1784001348">
    <script src="https://aghazgarclinic.ir/wp-content/maintenance/assets/timer.js?1784001348"></script>
    <title>Scheduled Maintenance</title>
    <style>body {background-image: url("https://aghazgarclinic.ir/wp-content/maintenance/assets/images/bg.jpg?1784001348");}</style>
</head>

<body>

    <div class="container">

    <header class="header">
        <h1>The website is undergoing scheduled maintenance.</h1>
        <h2>Sorry for the inconvenience. Come back a bit later, we will be ready soon!</h2>
    </header>

    <!--START_TIMER_BLOCK-->
        <!--END_TIMER_BLOCK-->

    <!--START_SOCIAL_LINKS_BLOCK-->
    <section class="social-links">
                    <a class="social-links__link" href="https://www.facebook.com/cPanel" target="_blank" title="Facebook">
                <span class="icon"><img src="https://aghazgarclinic.ir/wp-content/maintenance/assets/images/facebook.svg?1784001348" alt="Facebook"></span>
            </a>
                    <a class="social-links__link" href="https://x.com/cPanel" target="_blank" title="Twitter">
                <span class="icon"><img src="https://aghazgarclinic.ir/wp-content/maintenance/assets/images/twitter.svg?1784001348" alt="Twitter"></span>
            </a>
                    <a class="social-links__link" href="https://instagram.com/cPanel" target="_blank" title="Instagram">
                <span class="icon"><img src="https://aghazgarclinic.ir/wp-content/maintenance/assets/images/instagram.svg?1784001348" alt="Instagram"></span>
            </a>
            </section>
    <!--END_SOCIAL_LINKS_BLOCK-->

</div>

<footer class="footer">
    <div class="footer__content">
        Powered by WP Toolkit    </div>
</footer>

</body>
</html>
