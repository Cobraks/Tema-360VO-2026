# Changelog

## 3.0.4 - 2026-04-15
- Fixed the stylesheet dependency chain so `blog.css` loads after `pages.css` and can safely override TOC layout modifiers in blog single posts.
- Removed legacy TOC toggle radius/hidden-state blocks that no longer match the current interaction.
- Refined shared TOC list/link/number styles for a cleaner hover state across pages and single posts.

## 3.0.3 - 2026-04-15
- Unified the table of contents markup so pages and blog single posts use the same theme component.
- Enabled the TOC by default in `single.php` through a local flag and added the `toc-container--single` modifier for post-specific layout overrides.
- Moved dynamic TOC generation back to the shared theme script and removed the duplicate single-post TOC logic from `blog.js`.
- Aligned the internal theme asset version with the stylesheet version.
