# Design Audit — Invisible Scribe EEC opt-in (fintech/index.html)
**Date:** 2026-06-16
**Path:** fintech/index.html (served http://127.0.0.1:8791/index.html)
**Type:** HTML landing page (single-page opt-in)

## Score: 18/20 — Excellent (ship as-is; the one P1 is already fixed)

| Dimension | Score | One-line summary |
|---|---|---|
| Accessibility | 4/4 | Semantic main/section/footer, h1>h2>h3 order, label[for], focus-visible, aria-live form, alt text, reduced-motion |
| Performance | 4/4 | transform/opacity-only animation, lazy below-fold img, fonts non-render-blocking + swap, inline SVG seams |
| Theming | 4/4 | Full token system (paper/ink/ochre + scale), one display/serif/mono stack, consistent CTA primitive |
| Responsive | 3/4 | 900/760/680/600 breakpoints verified at 1280 + 390; touch targets ok; 768 inherits the stack (not independently tuned) |
| Anti-Patterns | 4/4 | Zero AI tells: editorial kinetic list (no card grid), torn seams (no gradient bars), ink-stamp CTA, ochre (non-category) palette |

## Issues
### P0: none
### P1 (high)
- [FIXED] line 7 title em-dash ("Playbook — a free..."). Violated no-em-dash hard rule. Fixed to colon during audit. Zero em/en dashes site-wide now.
### P2 (medium)
- 768px tablet inherits the single-column stack (acceptable for a single-scroll opt-in).
- hero.png 1536x1024 ~2.1MB PNG; compress to WebP+JPG before heavy traffic (LCP).
### P3 (low)
- og.jpg referenced but not yet generated.
- Local QA shows fallback fonts (Google Fonts TLS-blocked on laptop); link + families verified correct, load on Hostinger.

## Anti-Pattern Tells
None. Verified absent: side-stripe borders, gradient text, glassmorphism system, hero-metric template, identical card grid (5 days = asymmetric editorial kinetic list with torn-ribbon rules + alternating offsets), modal CTA, bounce default, purple/blue gradients, reflex fonts, stock hero (custom Kie fountain-pen macro), 4-col footer.

## Category-Reflex Check
Category: thought-leadership ghostwriting for fintech founders. Palette: aged ledger paper + iron-gall ink + ochre. Stranger guess category from palette? No (fintech reflex is blue/dark-SaaS; this anchors in writing-craft ink-on-paper). Passes.

## Recommendation
Ship. Gate cleared: 18/20 >= 15 AND anti-patterns 4/4 >= 3/4. P2/P3 are post-launch polish.
