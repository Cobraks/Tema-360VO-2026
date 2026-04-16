(() => {
	"use strict";

	const button = document.querySelector("[data-scroll-top]");

	if (!button) {
		return;
	}

	let viewportHeight = window.innerHeight;
	let ticking = false;
	let scrollFrame = null;
	let lastY = window.scrollY || window.pageYOffset || 0;
	let upwardGestures = 0;
	let downwardHideTimer = null;
	let lastDirection = "";
	let isVisible = false;

	const setVisible = (nextVisible) => {
		if (document.activeElement === button && !nextVisible) {
			return;
		}

		isVisible = nextVisible;
		button.classList.toggle("is-visible", nextVisible);
	};

	const updateVisibility = () => {
		ticking = false;
		const currentY = window.scrollY || window.pageYOffset || 0;
		const threshold = Math.max(viewportHeight * 0.9, 420);
		const aboveThreshold = currentY > threshold;
		const delta = currentY - lastY;

		if (!aboveThreshold) {
			upwardGestures = 0;
			lastDirection = "";
			if (downwardHideTimer) {
				clearTimeout(downwardHideTimer);
				downwardHideTimer = null;
			}
			setVisible(false);
			lastY = currentY;
			return;
		}

		if (delta > 18) {
			if (lastDirection !== "down") {
				lastDirection = "down";
				upwardGestures = 0;
			}

			if (downwardHideTimer) {
				clearTimeout(downwardHideTimer);
			}

			downwardHideTimer = window.setTimeout(() => {
				setVisible(false);
			}, 140);
		} else if (delta < -14) {
			if (downwardHideTimer) {
				clearTimeout(downwardHideTimer);
				downwardHideTimer = null;
			}

			if (lastDirection !== "up") {
				lastDirection = "up";
				upwardGestures = 0;
			}

			upwardGestures += 1;
			if (upwardGestures >= 2) {
				setVisible(true);
			}
		} else if (!isVisible && (delta === 0 || currentY > threshold * 1.35)) {
			setVisible(true);
		}

		lastY = currentY;
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
		if (downwardHideTimer) {
			clearTimeout(downwardHideTimer);
			downwardHideTimer = null;
		}
		button.classList.add("is-pressed");
		window.setTimeout(() => {
			button.classList.remove("is-pressed");
		}, 180);
		setVisible(false);
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
