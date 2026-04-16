# Changelog

## 3.1.2 - 2026-04-16
- Hid the single-post reading bar again when the blog shell is about to scroll out of view, so it clears away before the footer area takes over.

## 3.1.1 - 2026-04-16
- Updated the single-post reading bar so it hides whenever breadcrumbs are visible, reappears when they collapse, and uses a cleaner single-color fill with a subtle motion treatment.
- Expanded the reading-range calculation to start from the beginning of the single page and finish at the end of the article card, instead of beginning only at the entry-content block.

## 3.1.0 - 2026-04-16
- Added a dedicated single-post reading progress bar fixed under the header or breadcrumbs, with its own CSS and JS loaded only on blog single pages.
- Scripted the progress calculation against the article body using requestAnimationFrame plus resize observation, keeping the bar synchronized with scroll depth through to the end of the post.

## 3.0.33 - 2026-04-16
- Increased the single-post desktop TOC selector specificity at 1600px and above so it correctly overrides the shared page TOC grid placement and stays pinned to columns 1–4.

## 3.0.32 - 2026-04-16
- Removed the shared page TOC max-width from 783px upward and moved the single-post desktop TOC fully into columns 1–4 at 1600px and above.

## 3.0.31 - 2026-04-16
- Moved the single-post TOC blur overlay out of the mobile-only CSS so it stays active below 1080px, then explicitly disabled it again from 1080px upward.
- Tightened the newsletter form controls with pill-shaped 3rem fields, rounded focus rings, and a shorter auto-aligned subscribe button.

## 3.0.30 - 2026-04-16
- Repositioned the shared page TOC across the intermediate breakpoints, added an explicit 1080px placement, and reinforced the shared blur overlay through 1599px.
- Refined the single subscribe panel styling with calmer focus rings, larger help icon, tighter heading spacing, a shorter subscribe button, and smoother sticky offsets for the desktop aside.

## 3.0.29 - 2026-04-16
- Shifted the shared page TOC to columns 7–13 from 783px upward and lowered its sticky offset so the mid-width layout breathes more naturally.
- Extended the shared TOC compact-state, blur overlay, and page scroll lock through 1599px so the tablet/intermediate layout no longer re-expands the closed toggle when scrolling back up.

## 3.0.28 - 2026-04-16
- Right-aligned the shared TOC toggle from 783px upward so the tablet breakpoint keeps the control anchored to the right edge without disturbing the larger-screen TOC interaction states.

## 3.0.27 - 2026-04-16
- Extended the single-post floating TOC and search controls through the 768–782px range so they keep the mobile interaction model while the JavaScript still treats that width as mobile.
- Kept both controls fixed and right-aligned in that overlap range, preventing the TOC toggle from jumping left and the search control from collapsing into the desktop layout too early.

## 3.0.26 - 2026-04-16
- Delayed the single-post newsletter aside until 1080px and postponed the three-column TOC/content/aside layout until 1600px, keeping the mid-width layout calmer.
- Refreshed the newsletter email field toward a Material Design 3 outlined style and simplified the panel help button chrome while tightening the panel title spacing.

## 3.0.25 - 2026-04-16
- Sped up the scripted TOC section scrolling, tightened the close-to-scroll handoff on mobile, and increased the single-post heading offset so headers no longer overlap section titles.
- Fixed the mobile single TOC expansion direction by right-aligning the placeholder wrapper and widened the open mobile search field to fill the available row more naturally.

## 3.0.24 - 2026-04-16
- Locked page scrolling while the mobile single TOC is open, keeping breadcrumbs and header state stable while the TOC panel itself remains scrollable.
- Updated TOC link selection so the mobile panel closes completely before running the scripted section scroll, and removed the reduced-motion branch from that TOC navigation flow.

## 3.0.23 - 2026-04-16
- Fixed the single-post mobile TOC compact state by overriding the global TOC width rules with a more specific selector, keeping the closed sticky button icon-only and right-aligned.
- Added click-outside closing for the mobile single TOC and lowered the blur overlay behind the rest of the interface.

## 3.0.22 - 2026-04-16
- Kept the compact single-post TOC aligned to the right of the content area by preserving the gap for the mobile search trigger.
- Improved the TOC keyboard accessibility with Escape-to-close, reinforced the scripted scroll animation, and moved the mobile TOC blur overlay behind the floating controls.

## 3.0.21 - 2026-04-16
- Hardened the single-post mobile TOC compact state so it stays icon-only after entering sticky mode until the user explicitly opens it again.
- Reworked the TOC link scrolling animation to use a pure scripted window scroll and added a mobile blur overlay behind the open TOC panel.

## 3.0.20 - 2026-04-15
- Kept the single-post mobile TOC in its compact closed state after it has entered sticky mode, preventing it from widening again when scrolling back up with the TOC closed.

## 3.0.19 - 2026-04-15
- Refined the single-post mobile TOC so it shows the full toggle with text before sticking, then collapses back to the TOC icon when it reaches the sticky state.
- Forced the TOC heading navigation to use a smoother scripted scroll animation and refreshed the mobile search close icon background.

## 3.0.18 - 2026-04-15
- Nudged the single-post mobile TOC toggle left by adding right offset space for the search control.
- Reduced the shared sticky TOC top offsets and z-index to better fit the mobile header and breadcrumb stack.

## 3.0.17 - 2026-04-15
- Moved asset minification out of WordPress runtime so frontend and admin requests no longer generate minified files on the fly.
- Added a local minify script plus VS Code Run On Save and a manual task to keep `.min` assets synchronized while editing.

## 3.0.16 - 2026-04-15
- Stabilized the TOC toggle so its compact icon-only mode now depends on the TOC state itself instead of the global hidden body state.
- Preserved the user's open or closed choice, added an auto-close after three downward scroll gestures when the TOC was manually opened, and adjusted the sticky offsets plus the single hero excerpt divider behavior.

## 3.0.15 - 2026-04-15
- Corrected the shared hidden sticky TOC offset and removed the temporary fixed width from the single-post mobile TOC placeholder wrapper.

## 3.0.14 - 2026-04-15
- Repositioned the mobile single search trigger back to the right edge and moved the TOC control below it with deeper sticky offsets.
- Removed the extra open-state TOC toggle background and loosened the shared compact TOC button sizing.
- Restored a clearer material-style floating label treatment for the newsletter email field.

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
