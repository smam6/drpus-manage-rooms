# راهنمای پیکربندی — مدیریت ظرفیت اتاق‌های کلینیک

## تغییر ظرفیت هر کلینیک
1. از مسیر «تنظیمات ‹ ظرفیت اتاق‌ها» (Settings ‹ Room Capacity) وارد صفحه تنظیمات شوید.
2. برای هر کلینیک (پست‌های نوع hospital) یک فیلد عددی وجود دارد؛ بازه مجاز ۱ تا ۹۹ است.
3. مقدار پیش‌فرض برای همه کلینیک‌ها «۲» است (ثابت `DRPLUS_ROOM_CAPACITY_DEFAULT`).
4. با دکمه «ذخیره»، مقدار با کلید `drplus_capacity_clinic_{شناسه کلینیک}` در جدول optionها ذخیره می‌شود.

## ویزیت آنلاین معاف از ظرفیت
- سه نوع ویزیت آنلاین معاف هستند و هرگز تحت تأثیر محدودیت ظرفیت قرار نمی‌گیرند:
  `phone_consultation`، `chat_consultation`، `video_consultation`.
- `instant_chat_consultation` در این کسب‌وکار یک نوع ویزیت واقعی نیست و معاف نمی‌شود.

## فیلترهای قابل override برای توسعه‌دهندگان
- `drplus_child_capacity/enabled` — با مقدار false کل ماژول غیرفعال می‌شود.
- `drplus_child_capacity/default_capacity` — تغییر مقدار پیش‌فرض ظرفیت.
- `drplus_child_capacity/per_clinic` — فیلتر مقدار ظرفیت هر کلینیک.
- `drplus_child_capacity/online_types` — لیست انواع ویزیت آنلاین معاف.
