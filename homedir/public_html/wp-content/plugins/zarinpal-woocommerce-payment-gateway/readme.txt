=== افزونه پرداخت امن زرین‌پال برای ووکامرس (ZarinPal for WooCommerce) ===
author: Masoud Amini , Armin Zahedi
author URI: http://www.zarinpal.com/
Contributors: Amini7,ar4min
Tags: zarinpal, woocommerce, payment gateway, زرین‌پال, درگاه پرداخت
Requires at least: 5.8
Requires PHP: 7.0
Tested up to: 7.0
Stable tag: 5.1.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Accept online payments through the ZarinPal payment gateway, the leading Iranian payment service provider, directly in WooCommerce.

== Description ==
**ZarinPal Payment Gateway for WooCommerce** lets you easily set up the ZarinPal online payment gateway to accept payments for your WooCommerce store.

= Features =
 * Automatically adds the Iranian Rial, Toman, thousand-Rial, and thousand-Toman currencies to WooCommerce
 * Simple, user-friendly settings panel
 * Customizable messages for successful, cancelled, or failed payments
 * Displays the ZarinPal tracking code via a shortcode
 * Displays payment gateway errors
 * Optional sandbox (test) mode
 * Transaction refund support
 * Transaction detail lookup from the order screen
 * Choose whether the gateway fee is paid by the merchant or the customer

== Installation ==
1. Upload the `zarinpal-woocommerce-payment-gateway` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin under WooCommerce Settings / Payments

== Changelog ==
= 5.1.1 =
* Security: removed a hardcoded nonce bypass in the checkout payment-method-switch AJAX handler that allowed an unauthenticated request to skip nonce verification entirely

= 5.1.0 =
* Security: fixed a CSRF vulnerability in the manual transaction re-verification and transaction detail lookup admin actions
* Coding standards and security hardening pass (safe redirects, input sanitization, output escaping, translation domain)

= 5.0.17 =
* بهبود امنیت در فرآیند بازگشت از درگاه پرداخت
* بهبود پایداری در پردازش تراکنش‌ها

= 5.0.16 =
* افزودن ویژگی کسر کارمزد از خریدار
* انتخاب کسر کارمزد از پذیرنده یا خریدار در تنظیمات
* محاسبه خودکار کارمزد با استفاده از API زرین‌پال
* نمایش کارمزد در صفحه چک‌اوت
* پرداخت با مبلغ پیشنهادی و تضمین دریافت مبلغ دقیق
* تشخیص خودکار نوع کسر کارمزد و سینک با تنظیمات حساب
* رفع مشکلات سازگاری با HPOS (High-Performance Order Storage)
* بهبود نمایش کارمزد در خلاصه سفارش

= 5.0.1 =
* افزودن حالت تست (سندباکس)
* افزودن امکان استرداد تراکنش
* افزودن امکان مشاهده جزئیات تراکنش
* نمایش تراست لوگو زرین پال در پنل تنظیمات
* سازگاری با وردپرس 6.7.1 و ووکامرس 9.4.1
* تست شده بر روی ووکامرس 9.4.1

= 4.9.3 =
* بروزرسانی در ارسال شماره همراه و سازگاری با سایر پلاگین‌های دریافت شماره همراه.

= 4.9.2 =
* رفع مشکل ارسال شماره همراه با پیشوند‌های +98 و سایر نواقص آن‌ها.
* افزوده شدن لوگوی جدید زرین پال.

= 4.9.1 =
* هماهنگ‌سازی ارسال شماره همراه و ایمیل خریدار.
* هماهنگ‌سازی ارسال شماره سفارش `order_id`.

= 4.9 =
* تغییرات در بخش تنظیمات درگاه.
* بهبود عملکرد در صورت خطای اتصال به سرور زرین پال.
* سازگاری با نسخه جدید ووکامرس و وردپرس.

= 4.8.1 =
* بروزرسانی به نسخه جدید و رفع خطاها.

= 4.7.3 =
* هماهنگ‌سازی بخش واحد ارز با واحد ارز در زرین پال.
* ارتقا به وب سرویس جدید زرین پال.

= 4.7.2 =
* بروزرسانی واحد.

= 4.7.1 =
* رفع خطاها.

= 4.7 =
* بروزرسانی به وب سرویس جدید زرین پال.
* درج شماره همراه و ایمیل در درگاه بانکی.

= 4.6.1 =
* رفع خطای -۱ در برخی شرایط.

= 4.6 =
* بهینه‌سازی کدها و سازگار سازی با نسخه‌های جدید ووکامرس.

= 4.5 =
* افزودن تنظیم دستی درگاه برای انتقال به وب گیت و زرین گیت.
* تغییر نوع وب سرویس از SOAP به REST.
* رفع باگ تایید نشدن سفارش پس از پرداخت موفق در برخی حالات.

= 4.4 =
* اضافه شدن خودکار واحدهای پولی ریال، تومان، هزار ریال و هزار تومان به ووکامرس.

= 4.3.5 =
* افزونه به صورت پیش‌فرض روی درگاه مستقیم است.
* نمایش کد خطا.
* کاهش یک مرحله از پرداخت جهت سریع‌تر شدن فرآیند.

= 4.3.2 =
* رفع مشکل باز نشدن قسمت تنظیمات در ووکامرس جدید.

= 4.3 =
* رفع مشکل ریدایرکت پس از پرداخت.
