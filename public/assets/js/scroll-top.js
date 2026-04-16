(() => {
	"use strict";

	const button = document.querySelector("[data-scroll-top]");
	const footerTopSection = document.querySelector(".ft-top");

	if (!button) {
		return;
	}

	let viewportHeight = window.innerHeight;
	let lastY = window.scrollY || window.pageYOffset || 0;
	let upwardGestures = 0;
	let downwardGestures = 0;
	let lastDirection = "";
	let ticking = false;
	let scrollFrame = null;
	let idleTimer = null;
	let isVisible = false;

	const getCurrentY = () => window.scrollY || window.pageYOffset || 0;

	const clearIdleTimer = () => {
		if (!idleTimer) return;
		window.clearTimeout(idleTimer);
		idleTimer = null;
	};

	const setVisible = (nextVisible) => {
		if (document.activeElement === button && !nextVisible) {
			return;
		}

		isVisible = nextVisible;
		button.classList.toggle("is-visible", nextVisible);
	};

	const setCompact = (isCompact) => {
		button.classList.toggle("is-compact", isCompact);
	};

	const updateFooterDocking = () => {
		if (!footerTopSection || !isVisible) {
			button.style.setProperty("--scroll-top-offset-y", "0px");
			button.classList.remove("is-near-footer");
			return;
		}

		const footerRect = footerTopSection.getBoundingClientRect();
		const buttonHeight = button.offsetHeight || 48;
		const restingCenterY = viewportHeight - 32 - buttonHeight / 2;
		const overlap = Math.max(0, restingCenterY - footerRect.top);

		button.style.setProperty("--scroll-top-offset-y", `${-overlap}px`);
		button.classList.toggle("is-near-footer", overlap > 0);
	};

	const scheduleIdleReveal = (thresholdReached) => {
		clearIdleTimer();

		if (!thresholdReached) return;

		idleTimer = window.setTimeout(() => {
			setVisible(true);
			setCompact(false);
			updateFooterDocking();
		}, 540);
	};

	const updateVisibility = () => {
		ticking = false;

		const currentY = getCurrentY();
		const threshold = Math.max(viewportHeight * 0.9, 420);
		const thresholdReached = currentY > threshold;
		const delta = currentY - lastY;

		if (!thresholdReached) {
			upwardGestures = 0;
			downwardGestures = 0;
			lastDirection = "";
			clearIdleTimer();
			setCompact(false);
			setVisible(false);
			updateFooterDocking();
			lastY = currentY;
			return;
		}

		if (delta > 16) {
			if (lastDirection !== "down") {
				lastDirection = "down";
				downwardGestures = 0;
			}

			downwardGestures += 1;
			upwardGestures = 0;
			setCompact(true);
			if (downwardGestures >= 2) {
				setVisible(false);
			}
			scheduleIdleReveal(thresholdReached);
		} else if (delta < -14) {
			clearIdleTimer();

			if (lastDirection !== "up") {
				lastDirection = "up";
				upwardGestures = 0;
			}

			upwardGestures += 1;
			downwardGestures = 0;

			if (upwardGestures >= 2) {
				setVisible(true);
				setCompact(false);
			}
		} else {
			scheduleIdleReveal(thresholdReached);
		}

		if (!isVisible && currentY > threshold * 1.35) {
			setVisible(true);
		}

		updateFooterDocking();
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

		const startY = getCurrentY();
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
		clearIdleTimer();
		button.classList.add("is-pressed");
		window.setTimeout(() => {
			button.classList.remove("is-pressed");
		}, 180);
		setVisible(false);
		setCompact(false);
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
