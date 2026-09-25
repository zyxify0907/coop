# CoopBest

CoopBest is a Laravel cooperative management system.

## Stack

- Laravel 13
- PHP 8.4
- Tailwind CSS
- Vite
- Blade views in `resources/views`
- Web routes in `routes/web.php`

## Product Roles

- Admin manages ahli, staff, imports, vendors, pembayaran, saham, and reports.
- Staff manages tempahan and stok.
- Student / ahli views profile, saham/yuran, and creates tempahan.

## UI Direction

- Build premium SaaS-style interfaces for an operations system.
- Prefer layouts that feel clean, dense, and practical like Linear, Vercel, and Stripe.
- Never use Bootstrap.
- Use reusable Blade patterns/components when the same UI appears repeatedly.
- Always make layouts responsive for desktop, tablet, and mobile.
- Follow accessibility best practices: labels, visible focus states, readable contrast, semantic buttons/forms.
- Use professional line icons. Do not use emoji as UI icons.

## CoopBest Palette

- Primary: `#2563EB`
- Secondary/status success: `#10B981`
- Background: `#F8FAFC`
- Card: `#FFFFFF`
- Text: `#0F172A`
- Secondary text: `#64748B`
- Border: `#E2E8F0`
- Danger: `#EF4444`
- Warning: `#F59E0B`

Use emerald only for success/status, not as the dominant UI color.

## Engineering Rules

- Keep route names explicit and use named routes in Blade.
- Use CSRF protection for all forms.
- Use method spoofing for PUT/PATCH/DELETE forms.
- Do not rewrite unrelated parts of the app while making UI changes.
- Run `.\vendor\bin\pint.bat --dirty` and `php artisan test` after meaningful changes.
