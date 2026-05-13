(function () {
	"use strict";

	const SELECTOR = ".map-container[data-map]";
	const LOAD_MARGIN = "1200px 0px";
	const TIMEOUT_MS = 12000;

	function ensureLink(rel, href, crossOrigin) {
		if (!href || !document.head) return;
		const key = rel + "::" + href;
		if (document.head.querySelector('link[data-k="' + key + '"]')) return;

		const link = document.createElement("link");
		link.rel = rel;
		link.href = href;
		link.setAttribute("data-k", key);
		if (crossOrigin) link.crossOrigin = crossOrigin;
		document.head.appendChild(link);
	}

	function warmUpMapOrigins() {
		ensureLink("dns-prefetch", "//www.google.com");
		ensureLink("dns-prefetch", "//www.gstatic.com");
	}

	function init() {
		const container = document.querySelector(SELECTOR);
		if (!container) return;

		const iframe = container.querySelector("iframe[data-src]");
		if (!iframe) return;

		let started = false;
		let timeoutId = null;

		// Warmup barato: lo antes posible pero sin bloquear
		if ("requestIdleCallback" in window) {
			requestIdleCallback(warmUpMapOrigins, { timeout: 1500 });
		} else {
			setTimeout(warmUpMapOrigins, 800);
		}

		const markLoaded = () => {
			if (timeoutId) clearTimeout(timeoutId);
			container.classList.remove("is-loading", "is-timeout");
			container.classList.add("loaded");
		};

		const markTimeout = () => {
			container.classList.remove("is-loading");
			container.classList.add("is-timeout", "loaded");
		};

		const startLoading = () => {
			if (started) return;
			started = true;

			const src = iframe.getAttribute("data-src");
			if (!src) return markLoaded();

			container.classList.add("is-loading");

			timeoutId = setTimeout(markTimeout, TIMEOUT_MS);
			iframe.addEventListener("load", markLoaded, { once: true });

			iframe.setAttribute("src", src);
			iframe.removeAttribute("data-src");
		};

		// Si por navegación/restore ya hubiera src
		if (iframe.getAttribute("src")) {
			queueMicrotask(markLoaded);
			return;
		}

		// IO principal
		if ("IntersectionObserver" in window) {
			const io = new IntersectionObserver(
				(entries) => {
					for (const entry of entries) {
						if (entry.isIntersecting) {
							startLoading();
							io.disconnect();
							break;
						}
					}
				},
				{ rootMargin: LOAD_MARGIN, threshold: 0 },
			);

			io.observe(container);

			// Fallback por interacción (por si IO no dispara)
			const onUserIntent = () => startLoading();
			window.addEventListener("scroll", onUserIntent, {
				passive: true,
				once: true,
			});
			window.addEventListener("pointerdown", onUserIntent, {
				passive: true,
				once: true,
			});
			window.addEventListener("keydown", onUserIntent, { once: true });

			return;
		}

		// Fallback sin IO
		setTimeout(startLoading, 1500);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init, { once: true });
	} else {
		init();
	}
})();
