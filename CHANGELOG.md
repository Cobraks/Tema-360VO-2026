# Changelog

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
