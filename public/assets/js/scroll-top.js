(() => {
	"use strict";

	const button = document.querySelector("[data-scroll-top]");

	if (!button) {
		return;
	}

	let viewportHeight = window.innerHeight;
	let ticking = false;
	let scrollFrame = null;

	const updateVisibility = () => {
		ticking = false;
		const threshold = Math.max(viewportHeight * 0.9, 420);
		const isVisible = (window.scrollY || window.pageYOffset || 0) > threshold;
		button.classList.toggle("is-visible", isVisible);
	};

	const requestVisibilityUpdate = () => {
		if (ticking) return;
		ticking = true;
		window.requestAnimationFrame(updateVisibility);
	};

	const smoothScrollToTop = () => {
		if (scrollFrame) {
			cancelAnimationFrame(scrollFrame);
			scrollFrame = null;
		}

		const startY = window.pageYOffset || window.scrollY || 0;
		if (startY <= 0) {
			return;
		}

		const duration = Math.min(700, Math.max(420, startY * 0.35));
		const startTime = performance.now();
		const easeOutQuart = (progress) => 1 - Math.pow(1 - progress, 4);

		const step = (now) => {
			const progress = Math.min(1, (now - startTime) / duration);
			const eased = easeOutQuart(progress);
			const nextY = Math.max(0, startY * (1 - eased));

			window.scrollTo(0, nextY);
			document.documentElement.scrollTop = nextY;
			document.body.scrollTop = nextY;

			if (progress < 1) {
				scrollFrame = requestAnimationFrame(step);
				return;
			}

			scrollFrame = null;
			window.scrollTo(0, 0);
			document.documentElement.scrollTop = 0;
			document.body.scrollTop = 0;
		};

		scrollFrame = requestAnimationFrame(step);
	};

	button.addEventListener("click", () => {
		button.classList.add("is-pressed");
		window.setTimeout(() => {
			button.classList.remove("is-pressed");
		}, 180);
		smoothScrollToTop();
	});

	window.addEventListener("scroll", requestVisibilityUpdate, { passive: true });
	window.addEventListener(
		"resize",
		() => {
			viewportHeight = window.innerHeight;
			requestVisibilityUpdate();
		},
		{ passive: true },
	);
	window.addEventListener("load", requestVisibilityUpdate);

	updateVisibility();
})();
