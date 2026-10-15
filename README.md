# BrightNest — WordPress theme

Custom home-services marketplace theme (cleaning, maintenance, moving, pest control, health at home…) with service listings, a 4-step booking checkout and an admin bookings inbox.

## Install

1. Copy this folder to `wp-content/themes/brightnest`.
2. Activate **BrightNest** in *Appearance → Themes*. On first activation it creates starter service categories, services, sample reviews and the About, Contact, FAQs, Terms, Privacy and Checkout pages.
3. Set permalinks to *Post name* (*Settings → Permalinks*).
4. Fill in *Appearance → Customize → BrightNest Settings* (phone, WhatsApp, cities, currency, vouchers, stats).

## Features

- **Services** custom post type with categories, price, duration, rating, icon, "What's included" and "Most popular" flag.
- **Homepage**: hero with service search, trust stats, category grid, service carousels, why us, how it works, reviews, promo banner, areas, FAQ.
- **Checkout** (`/checkout/?service=ID&step=1&voucher=CODE`):
  1. Options with category tabs and quantity steppers
  2. Frequency, date and time slots
  3. Address (map pin via Leaflet + OpenStreetMap) and contact details
  4. Review, payment method (cash / card on arrival), voucher, confirm
- Live booking & payment summary sidebar; prices and vouchers are recalculated on the server.
- **Bookings** inbox in wp-admin with status (New / Confirmed / Completed / Cancelled) and email notifications.
- Reviews, contact form, WhatsApp button, Service & FAQ schema markup, responsive down to 360px.

## Checkout options format

In *Services → Edit → Checkout options*, one option per line:

```
Tab name | Option name | Short description | Price | Old price (optional)
```

Leave empty to sell the service itself at its starting price.

## Vouchers

*Customize → BrightNest Settings → Promo Banner → Voucher codes*, one per line: `CODE:20%` (percent) or `CODE:50` (fixed amount).
