(() => {
	"use strict";

	const KEY = "edreamscars_saved_posts_v1";
	let toastEl = null;
	let toastTimer = null;

	function ensureToast() {
		if (toastEl) return toastEl;
		toastEl = document.getElementById("toast");
		if (!toastEl) {
			toastEl = document.createElement("div");
			toastEl.id = "toast";
			toastEl.className = "toast";
			toastEl.setAttribute("role", "status");
			toastEl.setAttribute("aria-live", "polite");
			toastEl.setAttribute("aria-atomic", "true");
			document.body.appendChild(toastEl);
		}
		return toastEl;
	}

	function toast(msg) {
		const el = ensureToast();
		el.textContent = msg;
		el.classList.add("is-visible");
		window.clearTimeout(toastTimer);
		toastTimer = window.setTimeout(
			() => el.classList.remove("is-visible"),
			1400,
		);
	}

	function safeParse(json, fallback) {
		try {
			return JSON.parse(json);
		} catch {
			return fallback;
		}
	}

	function storageAvailable() {
		try {
			const x = "__t__";
			localStorage.setItem(x, x);
			localStorage.removeItem(x);
			return true;
		} catch {
			return false;
		}
	}

	function getSaved() {
		if (!storageAvailable()) return [];
		const raw = localStorage.getItem(KEY);
		const arr = safeParse(raw, []);
		return Array.isArray(arr) ? arr : [];
	}

	function setSaved(arr) {
		if (!storageAvailable()) return;
		localStorage.setItem(KEY, JSON.stringify(arr));
	}

	function setBtnState(btn, pressed) {
		btn.classList.toggle("is-active", pressed);
		btn.classList.toggle("is-saved", pressed);
		btn.setAttribute("aria-pressed", pressed ? "true" : "false");
	}

	function hydrateSaveButtons() {
		const saved = new Set(getSaved().map(String));
		document
			.querySelectorAll('button[data-action="save"][data-id]')
			.forEach((btn) => {
				const id = btn.getAttribute("data-id");
				setBtnState(btn, saved.has(String(id)));
			});
	}

	function initNewsletterFloatingLabels() {
		document
			.querySelectorAll(
				".panel--subscribe .wpcf7-form label input[type='email']",
			)
			.forEach((input) => {
				const label = input.closest("label");
				if (!label) return;

				input.setAttribute("placeholder", " ");

				const sync = () => {
					label.classList.toggle("has-value", input.value.trim() !== "");
				};

				input.addEventListener("input", sync);
				input.addEventListener("blur", sync);
				sync();
			});
	}

	function initNewsletterTips() {
		document.querySelectorAll("[data-tip-toggle]").forEach((btn) => {
			const id = btn.getAttribute("aria-controls");
			if (!id) return;
			const tip = document.getElementById(id);
			if (!tip) return;

			btn.addEventListener("click", () => {
				const expanded = btn.getAttribute("aria-expanded") === "true";
				btn.setAttribute("aria-expanded", expanded ? "false" : "true");
				tip.hidden = expanded;
			});
		});
	}

	function initNewsletterPanels() {
		const panels = Array.from(document.querySelectorAll("[data-newsletter-panel]"));

		if (!panels.length) {
			return;
		}

		const openPanel = (panel) => {
			const trigger = panel.querySelector("[data-newsletter-toggle]");
			const formShell = panel.querySelector("[data-newsletter-form]");
			const emailInput = panel.querySelector("input[type='email']");

			if (!trigger || !formShell) {
				return;
			}

			panel.classList.add("is-open");
			formShell.hidden = false;
			trigger.hidden = true;
			trigger.setAttribute("aria-expanded", "true");

			if (emailInput) {
				window.setTimeout(() => emailInput.focus(), 120);
			}
		};

		const closePanel = (panel, { returnFocus = false } = {}) => {
			const trigger = panel.querySelector("[data-newsletter-toggle]");
			const formShell = panel.querySelector("[data-newsletter-form]");

			if (!trigger || !formShell) {
				return;
			}

			panel.classList.remove("is-open");
			formShell.hidden = true;
			trigger.hidden = false;
			trigger.setAttribute("aria-expanded", "false");

			if (returnFocus) {
				trigger.focus();
			}
		};

		panels.forEach((panel) => {
			const trigger = panel.querySelector("[data-newsletter-toggle]");
			const cancelButtons = panel.querySelectorAll("[data-newsletter-cancel]");

			if (trigger) {
				trigger.addEventListener("click", () => openPanel(panel));
			}

			cancelButtons.forEach((button) => {
				button.addEventListener("click", () => closePanel(panel, { returnFocus: true }));
			});
		});

		document.addEventListener("click", (event) => {
			panels.forEach((panel) => {
				if (!panel.classList.contains("is-open")) {
					return;
				}

				if (!panel.contains(event.target)) {
					closePanel(panel);
				}
			});
		});

		document.addEventListener("keydown", (event) => {
			if (event.key !== "Escape") {
				return;
			}

			panels.forEach((panel) => {
				if (panel.classList.contains("is-open")) {
					closePanel(panel, { returnFocus: true });
				}
			});
		});
	}

	function initSingleFloatingTools() {
		const mobileQuery = window.matchMedia("(max-width: 767px)");
		const searchControl = document.querySelector("[data-single-search]");
		const searchToggle = searchControl?.querySelector(".single-search__toggle");
		const searchForm = searchControl?.querySelector(".search--single");
		const searchInput = searchControl?.querySelector(".search__input");
		const tocContainer = document.querySelector(".toc-container--single");
		const tocToggle = tocContainer?.querySelector(".toc-container__toggle");

		if (!searchControl || !searchToggle || !searchForm || !searchInput || !tocToggle) {
			return;
		}

		let closeSearchTimer = null;

		const setSearchOpenState = (isOpen, { focusInput = false } = {}) => {
			searchControl.classList.toggle("is-open", isOpen);
			searchToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
			searchToggle.setAttribute(
				"aria-label",
				isOpen ? "Cerrar búsqueda en noticias" : "Abrir búsqueda en noticias",
			);

			if (!mobileQuery.matches) {
				searchInput.removeAttribute("tabindex");
				return;
			}

			searchInput.tabIndex = isOpen ? 0 : -1;

			if (closeSearchTimer) {
				window.clearTimeout(closeSearchTimer);
				closeSearchTimer = null;
			}

			if (isOpen && focusInput) {
				closeSearchTimer = window.setTimeout(() => searchInput.focus(), 180);
				return;
			}

			if (!isOpen) {
				searchInput.blur();
			}
		};

		const closeSearch = () => setSearchOpenState(false);

		const syncFloatingTools = () => {
			if (mobileQuery.matches) {
				setSearchOpenState(false);
				return;
			}

			searchControl.classList.remove("is-open");
			searchToggle.setAttribute("aria-expanded", "false");
			searchInput.removeAttribute("tabindex");
		};

		searchToggle.addEventListener("click", () => {
			if (!mobileQuery.matches) {
				searchInput.focus();
				return;
			}

			const willOpen = !searchControl.classList.contains("is-open");
			if (willOpen && tocContainer.classList.contains("open")) {
				tocToggle.click();
			}

			setSearchOpenState(willOpen, { focusInput: willOpen });
		});

		tocToggle.addEventListener("click", () => {
			if (!mobileQuery.matches) return;
			closeSearch();
		});

		document.addEventListener("click", (event) => {
			if (!mobileQuery.matches) return;
			if (searchControl.contains(event.target) || tocContainer.contains(event.target)) {
				return;
			}
			closeSearch();
		});

		document.addEventListener("keydown", (event) => {
			if (event.key !== "Escape") return;
			closeSearch();
		});

		if (typeof mobileQuery.addEventListener === "function") {
			mobileQuery.addEventListener("change", syncFloatingTools);
		}

		syncFloatingTools();
	}

	function initSingleBrandHighlight() {
		const desktopQuery = window.matchMedia("(min-width: 1080px)");
		const layout = document.querySelector(".layout--single");
		const brandHighlight = document.querySelector("[data-brand-highlight-single]");
		const article = document.querySelector(".layout--single .post-card");
		const aside = document.querySelector(".layout--single .aside--single");
		const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

		if (!layout || !brandHighlight || !article) {
			return;
		}

		let rafId = 0;
		let currentCompactState = null;
		let activeAnimation = null;
		let activeGhost = null;

		const clearGhost = ({ cancelAnimation = true } = {}) => {
			if (cancelAnimation && activeAnimation) {
				activeAnimation.cancel();
			}

			activeAnimation = null;

			if (activeGhost) {
				activeGhost.remove();
				activeGhost = null;
			}

			brandHighlight.style.removeProperty("visibility");
		};

		const syncStackMetrics = (isCompact) => {
			if (!desktopQuery.matches || !aside) {
				layout.style.removeProperty("--single-brand-stack-height-current");
				layout.style.removeProperty("--single-brand-stack-gap-current");
				return;
			}

			layout.style.setProperty("--single-brand-stack-height-current", `${Math.ceil(brandHighlight.offsetHeight)}px`);
			layout.style.setProperty("--single-brand-stack-gap-current", isCompact ? ".75rem" : ".9rem");
		};

		const applyCompactState = (shouldCompact) => {
			const firstRect = brandHighlight.getBoundingClientRect();

			layout.classList.toggle("has-compact-brand", shouldCompact);
			syncStackMetrics(shouldCompact);

			if (prefersReducedMotion.matches) {
				clearGhost();
				return;
			}

			const lastRect = brandHighlight.getBoundingClientRect();
			const deltaX = firstRect.left - lastRect.left;
			const deltaY = firstRect.top - lastRect.top;
			const scaleX = firstRect.width > 0 && lastRect.width > 0 ? firstRect.width / lastRect.width : 1;
			const scaleY = firstRect.height > 0 && lastRect.height > 0 ? firstRect.height / lastRect.height : 1;

			clearGhost();

			activeGhost = brandHighlight.cloneNode(true);
			activeGhost.classList.add("brand-highlight--ghost");
			activeGhost.setAttribute("aria-hidden", "true");
			activeGhost.removeAttribute("data-brand-highlight-single");
			activeGhost.style.position = "fixed";
			activeGhost.style.left = `${lastRect.left}px`;
			activeGhost.style.top = `${lastRect.top}px`;
			activeGhost.style.width = `${lastRect.width}px`;
			activeGhost.style.height = `${lastRect.height}px`;
			activeGhost.style.margin = "0";
			activeGhost.style.pointerEvents = "none";
			activeGhost.style.zIndex = "999";
			activeGhost.style.willChange = "transform, opacity";
			layout.appendChild(activeGhost);
			brandHighlight.style.visibility = "hidden";

			activeAnimation = activeGhost.animate(
				[
					{
						transformOrigin: "top right",
						transform: `translate(${deltaX}px, ${deltaY}px) scale(${scaleX}, ${scaleY})`,
						opacity: 0.98,
					},
					{
						transformOrigin: "top right",
						transform: "translate(0, 0) scale(1, 1)",
						opacity: 1,
					},
				],
				{
					duration: 640,
					easing: "cubic-bezier(.22, 1, .36, 1)",
					fill: "both",
				},
			);

			activeAnimation.onfinish = () => {
				clearGhost({ cancelAnimation: false });
			};

			activeAnimation.oncancel = () => {
				clearGhost({ cancelAnimation: false });
			};
		};

		const syncState = () => {
			rafId = 0;

			if (!desktopQuery.matches) {
				clearGhost();
				layout.classList.remove("has-compact-brand");
				syncStackMetrics(false);
				currentCompactState = false;
				return;
			}

			const articleTop = article.getBoundingClientRect().top;
			const shouldCompact = articleTop <= 140;

			if (currentCompactState === null) {
				layout.classList.toggle("has-compact-brand", shouldCompact);
				syncStackMetrics(shouldCompact);
				currentCompactState = shouldCompact;
				return;
			}

			if (currentCompactState === shouldCompact) {
				syncStackMetrics(shouldCompact);
				return;
			}

			currentCompactState = shouldCompact;
			applyCompactState(shouldCompact);
		};

		const requestSync = () => {
			if (rafId) return;
			rafId = window.requestAnimationFrame(syncState);
		};

		window.addEventListener("scroll", requestSync, { passive: true });
		window.addEventListener("resize", requestSync);

		if (typeof desktopQuery.addEventListener === "function") {
			desktopQuery.addEventListener("change", requestSync);
		}

		requestSync();
	}

	async function shareUrl(url, title) {
		const u = url || window.location.href;
		const t = title || document.title || "EdreamsCars";

		if (navigator.share) {
			try {
				await navigator.share({ title: t, url: u });
				toast("Compartido");
				return;
			} catch {}
		}

		if (navigator.clipboard && window.isSecureContext) {
			try {
				await navigator.clipboard.writeText(u);
				toast("Enlace copiado");
				return;
			} catch {}
		}

		prompt("Copia este enlace:", u);
	}

	async function copyCurrentUrl() {
		const u = window.location.href;
		if (navigator.clipboard && window.isSecureContext) {
			try {
				await navigator.clipboard.writeText(u);
				toast("Enlace copiado");
				return;
			} catch {}
		}
		prompt("Copia este enlace:", u);
	}

	hydrateSaveButtons();
	initNewsletterFloatingLabels();
	initNewsletterTips();
	initNewsletterPanels();
	initSingleFloatingTools();
	initSingleBrandHighlight();

	document.addEventListener("click", async (e) => {
		const btn = e.target.closest("[data-action]");
		if (!btn) return;

		const action = btn.getAttribute("data-action");

		if (action === "save") {
			const id = btn.getAttribute("data-id");
			if (!id) return;

			const saved = getSaved().map(String);
			const idx = saved.indexOf(String(id));

			if (idx >= 0) {
				saved.splice(idx, 1);
				setSaved(saved);
				setBtnState(btn, false);
				toast("Eliminado de guardados");
			} else {
				saved.unshift(String(id));
				setSaved(saved);
				setBtnState(btn, true);
				toast("Guardado");
			}
			return;
		}

		if (action === "share") {
			const url = btn.getAttribute("data-url") || window.location.href;
			await shareUrl(url, document.title);
			return;
		}

		if (action === "copy") {
			await copyCurrentUrl();
		}
	});

	const copyBtn = document.getElementById("copy-button");
	if (copyBtn) {
		copyBtn.addEventListener("click", (e) => {
			e.preventDefault();
			copyCurrentUrl();
		});
	}

	const shareBtn = document.getElementById("share-button");
	if (shareBtn) {
		shareBtn.addEventListener("click", (e) => {
			e.preventDefault();
			shareUrl(window.location.href, document.title);
		});
	}
})();
