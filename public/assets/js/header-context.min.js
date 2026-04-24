function initializeHeaderContextSwitcher() {
	const body = document.body;
	const header = document.querySelector(".site-header");
	const switcher = header?.querySelector("[data-header-context-switcher]");
	const title = header?.querySelector("[data-header-context-title]");
	const navigation = switcher?.querySelector(".site-navigation");

	if (!body || !header || !switcher || !title || !navigation) return;

	const navigationFocusables = Array.from(
		navigation.querySelectorAll("a, button, input, select, textarea, [tabindex]"),
	);
	const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
	const minScrollBeforeContext = 96;
	const deltaThreshold = 7;
	let lastScrollY = window.scrollY;
	let downIntent = 0;
	let upIntent = 0;
	let ticking = false;
	let isActive = false;

	const setNavigationFocusability = (isHidden) => {
		navigation.setAttribute("aria-hidden", isHidden ? "true" : "false");
		title.setAttribute("aria-hidden", isHidden ? "false" : "true");

		if ("inert" in navigation) {
			navigation.inert = isHidden;
		}

		navigationFocusables.forEach((element) => {
			if (isHidden) {
				if (!element.hasAttribute("data-header-context-tabindex")) {
					element.setAttribute(
						"data-header-context-tabindex",
						element.getAttribute("tabindex") ?? "",
					);
				}
				element.setAttribute("tabindex", "-1");
				return;
			}

			const originalTabindex =
				element.getAttribute("data-header-context-tabindex");

			if (originalTabindex === null) return;

			if (originalTabindex === "") {
				element.removeAttribute("tabindex");
			} else {
				element.setAttribute("tabindex", originalTabindex);
			}

			element.removeAttribute("data-header-context-tabindex");
		});
	};

	const setContextActive = (nextActive) => {
		if (isActive === nextActive) return;

		isActive = nextActive;
		body.classList.toggle("header-context-active", nextActive);
		setNavigationFocusability(nextActive);
	};

	const resetAtTop = (scrollY) => {
		if (scrollY > 12) return false;

		downIntent = 0;
		upIntent = 0;
		setContextActive(false);
		return true;
	};

	const updateFromScroll = () => {
		const currentScrollY = Math.max(0, window.scrollY);
		const delta = currentScrollY - lastScrollY;

		ticking = false;

		if (body.classList.contains("toc-scroll-lock")) {
			lastScrollY = currentScrollY;
			return;
		}

		if (resetAtTop(currentScrollY)) {
			lastScrollY = currentScrollY;
			return;
		}

		if (Math.abs(delta) < deltaThreshold) {
			lastScrollY = currentScrollY;
			return;
		}

		if (delta > 0 && currentScrollY > minScrollBeforeContext) {
			downIntent += prefersReducedMotion.matches ? 2 : 1;
			upIntent = 0;

			if (downIntent >= 3) {
				setContextActive(true);
			}
		} else if (delta < 0) {
			upIntent += prefersReducedMotion.matches ? 2 : 1;
			downIntent = 0;

			if (upIntent >= 2) {
				setContextActive(false);
			}
		}

		lastScrollY = currentScrollY;
	};

	const requestUpdate = () => {
		if (ticking) return;
		ticking = true;
		window.requestAnimationFrame(updateFromScroll);
	};

	setNavigationFocusability(false);
	resetAtTop(window.scrollY);
	window.addEventListener("scroll", requestUpdate, { passive: true });
	window.addEventListener("pageshow", () => {
		lastScrollY = window.scrollY;
		resetAtTop(lastScrollY);
	});
}

document.addEventListener("DOMContentLoaded", () => {
	initializeHeaderContextSwitcher();
});
