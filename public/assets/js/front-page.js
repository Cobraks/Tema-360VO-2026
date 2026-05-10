(function () {
	"use strict";

	const hero = document.querySelector(".home-hero");
	const gallery = document.querySelector(".home-hero__gallery");

	if (!gallery) return;

	const viewport = gallery.querySelector(".home-hero__viewport");
	const slides = Array.from(gallery.querySelectorAll(".home-hero__slide"));
	const prevButton = gallery.querySelector(".home-hero__control--prev");
	const nextButton = gallery.querySelector(".home-hero__control--next");
	const toggleButton = gallery.querySelector(".home-hero__control--toggle");
	const dots = Array.from(gallery.querySelectorAll(".home-hero__dot"));
	const panel = gallery.querySelector(".home-hero__vehicle-panel");
	const logoBox = gallery.querySelector(".home-hero__vehicle-logo");
	const titleEl = gallery.querySelector(".home-hero__vehicle-title");
	const subtitleEl = gallery.querySelector(".home-hero__vehicle-subtitle");
	const priceEl = gallery.querySelector(".home-hero__vehicle-price");
	const priceLabelEl = gallery.querySelector(".home-hero__vehicle-price-label");
	const linkEl = gallery.querySelector(".home-hero__vehicle-link");
	const kickerEl = gallery.querySelector(".home-hero__vehicle-kicker");
	const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

	let cars = [];
	try {
		cars = JSON.parse(gallery.getAttribute("data-home-hero-cars") || "[]");
	} catch (error) {
		cars = [];
	}

	const slideCount = Math.min(slides.length, cars.length || slides.length);
	const canRotate = slideCount > 1;
	let currentIndex = 0;
	let timer = null;
	let isPaused = prefersReducedMotion.matches;
	let isTransitioning = false;

	function setText(element, value) {
		if (!element) return;
		element.textContent = value || "";
	}

	function clearTimer() {
		if (!timer) return;
		window.clearTimeout(timer);
		timer = null;
	}

	function getVehicleTitle(car) {
		return [car && car.marca, car && car.modelo].filter(Boolean).join(" ") || "Vehículo destacado";
	}

	function getMonthlyText(car) {
		return car && car.cuota ? "desde " + car.cuota + " al mes" : "Financiación a medida";
	}

	function renderLogo(car) {
		if (!logoBox) return;
		logoBox.textContent = "";

		if (car && car.logo) {
			const img = document.createElement("img");
			img.src = car.logo;
			img.alt = "";
			img.loading = "lazy";
			img.decoding = "async";
			logoBox.appendChild(img);
			return;
		}

		const fallback = document.createElement("span");
		fallback.textContent = ((car && car.marca) || "E").trim().charAt(0).toUpperCase();
		logoBox.appendChild(fallback);
	}

	function updatePanel(index) {
		if (!panel) return;
		const car = cars[index] || {};

		renderLogo(car);
		setText(kickerEl, "");
		setText(titleEl, getVehicleTitle(car));
		setText(subtitleEl, car.version || "Vehículo revisado por Escarpa Motor");
		setText(priceLabelEl, "Cuota mensual");
		setText(priceEl, getMonthlyText(car));

		if (linkEl) {
			const fallbackHref = linkEl.getAttribute("href") || "/";
			linkEl.href = car.link || fallbackHref;
			linkEl.setAttribute("aria-label", car.link ? "Ver ficha de " + getVehicleTitle(car) : "Ver vehículos disponibles");
		}

	}

	function updateControls(index) {
		dots.forEach((dot, dotIndex) => {
			dot.setAttribute("aria-current", dotIndex === index ? "true" : "false");
		});
	}

	function updateToggleButton() {
		if (!toggleButton) return;
		toggleButton.setAttribute("aria-pressed", isPaused ? "true" : "false");
		toggleButton.setAttribute("aria-label", isPaused ? "Reanudar galería" : "Pausar galería");
		toggleButton.innerHTML = isPaused
			? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7L8 5Z"></path></svg>'
			: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h4v14H6V5Zm8 0h4v14h-4V5Z"></path></svg>';
	}

	function preloadNextImage(index) {
		const nextCar = cars[(index + 1) % slideCount];
		if (!nextCar || !nextCar.img) return;
		const image = new Image();
		image.decoding = "async";
		image.src = nextCar.img;
	}

	function setActiveSlide(nextIndex) {
		if (!slideCount || isTransitioning) return;

		const normalizedIndex = (nextIndex + slideCount) % slideCount;
		if (normalizedIndex === currentIndex) return;

		const previousSlide = slides[currentIndex];
		const nextSlide = slides[normalizedIndex];
		if (!nextSlide) return;

		isTransitioning = true;
		if (previousSlide) {
			previousSlide.classList.remove("is-active");
			previousSlide.classList.add("is-exiting");
			previousSlide.setAttribute("aria-hidden", "true");
		}

		nextSlide.classList.add("is-active");
		nextSlide.classList.remove("is-exiting");
		nextSlide.setAttribute("aria-hidden", "false");

		currentIndex = normalizedIndex;
		updateControls(currentIndex);
		updatePanel(currentIndex);
		preloadNextImage(currentIndex);

		window.setTimeout(function () {
			if (previousSlide) previousSlide.classList.remove("is-exiting");
			isTransitioning = false;
		}, prefersReducedMotion.matches ? 20 : 740);
	}

	function startAuto() {
		clearTimer();
		if (!canRotate || isPaused || prefersReducedMotion.matches) return;
		timer = window.setTimeout(function () {
			setActiveSlide(currentIndex + 1);
			startAuto();
		}, 6500);
	}

	function pauseAuto(manual) {
		clearTimer();
		if (manual) {
			isPaused = true;
			updateToggleButton();
		}
	}

	function resumeAuto(manual) {
		if (prefersReducedMotion.matches) return;
		if (manual) {
			isPaused = false;
			updateToggleButton();
		}
		startAuto();
	}

	function goTo(index) {
		setActiveSlide(index);
		startAuto();
	}

	function setupControls() {
		if (!canRotate) {
			[prevButton, nextButton, toggleButton].forEach((button) => {
				if (button) button.hidden = true;
			});
			const dotsWrap = gallery.querySelector(".home-hero__dots");
			if (dotsWrap) dotsWrap.hidden = true;
			return;
		}

		if (prevButton) {
			prevButton.addEventListener("click", function () {
				goTo(currentIndex - 1);
			});
		}

		if (nextButton) {
			nextButton.addEventListener("click", function () {
				goTo(currentIndex + 1);
			});
		}

		if (toggleButton) {
			toggleButton.addEventListener("click", function () {
				if (isPaused) {
					resumeAuto(true);
				} else {
					pauseAuto(true);
				}
			});
		}

		dots.forEach((dot) => {
			dot.addEventListener("click", function () {
				const index = Number.parseInt(dot.getAttribute("data-dot-index"), 10);
				if (Number.isNaN(index)) return;
				goTo(index);
			});
		});

		if (viewport) {
			viewport.addEventListener("keydown", function (event) {
				if (event.key === "ArrowLeft") {
					event.preventDefault();
					goTo(currentIndex - 1);
				}

				if (event.key === "ArrowRight") {
					event.preventDefault();
					goTo(currentIndex + 1);
				}

				if (event.key === " " || event.key === "Spacebar") {
					event.preventDefault();
					if (isPaused) {
						resumeAuto(true);
					} else {
						pauseAuto(true);
					}
				}
			});
		}

		gallery.addEventListener("pointerenter", function () {
			if (!isPaused) clearTimer();
		});

		gallery.addEventListener("pointerleave", function () {
			if (!isPaused) startAuto();
		});
	}

	function init() {
		slides.forEach((slide, index) => {
			slide.classList.toggle("is-active", index === 0);
			slide.classList.remove("is-exiting");
			slide.setAttribute("aria-hidden", index === 0 ? "false" : "true");
		});

		updatePanel(0);
		updateControls(0);
		updateToggleButton();
		setupControls();
		startAuto();
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init, { once: true });
	} else {
		init();
	}

	document.addEventListener("visibilitychange", function () {
		if (document.hidden) {
			clearTimer();
			return;
		}
		startAuto();
	});

	function handleMotionPreferenceChange() {
		isPaused = prefersReducedMotion.matches;
		updateToggleButton();
		if (isPaused) {
			clearTimer();
		} else {
			startAuto();
		}
	}

	if (typeof prefersReducedMotion.addEventListener === "function") {
		prefersReducedMotion.addEventListener("change", handleMotionPreferenceChange);
	} else if (typeof prefersReducedMotion.addListener === "function") {
		prefersReducedMotion.addListener(handleMotionPreferenceChange);
	}

	window.addEventListener("pagehide", clearTimer);

	if (hero) {
		hero.classList.add("is-ready");
	}
})();
