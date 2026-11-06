# Phase B — Category 2: Slot Filtering (Specs 2.1–2.15)

Parent evidence base: filter `apply_filters('drplus/booking/available_time_slots', $available_slots, ['date'=>…, 'specialist_id'=>…, 'office_id'=>…])` at `dr-plus/inc/Utils/utils-booking.php:511`; slot shape `{from,to,available,label}` built at `utils-booking.php:500-506`; JS consumer expects `res.data[]` with `slot['from']`, `slot['available']`, `slot['label']` at `assets/js/booking.js:189-219`; disabled slots rendered `.booking-time-slot.disabled` at `templates/booking/template-booking-step-time.php:246-267`; AJAX endpoint `drplus_get_available_times` at `inc/AJAX.php:211-220`.

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 16 | 2.1 | Filter MUST be registered as `add_filter('drplus/booking/available_time_slots', 'drplus_child_capacity_filter_slots', 20, 2)` | grep registration in bootstrap | VERIFIED (hook `utils-booking.php:511`) |
| 17 | 2.2 | Callback MUST accept exactly 2 args: `$available_slots`, `$args` with `date`, `specialist_id`, `office_id` | reflection/unit test | VERIFIED (`utils-booking.php:511-516`) |
| 18 | 2.3 | For each slot with `available === true`, capacity MUST be checked (with `deleted = 0`); if exceeded, the slot MUST be `unset()` and the array re-indexed with `array_values()` — full slots are REMOVED, not disabled | scenario D2 | VERIFIED (decision #2: hidden entirely) |
| 19 | 2.4 | Slots with `available === false` MUST be left untouched (trust parent's off-hours/breaks/soft-delete behavior) | scenario V7 | VERIFIED (locked rule; parent bug preserved deliberately) |
| 20 | 2.5 | Returned array MUST preserve the `{from, to, available, label}` shape for every remaining slot | compare response vs `booking.js:208-219` consumer | VERIFIED |
| 21 | 2.6 | Online offices (`phone_consultation`, `chat_consultation`, `video_consultation`) MUST skip filtering entirely and return slots unchanged | scenario D3 | VERIFIED (decision #5; `utils-booking.php:560-576`) |
| 22 | 2.7 | Hour counts for the whole day MUST come from one `GROUP BY start_time` query (spec 1.13), not per-slot queries | query log | VERIFIED |
| 23 | 2.8 | `HH:MM` slot `from` values MUST be normalized to `H:i:s` before comparison with DB `TIME` values | unit check normalizer | VERIFIED (`utils-booking.php:457` builds `date('H:i', …)`) |
| 24 | 2.9 | If capacity option is missing/zero/invalid, fallback MUST be `DRPLUS_ROOM_CAPACITY_DEFAULT = 2` | unit test with deleted option | VERIFIED (decision #1) |
| 25 | 2.10 | Filter MUST apply to both AJAX (`drplus_get_available_times`) and any template rendering, because both go through `Booking::get_available_time_slots()` | hit AJAX + reload page | VERIFIED (`inc/AJAX/GetAvailableTimes.php:30`) |
| 26 | 2.11 | No persistent cache MAY store filtered slot lists (counts change per request); only per-request static cache allowed | code review | VERIFIED (spec 1.14) |
| 27 | 2.12 | Feature MUST be disableable via filter `drplus_child_capacity/enabled` returning false → return `$available_slots` unchanged | toggle filter, reload slots | VERIFIED (Section 5.6 spec 95) |
| 28 | 2.13 | CONTINGENCY: if the parent filter at `utils-booking.php:511` is ever removed, wrapper MUST `remove_action('wp_ajax_drplus_get_available_times', [\DrPlus\AJAX\GetAvailableTimes::get_instance(), 'get_available_times'], 10)` and same for `wp_ajax_nopriv_…` | code review only | VERIFIED as viable (Verification 2, `inc/AJAX.php:351-358`, priority 10, singleton callback) |
| 29 | 2.14 | CONTINGENCY: wrapper MUST replicate parent nonce (`booking_available_times`) and requires (`specialist, office, date, chunk, nonce`) validation exactly | compare with `inc/AJAX.php:211-220, 401-447` | VERIFIED |
| 30 | 2.15 | CONTINGENCY: wrapper MUST call `Booking::get_available_time_slots($date,$specialist_id,$office_id,$chunk)`, filter full slots, and respond via identical `wp_send_json_success($slots)` structure | compare with `inc/AJAX/GetAvailableTimes.php:20-32`, `inc/AJAX.php:366-372` | VERIFIED |
