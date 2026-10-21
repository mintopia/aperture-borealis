# Borealis Design Brief

## Purpose

Borealis is the companion authentication app for Aperture, a LAN party management tool. It handles OAuth2 Device Flow authentication for event attendees and administrators. The core customer journey is intentionally short: a user sees a code on their PC, scans a QR code or enters a short device code on their phone, chooses an enabled identity provider, completes sign-in, and returns to their PC to confirm access.

This document is the definitive design reference for Borealis implementation. It captures the workshop decisions, the expected page inventory, the shared layout and component rules, and the design constraints inherited from Aperture's Dispatch design system.

## Product Context

### What Borealis Does

- Supports OAuth2 Device Flow for event login
- Lets users authenticate with a small fixed set of social or self-hosted providers
- Confirms successful authentication back on the user's PC
- Gives event staff an admin interface for providers, clients, theming, and general settings

### Primary User Groups

| User | Context | Design Implication |
| --- | --- | --- |
| LAN event attendees | Hurried, interrupted, sometimes confused or annoyed | Customer flows must be fast, obvious, reassuring, and mobile-first |
| Admins | Managing a live event system under pressure | Admin UI must be dense, scannable, stable, and desktop-optimised |

## Platform and Experience Strategy

### Platforms

- **Customer experience:** public internet, mobile-first
- **Admin experience:** desktop-first admin panel, with sensible tablet support
- **Validation target:** explicit mobile viewport review using an iPhone 14 Pro frame at **375×812**

### Experience Principles

1. **Fast under pressure** — minimise friction, choices, and cognitive load.
2. **Reassuring by default** — always remind users that the code came from their PC and that they should return there after success.
3. **Exact Aperture alignment** — Borealis must feel like part of the same product family, not an adjacent tool.
4. **Themeable without fragmentation** — the system supports accent and mode changes, but not arbitrary branding uploads.
5. **Operational clarity** — admins need strong information hierarchy, stable navigation, and familiar management patterns.

## Design System Alignment

### Required System

Borealis uses **Aperture's Dispatch design system** as an exact match.

- **Frontend stack:** Tailwind CSS 4, Vue 3, Inertia.js
- **Visual system:** Dispatch tokens, patterns, spacing rhythm, and component language
- **Expectation:** if a matching Dispatch component or pattern exists, Borealis should reuse it rather than inventing a new one

### Typography

All fonts must be **locally hosted**.

| Role | Font |
| --- | --- |
| Headings / display | Bricolage Grotesque |
| Body / UI copy | Hanken Grotesk |
| Code / IDs / machine values | JetBrains Mono |

## Theme and Visual Customisation

### Accent Presets

Borealis uses a hue-based accent system with the following eight presets:

| Name | Hue |
| --- | --- |
| Pink | 350 |
| Coral | 20 |
| Tangerine *(default)* | 55 |
| Lime | 135 |
| Teal | 185 |
| Sky | 230 |
| Violet | 295 |
| Magenta | 325 |

### Mode Support

- Light mode
- Dark mode
- System mode toggle

### Customer Page Customisation

- Customer-facing pages allow **custom CSS injection**
- This is intended for event-level tuning, not full rebranding
- **No branding uploads** are supported, matching Aperture's approach

## Authentication Model

### Social Providers

The supported provider list is fixed:

1. Steam
2. Discord
3. Twitch
4. Authentik
5. Laravel Passport

Typically, only **2–3 providers** are enabled for a given event. The UI should therefore:

- treat provider lists as short and curated
- present providers as prominent, full-width actions
- rely on recognisable icons and brand colours for fast scanning

### Device Codes

| Attribute | Value |
| --- | --- |
| Length | 4 characters |
| Charset | `ABCDEFGHJKLMNPWRSTUVWXYZ23456789` |
| Default expiry | 300 seconds |
| Expiry setting | Configurable in admin settings |

Design implications:

- code entry must feel quick and tactile on mobile
- ambiguous characters are excluded by design
- expiry messaging must be explicit and recovery-focused

### Admin Authentication

- **Primary:** Aperture SSO
- **Fallback:** email/password

### Legal Links

- Terms URL: optional
- Privacy URL: optional
- These links appear in the **customer footer only when configured**

## Canonical Prototype

The canonical visual reference is:

- `docs/mockups/borealis-prototype.html`

This prototype contains all **15 designed pages** and should be treated as the source of truth for layout, tone, hierarchy, and component styling during implementation.

## Page Inventory

### Customer Pages

Customer pages should use a focused, centered, dark-first presentation with strong mobile ergonomics.

| # | Page | Purpose | Key UI Requirements |
| --- | --- | --- | --- |
| 1 | Code Entry | Primary entry point for attendees | 4 individual digit inputs, centered dark layout, event branding (logo + name), expandable "How it works" guidance |
| 2 | Code Entry (Error) | Validation failure state | Same as Code Entry, but with red-bordered inputs and inline error messaging |
| 3 | Provider Selection | Choose an enabled auth provider | Full-width provider buttons with brand colours and icons, plus reassurance text referencing the PC code |
| 4 | Provider Selection (Loading) | Redirect transition state | Selected provider shows spinner and "Redirecting to [Provider]…", all others disabled |
| 5 | Success | Completion confirmation | User avatar, display name, "You're all set" headline, instruction to return to PC |
| 6 | Error / Auth Failed | Provider or auth failure | Error circle icon, descriptive message, clear "Try Again" action |
| 7 | Code Expired | Expired device code recovery | Warning/clock icon, message to check PC for a new code, "Enter New Code" button |

### Customer Flow Notes

- The design must keep users oriented at every step.
- The event logo and event name establish trust early.
- Reassurance text should repeatedly connect the phone flow to the user's PC session.
- Error states should explain recovery in plain language instead of just reporting failure.

### Admin Pages

Admin pages should follow Aperture's standard operational layout and component language.

| # | Page | Purpose | Key UI Requirements |
| --- | --- | --- | --- |
| 8 | Admin Login | Entry point for administrators | Centered login box, Aperture SSO as primary action, divider with "or", email/password fallback fields |
| 9 | Dashboard | System overview | Stat strip for auths today, active codes, providers, clients; recent authentications DataTable with StatusPills |
| 10 | Social Providers | Provider enablement and config | 2-column card grid; each card includes icon, name, status, configure link, and toggle |
| 11 | Clients (List) | Manage OAuth clients | DataTable with linked name, monospace client ID, status pill, auths in 24h, created date, and "New Client" CTA |
| 12 | Client Detail | View and manage a client | MetadataStrip for key stats; credential rows with copy/reveal actions; configuration form; danger zone |
| 13 | Theme Settings | Control Borealis appearance | Settings layout with subnav, 8 accent presets, hue slider, mode toggle, site title, custom CSS textarea |
| 14 | General Settings | Configure operational defaults | Settings layout with subnav, site name, device code expiry, optional legal links |
| 15 | Mobile Viewport | Responsive validation artifact | iPhone 14 Pro frame showing the Code Entry page at 375×812 |

## Layout Rules

### Admin Layout Pattern

Borealis admin pages inherit the Aperture admin shell exactly.

| Area | Rule |
| --- | --- |
| Overall shell | Flex row layout |
| Sidebar width | 220px on the left |
| Header | Sticky 48px header in right column |
| Main content | Scrollable content area |
| Main content width | Max width 1400px |
| Main padding | 40px horizontal, 32px top |

### Sidebar Structure

Sidebar content order:

1. Logo
2. Navigation groups
3. Divider
4. Logout action

Navigation groups:

- **Overview:** Dashboard
- **Management:** Social Providers, Clients
- **Settings:** Theme, General

### Header Pattern

- Breadcrumb on the left
- User avatar + label on the right

### Settings Page Pattern

Settings pages use a split settings layout:

- negative margins to fill the main area
- **180px** sub-navigation on the left
- settings content on the right

## Component Library Requirements

All major UI pieces should match Aperture's existing component behaviour and styling.

| Component | Required Characteristics |
| --- | --- |
| SectionHeader | 14px uppercase bold heading |
| StatCard / StatStrip | 10px label + 28px value, optional dot indicator |
| DataTable | 13px body text, 11px uppercase headers, clickable rows with hover state |
| StatusPill | Inline flex, 11px bold, success/danger/warning/neutral variants, `/14` opacity backgrounds |
| FormField | 11px uppercase label, monospace input styling where appropriate, optional hint text |
| MetadataStrip | Horizontal metadata items separated by borders |
| Toggle Switch | 40×22px, green when on |
| Provider Card | Icon, provider name, status, configure link, toggle |

### Interaction Expectations

- Clickable rows must feel clearly interactive
- Status should be scannable at a glance
- Monospace treatment should be used for client IDs, secrets, codes, and machine-readable values
- Toggle controls must read as immediate state controls, not as submit-driven form inputs

## CSS Token System

Implementation should use Aperture's `dispatch.css` token names.

### Core Dispatch Tokens

| Category | Tokens |
| --- | --- |
| Backgrounds | `--color-bg`, `--color-surface`, `--color-surface-hover` |
| Text | `--color-text`, `--color-text-secondary`, `--color-text-muted` |
| Borders | `--color-border`, `--color-border-hover` |
| Semantic | `--color-primary`, `--color-accent-dim`, `--color-success`, `--color-danger`, `--color-warning`, `--color-info` |

### Customer Page Aliases

Customer pages also use shorthand aliases:

- `--bg`
- `--surface-1`
- `--surface-2`
- `--surface-3`
- `--text`
- `--text-2`
- `--text-3`
- `--accent`
- `--up`
- `--down`
- `--warn`

The customer shorthand layer should remain aligned with Dispatch semantics rather than drifting into a separate visual system.

## Content and UX Guidance

### Customer Copy Tone

- short
- direct
- calm under pressure
- recovery-oriented when errors occur

### Customer Interaction Rules

- Keep single-purpose screens focused on one primary action
- Preserve context between steps by showing the event identity and/or code reassurance text
- Use descriptive loading text during provider redirects
- Always tell the user what to do next after success or failure

### Admin UX Guidance

- Prioritise clarity and scanability over decorative treatment
- Use structured lists and strips rather than heavy card overload
- Make operational state visible without requiring drill-down
- Keep settings forms predictable and convention-driven

## Responsive Expectations

### Customer Pages

- Mobile-first is mandatory
- Code entry should remain comfortable and legible on smaller devices
- Provider buttons should stay full-width and easy to tap
- Expandable help content should not compete with the main flow

### Admin Pages

- Desktop is the primary design target
- The supplied mobile viewport page is for validation of customer-facing responsive behaviour, not a fully mobile admin redesign

## Constraints and Non-Negotiables

### Must Match Aperture

- Dispatch design system patterns
- Layout structure
- Typography choices
- Token naming
- General level of polish and visual confidence

### Must Not Include

- Border-left accent treatments
- Gradient text
- `backdrop-filter`
- Generic or bland dashboard styling
- Google Fonts or other remote typography dependencies
- Branding or logo uploads

## Implementation Reference Checklist

Use this checklist before building or reviewing any Borealis UI:

- [ ] Matches Dispatch, not a parallel design language
- [ ] Uses locally hosted Bricolage Grotesque, Hanken Grotesk, and JetBrains Mono
- [ ] Uses one of the approved accent presets or the hue system
- [ ] Supports dark, light, and system mode
- [ ] Respects the 15-page reference prototype
- [ ] Keeps customer flows mobile-first and recovery-oriented
- [ ] Keeps admin pages desktop-first and operationally clear
- [ ] Uses Dispatch token names
- [ ] Avoids all listed anti-patterns

## Source References

- Workshop decisions captured in this brief
- `docs/mockups/borealis-prototype.html` — canonical Borealis prototype
- Aperture Dispatch design context in `.impeccable.md`
