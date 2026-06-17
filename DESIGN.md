# DESIGN.md — Invisible Scribe

> Locked visual decisions for invisiblescribe.com. Derived from PRODUCT.md.
> Created 2026-06-16. Read BEFORE writing HTML; cite sections when deciding.

## Archetype
EDITORIAL_NARRATIVE. A writing brand's opt-in must read as written, not assembled.
The page is a single editorial scroll, not a funnel skeleton. Organic-geometry
first-pass standard applies (torn-paper seams, irregular SVG ribbons, ink-stamp
CTAs, no rectangle tells, no CSS gradient bars).

## Typography (LOCKED)
- Display / headings: Bricolage Grotesque (700/800). Distinctive editorial sans,
  NOT on the reflex-reject list. Carries the "serious magazine cover" register.
- Body / literary: Spectral (400/500/600). Production Type editorial serif, allowed.
  This is the writer's voice on the page.
- Meta / labels / kickers: Spline Sans Mono (400/500). Quiet archival ledger tone.
- Reflex-reject check: did NOT use Inter, DM Sans, Playfair, Gloock (v1), Fraunces,
  Cormorant, Source Serif, Space Grotesk. Pairing is fresh vs last 3 builds and vs v1.

## Palette (LOCKED) — anchored in "aged ledger paper + iron-gall ink"
- --paper   #EFE9DC  warm archival paper (surface)
- --paper-2 #E6DDCB  lifted paper tone (panels, via tone not borders)
- --paper-3 #DCCFB6  deepest paper (alt sections)
- --ink     #1C2230  iron-gall ink, deep blue-black (text + dark sections)
- --ink-2   #2A3142  lifted ink
- --ochre   #B07A2E  aged-ink ochre — THE accent (CTA, focal moments). The
  surprising choice: a writing/fintech brand that is NOT fintech-blue.
- --ochre-soft #C9954A  lighter ochre for glow / molten moments on dark
- --ink-soft #5A5446 muted body on paper
- --ink-faint #8A8070 faint meta
- 3 anchors (paper, ink, ochre) + neutrals. One accent only.

## Type scale
Fluid clamp, editorial. h1 clamp(2.6rem, 1.6rem+4.4vw, 6rem) line-height .98.
Body 1.05-1.2rem, line-height 1.6. Generous measure caps (lede 54ch, body 66ch).

## Spacing
Section padding clamp(78px, 9vw, 120px). Asymmetric: hand-placed offsets, uneven
row tilts, stamped labels at small negative tilts. No uniform grid edges.

## Motion vocabulary (EDITORIAL_NARRATIVE)
- Hero headline: line-wipe reveal (translateY 108% -> 0, staggered), reduced-motion safe.
- Scroll reveal: opacity + translateY, cubic-bezier(.22,1,.36,1), elements VISIBLE by
  default + JS adds .pre hide class only when off-screen (never invisible static).
- One signature interaction: the word "invisible" in the hero renders in outline /
  faint ink, then INKS IN (fills) on load — the invisible scribe becoming visible.
  Settles to a static inked rest-state so a screenshot reads as resolved.
- No bounce default. No Lenis (static-HTML, shared hosting). prefers-reduced-motion respected.

## Interaction primitives
- CTA = rough-edged ink stamp (feTurbulence displacement on fill layer, text crisp on top).
- Form fields = hand-ink underline (border-bottom only), NOT boxed inputs. Matches no-frame idiom.
- Section seams = distinct deterministic torn-paper SVG edges, a different macro envelope each.
- Dividers/rules = irregular hand-inked SVG ribbons at varied widths + slight tilts. Never hr/gradient bar.

## Anti-pattern compliance checklist
- [ ] No banned skeleton (no sticky-nav+split-hero+card-grid+CTA-box+footer)
- [ ] Services/"what you get" is NOT a card grid (use editorial kinetic list)
- [ ] No side-stripe borders, no gradient text, no glassmorphism system
- [ ] No identical 3/N-card grid
- [ ] No em-dashes anywhere
- [ ] Single primary CTA path (email capture), inline not modal
- [ ] Reflex-reject fonts: none
- [ ] Category-reflex palette avoided (ochre, not fintech-blue)
- [ ] Agent-readable DOM: native button/input/label, cursor pointer, real focus states
- [ ] Form has success state + error state + loading state

## Opt-in wiring target (LOCKED)
- subscribe.php (Hostinger runs PHP) -> Kit v4 API:
  - create/upsert subscriber (first_name + email)
  - add to sequence 2796374 ("The Fintech Founder's Authority Playbook (EEC)")
  - tag fintech-eec (id 20415268)
- Kit base https://api.kit.com/v4/ , auth header X-Kit-Api-Key.
- API key NOT in repo. Lives in Hostinger env / gitignored config.php. Never committed.
- Form posts to subscribe.php; PHP returns JSON {ok:true} / {ok:false,error}.
- Front-end shows loading spinner -> success state on ok, error message on fail.
