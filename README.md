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

## Admin

- One **BrightNest** menu holds everything: Dashboard, Bookings, Services, Add Service, Categories, Reviews and Settings.
- **Settings** (tabs): *Business* (phones, WhatsApp, email, address, hours, cities, currency), *Booking* (slot hours, same-day notice, days ahead, cash/card on/off and labels, note under the confirm button, notification email, vouchers), *Homepage* (every heading, text and photo, each section on/off; `|` highlights words in orange), *FAQ* (global questions), *Social & numbers*.
- **Payments**: no online payment yet — customers pay cash or card after the service; methods are chosen in Settings → Booking and validated on the server.
- **Categories**: choose which homepage tab (Cleaning / Maintenance / Lifestyle) each category appears in.
- **Service editor** also has *Display order* and a per-service *FAQ* tab.
- **Bookings**: private admin notes on each booking and *Export CSV* (respects the status filter).
- **Dashboard**: new bookings, bookings this week vs last week, booked value this month, live services, a 14-day bookings chart, status breakdown, latest bookings with one-click *Confirm*, top services, a website setup checklist and shortcuts.
- **Services → Add New**: title, short description, then a *Service Details* box with three tabs:
  - *Basics* — price (auto-filled from the cheapest option if left empty), duration, icon picker, "Popular" switch.
  - *Booking & prices* — choose *Packages / options* (visual editor: add tabs and options with price and "was" price, reorder, delete) or *Hourly*.
  - *What's included* — checklist items.
- **Services list**: icon, category, price, option/tab count, one-click ⭐ popular, *Duplicate* and *Test checkout* links.
- **Bookings list**: status filter, one-click *Confirm / Complete / Cancel*, *WhatsApp* link, and a count of new bookings on the menu.

## Checkout options format

The visual editor writes this text format; you can also paste it under *Bulk edit as text*. One option per line:

```
Tab name | Option name | Short description | Price | Old price (optional)
```

Leave empty to sell the service itself at its starting price.

**Hourly mode** (e.g. Home Cleaning): set *Booking mode* to *Hourly* and enter a rate per professional per hour and an optional materials rate. Step 1 then asks for hours (1–8), professionals (1–4) and cleaning materials.

## Location

The homepage hero asks *"Where would you like to receive your service?"* (OpenStreetMap search or GPS). The chosen location is remembered in the browser and pre-fills the checkout, so the map pop-up is skipped.

## Credits

Hero photo (`assets/img/hero.jpg`): green velvet sofa from [Unsplash](https://unsplash.com/license), free to use under the Unsplash License.

## Vouchers

*Customize → BrightNest Settings → Promo Banner → Voucher codes*, one per line: `CODE:20%` (percent) or `CODE:50` (fixed amount).
