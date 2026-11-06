# گزارش تست — مدیریت ظرفیت اتاق‌های کلینیک (فاز ۱)

## محیط تست
- تست‌های V1–V13   به یک runtime زنده WordPress (PHP + MySQL + سرور وب) نیاز دارند.
- در محیط  فعلی (sandbox) فقط static check قابل اجرا بود؛
  نتایج static check در ادامه آمده است. تست‌های runtime باید روی سرور staging
  با داده تست isolated (تاریخ 2099-05-20) اجرا شوند.

## نتایج static check (اجرا شده در sandbox)

| Check | Result |
|-------|--------|
| node --check assets/js/capacity.js | PASS |
| forbidden-pattern grep (extract/eval/curl_/mysqli_/PDO/dj3i_) در inc/capacity + assets | PASS (0 match) |
| escaping grep (echo بدون esc_*) | PASS (0 match) |
| Persian-hardcode در JS | PASS (0 رشته فارسی در capacity.js) |
| header docblock «Implements: spec» در همه فایل‌های php/js/css | PASS |
| git diff قالب پدر dr-plus/ | خالی (PASS) |
| php -l / phpcs | NOT RUN — PHP CLI در sandbox موجود نیست؛ باید در CI/server staging اجرا شود |

## تست‌های runtime (V1–V13) — وضعیت

| Test | سناریو | وضعیت |
|------|--------|-------|
| V1 | بدون fatal error در debug.log | NOT RUN — نیاز به runtime زنده |
| V2 | firing فیلتر اسلات | NOT RUN — نیاز به runtime زنده |
| V3 | مخفی شدن اسلات پر | NOT RUN — نیاز به runtime زنده |
| V4 | رد شدن bypass با POST مستقیم + ثبت ردیف لاگ | NOT RUN — نیاز به runtime زنده |
| V5 | معافیت ویزیت آنلاین | NOT RUN — نیاز به runtime زنده |
| V6 | نادیده گرفتن soft-delete در سرور | NOT RUN — نیاز به runtime زنده |
| V7 | حفظ رفتار فرانت‌اند parent در soft-delete | NOT RUN — نیاز به runtime زنده |
| V8 | بلاک شدن submit با تاریخ خالی + پیام فارسی | NOT RUN — نیاز به runtime زنده |
| V9 | race با ۲ POST همزمان | NOT RUN — نیاز به runtime زنده |
| V10 | تغییر ظرفیت از پنل ادمین | NOT RUN — نیاز به runtime زنده |
| V11 | regression (آنلاین، خالی، cancel، موبایل ۳۷۵px) | NOT RUN — نیاز به runtime زنده |
| V12 | RTL و فارسی بودن پیام‌ها | NOT RUN — نیاز به runtime زنده |
| V13 | پاکسازی داده‌های تست (تاریخ 2099-05-20) | NOT RUN — نیاز به runtime زنده |

## نکته
- هيچ تستی به‌صورت «Assumed PASS» گزارش نشده است؛ هرجا که runtime وجود نداشت,
  وضعیت «NOT RUN» درج شده است.
