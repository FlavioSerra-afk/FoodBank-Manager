# Shortcodes UX Guide

FoodBank Manager exposes two public shortcodes:

- `[fbm_form preset="slug"]` – renders a public intake form based on the given preset.
- `[fbm_dashboard]` – shows the attendance dashboard for users with the proper capability.

Assets for these shortcodes are only loaded on the front-end when the shortcodes are present in the page content. The dashboard is restricted to users who can `fb_manage_dashboard` or `fb_view_dashboard`; others see a friendly notice.

