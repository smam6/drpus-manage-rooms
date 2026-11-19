# drplus-probe.php — the read-only behavioural probe

## Why it exists

Ten files in the parent theme are **ionCube-encrypted**:

| file | size |
|---|---|
| `inc/SaveBookingProcess.php` | 239,321 B |
| `Redux/Options.php` | 148,421 B |
| `inc/wallet/WalletUtils.php` | 80,896 B |
| `inc/wallet/Woocommerce/ProcessTopUp.php` | 69,776 B |
| `inc/wallet/Woocommerce/PartialPayment.php` | 63,012 B |
| `Redux/RTL_License_e4b3bfa439bf4f40.php` | 61,336 B |
| `Redux/Save.php` | 49,570 B |
| `inc/ProcessBuyPlan.php` | 54,384 B |
| `inc/Utils/utils-chat.php` | 44,857 B |
| `inc/wallet/ThemeInit.php` | 26,977 B |

Their logic decides how a booking becomes an order, and how an order status flows back
into `drplus_booking`. That logic **cannot be read** — the ciphertext is 7.9984 bits/byte
over 176,485 bytes with zero repeated 16-byte blocks, and the key lives inside the
proprietary ionCube Loader binary, not in the file.

So the honest alternative is to **ask the code what it does**, on a machine where it
actually runs. That is what this probe does.

## What it does NOT do

- writes nothing
- creates no orders, no bookings, no options
- cancels nothing, emails nobody
- requires `manage_options`

Every section is a read: `get_available_time_slots()` (a pure query), `SHOW TABLES`,
`SELECT`, and option reads.

## How to run it

1. Upload `drplus-probe.php` to `public_html/` (the folder with `wp-load.php`).
2. Log in as an administrator.
3. Open `https://YOUR-SITE/drplus-probe.php`.
4. Copy the whole output back.
5. **Delete the file.**

## What each section answers

| § | question |
|---|---|
| 1 | versions, timezone, whether the ionCube loader is running |
| 2 | which parent files are encrypted (proof, from your own install) |
| 3 | is the capacity module active |
| 4 | **does the parent block a mixed in-person/online double-booking** — measured by calling its own slot generator for both visit types on the same specialist and day |
| 5 | which cancel paths exist, and the parent's own list of valid statuses |
| 6 | stale-status count and how many rows hold a slot right now |
| 7 | plain-language summary |

Section 4 is the one that answers the open question. It prints the parent's own
slot lists for an in-person room and for `phone_consultation` /
`chat_consultation` / `video_consultation` on the same day. If the online lists
show hours that overlap the in-person list, the parent does not block the
clash — regardless of what any encrypted file "probably" does.
