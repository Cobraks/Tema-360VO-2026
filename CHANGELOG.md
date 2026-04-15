# Changelog

## 3.0.13 - 2026-04-15
- Fine-tuned the mobile single floating controls so the TOC sits flush to the right edge while the search trigger offsets itself to the left with a clearer gap.
- Restored a stronger material-style treatment for the newsletter input, eased the compact TOC closed-state sizing, and gave the mobile post content a little more inline breathing room.
- Added right alignment helpers to the shared TOC container defaults.

## 3.0.12 - 2026-04-15
- Refined the single-post mobile floating controls with a right-aligned search trigger, animated search/close icon swap, and cleaner spacing between the search and TOC buttons.
- Softened the single hero typography, simplified the post content card container, restored a more polished newsletter form treatment, and hid action labels on narrower screens.
- Updated the shared page TOC alignment so its container content is right-justified by default.

## 3.0.11 - 2026-04-15
- Reworked the blog single mobile controls so search and TOC behave as coordinated floating panels, closing each other to avoid overlap.
- Refined the blog search styling and reshaped the single featured image for a more editorial presentation.
- Added accessible TOC toggle labelling and stopped the single-post TOC from opening by default on mobile.

## 3.0.10 - 2026-04-15
- Added a smooth transition to the shared TOC container and adjusted its sticky offset from `783px` upward for a little more breathing room below the header and breadcrumbs.

## 3.0.9 - 2026-04-15
- Smoothed TOC section navigation by isolating its programmed scroll from the theme's global scroll-state toggles, reducing abrupt layout changes during the trip to each heading.
- Tuned the TOC scroll timing and persisted the final hash after the animation completes.

## 3.0.8 - 2026-04-15
- Replaced the TOC anchor jump with a controlled smooth-scrolling routine so section navigation transitions more fluidly between headings.
- Tightened TOC link spacing and text rhythm by removing inline padding and gap, aligning content from the top, and balancing wrapped titles.

## 3.0.7 - 2026-04-15
- Refined the shared TOC toggle geometry and icon choreography so the closed sticky state stays proportioned and the left, center, and right controls align consistently.
- Replaced the TOC collapse icon with the inward-arrow variant and tightened the tablet/desktop TOC width to `360px` from `783px` upward.
- Switched TOC item navigation back to a smooth `scrollIntoView()` flow that respects each heading's configured `scroll-margin-top`.

## 3.0.6 - 2026-04-15
- Stabilized the shared TOC around the `783px` breakpoint by removing conflicting width/state overrides that could make the closed panel flicker after scroll.
- Fixed the sticky state handoff so mobile-only compact behavior no longer leaks into tablet and desktop layouts.
- Set the shared page TOC container to a consistent `460px` max width at `783px+` and cleaned up duplicated breakpoint rules.

## 3.0.5 - 2026-04-15
- Updated the TOC component with dedicated theme icons, mobile-first open state, smoother in-page scrolling, and a cleaner sticky transition as it reaches the viewport edge.
- Simplified the page TOC layout rules so it sits in the main flow on mobile and no longer inherits several legacy right-column overrides.
- Scoped logged-in TOC offsets to sticky states only, avoiding unwanted displacement when the TOC is part of the normal flow.

## 3.0.4 - 2026-04-15
- Fixed the stylesheet dependency chain so `blog.css` loads after `pages.css` and can safely override TOC layout modifiers in blog single posts.
- Removed legacy TOC toggle radius/hidden-state blocks that no longer match the current interaction.
- Refined shared TOC list/link/number styles for a cleaner hover state across pages and single posts.

## 3.0.3 - 2026-04-15
- Unified the table of contents markup so pages and blog single posts use the same theme component.
- Enabled the TOC by default in `single.php` through a local flag and added the `toc-container--single` modifier for post-specific layout overrides.
- Moved dynamic TOC generation back to the shared theme script and removed the duplicate single-post TOC logic from `blog.js`.
- Aligned the internal theme asset version with the stylesheet version.
