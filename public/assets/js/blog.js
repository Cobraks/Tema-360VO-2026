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
				".panel--subscribe .newsletter-form__field input[type='email']",
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

		const stripWpcf7Hash = () => {
			if (!window.location.hash || !window.location.hash.startsWith("#wpcf7-f")) {
				return;
			}

			window.history.replaceState({}, document.title, `${window.location.pathname}${window.location.search}`);
		};

		const syncPanelLabels = (panel) => {
			panel.querySelectorAll(".newsletter-form__field input[type='email']").forEach((input) => {
				const label = input.closest("label");
				if (!label) return;
				label.classList.toggle("has-value", input.value.trim() !== "");
			});
		};

		const clearAjaxState = (form) => {
			form.querySelectorAll(".wpcf7-not-valid").forEach((field) => {
				field.classList.remove("wpcf7-not-valid");
				field.setAttribute("aria-invalid", "false");
			});

			form.querySelectorAll(".wpcf7-not-valid-tip").forEach((tip) => tip.remove());

			const responseOutput = form.querySelector(".wpcf7-response-output");
			if (responseOutput) {
				responseOutput.className = "wpcf7-response-output";
				responseOutput.textContent = "";
				responseOutput.setAttribute("aria-hidden", "true");
			}
		};

		const setAjaxResponse = (form, status, message) => {
			const responseOutput = form.querySelector(".wpcf7-response-output");
			if (!responseOutput) {
				return;
			}

			const statusClassMap = {
				mail_sent: "wpcf7-mail-sent-ok",
				validation_failed: "wpcf7-validation-errors",
				acceptance_missing: "wpcf7-acceptance-missing",
				spam: "wpcf7-spam-blocked",
				mail_failed: "wpcf7-mail-sent-ng",
				aborted: "wpcf7-aborted",
			};

			responseOutput.className = "wpcf7-response-output";
			if (statusClassMap[status]) {
				responseOutput.classList.add(statusClassMap[status]);
			}

			responseOutput.textContent = message || "";
			responseOutput.setAttribute("aria-hidden", message ? "false" : "true");
		};

		const applyInvalidFields = (form, invalidFields = []) => {
			invalidFields.forEach((fieldData) => {
				if (!fieldData?.field) return;

				const escapedFieldName =
					typeof CSS !== "undefined" && typeof CSS.escape === "function"
						? CSS.escape(fieldData.field)
						: fieldData.field.replace(/"/g, '\\"');

				const field =
					form.querySelector(`[name="${escapedFieldName}"]`) ||
					form.querySelector(`[name="${fieldData.field}"]`);

				if (!field) {
					return;
				}

				field.classList.add("wpcf7-not-valid");
				field.setAttribute("aria-invalid", "true");

				const wrap = field.closest(".wpcf7-form-control-wrap") || field.parentElement;
				if (!wrap) {
					return;
				}

				const tip = document.createElement("span");
				tip.className = "wpcf7-not-valid-tip";
				tip.textContent = fieldData.message || "Revisa este campo.";
				wrap.appendChild(tip);
			});
		};

		const enhanceAjaxSubmit = (panel) => {
			const form = panel.querySelector(".wpcf7-form");
			if (!form || form.dataset.newsletterAjaxEnhanced === "true") {
				return;
			}

			const formId = form.querySelector("input[name='_wpcf7']")?.value;
			if (!formId || !window.fetch || !window.FormData) {
				return;
			}

			form.dataset.newsletterAjaxEnhanced = "true";

			form.addEventListener(
				"submit",
				async (event) => {
					event.preventDefault();
					event.stopImmediatePropagation();

					const submitButtons = form.querySelectorAll("input[type='submit'], button[type='submit']");
					const apiRootRaw =
						window.wpcf7?.api?.root ||
						window.wpcf7?.apiSettings?.root ||
						`${window.location.origin}/wp-json/contact-form-7/v1/`;
					const apiRoot = apiRootRaw.endsWith("/") ? apiRootRaw : `${apiRootRaw}/`;
					const endpoint = `${apiRoot}contact-forms/${formId}/feedback`;
					const requestBody = new FormData(form);

					clearAjaxState(form);
					stripWpcf7Hash();
					submitButtons.forEach((button) => {
						button.disabled = true;
						button.setAttribute("aria-busy", "true");
					});

					try {
						const response = await window.fetch(endpoint, {
							method: "POST",
							body: requestBody,
							headers: {
								Accept: "application/json, */*;q=0.1",
								"X-Requested-With": "XMLHttpRequest",
							},
							credentials: "same-origin",
						});

						const payload = await response.json();
						const status = payload?.status || "aborted";
						const message = payload?.message || "No se pudo enviar el formulario.";

						if (payload?.posted_data_hash) {
							const hashInput = form.querySelector("input[name='_wpcf7_posted_data_hash']");
							if (hashInput) {
								hashInput.value = payload.posted_data_hash;
							}
						}

						setAjaxResponse(form, status, message);

						if (Array.isArray(payload?.invalid_fields) && payload.invalid_fields.length) {
							applyInvalidFields(form, payload.invalid_fields);
						}

						if (status === "mail_sent") {
							form.reset();
						}

						syncPanelLabels(panel);
					} catch {
						setAjaxResponse(form, "aborted", "No se pudo validar el formulario en este momento.");
					} finally {
						submitButtons.forEach((button) => {
							button.disabled = false;
							button.removeAttribute("aria-busy");
						});
					}
				},
				true,
			);
		};

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

			enhanceAjaxSubmit(panel);
			syncPanelLabels(panel);

			if (trigger) {
				trigger.addEventListener("click", () => openPanel(panel));
			}

			cancelButtons.forEach((button) => {
				button.addEventListener("click", () => closePanel(panel, { returnFocus: true }));
			});
		});

		stripWpcf7Hash();

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
		const mobileQuery = window.matchMedia("(max-width: 1079px)");
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
		const brandTargetUrl = brandHighlight?.getAttribute("data-brand-highlight-link") || "";

		if (!layout || !brandHighlight || !article) {
			return;
		}

		let rafId = 0;
		let currentCompactState = null;
		let activeAnimation = null;
		let activeGhost = null;
		const compactEnterThreshold = 140;
		const compactExitThreshold = 210;

		const cancelActiveAnimation = () => {
			if (!activeAnimation) {
				return;
			}

			activeAnimation.cancel();
			activeAnimation = null;
		};

		const clearGhost = () => {
			if (activeGhost) {
				activeGhost.remove();
				activeGhost = null;
			}

			brandHighlight.style.removeProperty("opacity");
			brandHighlight.style.removeProperty("pointer-events");
		};

		const syncStackMetrics = (isCompact) => {
			if (!desktopQuery.matches || !aside) {
				layout.style.removeProperty("--single-brand-stack-height-current");
				layout.style.removeProperty("--single-brand-stack-gap-current");
				return;
			}

			layout.style.setProperty("--single-brand-stack-height-current", `${Math.ceil(brandHighlight.offsetHeight)}px`);
			layout.style.setProperty("--single-brand-stack-gap-current", isCompact ? "1.75rem" : "2.2rem");
		};

		const syncClickableState = (isCompact) => {
			if (!brandTargetUrl) {
				brandHighlight.removeAttribute("role");
				brandHighlight.removeAttribute("tabindex");
				return;
			}

			if (isCompact) {
				brandHighlight.setAttribute("role", "link");
				brandHighlight.setAttribute("tabindex", "0");
				return;
			}

			brandHighlight.removeAttribute("role");
			brandHighlight.removeAttribute("tabindex");
		};

		const applyCompactState = (shouldCompact) => {
			const firstRect = brandHighlight.getBoundingClientRect();

			const commitState = () => {
				layout.classList.toggle("has-compact-brand", shouldCompact);
				syncStackMetrics(shouldCompact);
				syncClickableState(shouldCompact);
			};

			cancelActiveAnimation();
			clearGhost();

			if (prefersReducedMotion.matches) {
				commitState();
				return;
			}

			commitState();

			const lastRect = brandHighlight.getBoundingClientRect();
			const deltaX = lastRect.left - firstRect.left;
			const deltaY = lastRect.top - firstRect.top;
			const scaleX = firstRect.width > 0 && lastRect.width > 0 ? lastRect.width / firstRect.width : 1;
			const scaleY = firstRect.height > 0 && lastRect.height > 0 ? lastRect.height / firstRect.height : 1;

			const hasMeaningfulChange =
				Math.abs(deltaX) > 1 ||
				Math.abs(deltaY) > 1 ||
				Math.abs(scaleX - 1) > 0.01 ||
				Math.abs(scaleY - 1) > 0.01;

			if (!hasMeaningfulChange) {
				return;
			}

			activeGhost = brandHighlight.cloneNode(true);
			activeGhost.classList.add("brand-highlight--ghost");
			activeGhost.setAttribute("aria-hidden", "true");
			activeGhost.removeAttribute("data-brand-highlight-single");
			activeGhost.removeAttribute("id");
			activeGhost.style.position = "fixed";
			activeGhost.style.left = `${firstRect.left}px`;
			activeGhost.style.top = `${firstRect.top}px`;
			activeGhost.style.width = `${firstRect.width}px`;
			activeGhost.style.height = `${firstRect.height}px`;
			activeGhost.style.margin = "0";
			activeGhost.style.pointerEvents = "none";
			activeGhost.style.zIndex = "999";
			activeGhost.style.willChange = "transform, opacity";
			activeGhost.style.transformOrigin = "top right";
			document.body.appendChild(activeGhost);

			brandHighlight.style.opacity = "0";
			brandHighlight.style.pointerEvents = "none";

			activeAnimation = activeGhost.animate(
				[
					{
						transformOrigin: "top right",
						transform: "translate(0, 0) scale(1, 1)",
						opacity: 1,
					},
					{
						transformOrigin: "top right",
						opacity: 0.98,
						transform: `translate(${deltaX}px, ${deltaY}px) scale(${scaleX}, ${scaleY})`,
					},
				],
				{
					duration: 520,
					easing: "cubic-bezier(.22, 1, .36, 1)",
					fill: "none",
				},
			);

			activeAnimation.onfinish = () => {
				activeAnimation = null;
				clearGhost();
			};

			activeAnimation.oncancel = () => {
				activeAnimation = null;
				clearGhost();
			};
		};

		const syncState = () => {
			rafId = 0;

			if (!desktopQuery.matches) {
				cancelActiveAnimation();
				clearGhost();
				layout.classList.remove("has-compact-brand");
				syncStackMetrics(false);
				syncClickableState(false);
				currentCompactState = false;
				return;
			}

			const articleTop = article.getBoundingClientRect().top;
			const shouldCompact = currentCompactState
				? articleTop <= compactExitThreshold
				: articleTop <= compactEnterThreshold;

			if (currentCompactState === null) {
				layout.classList.toggle("has-compact-brand", shouldCompact);
				syncStackMetrics(shouldCompact);
				syncClickableState(shouldCompact);
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

		brandHighlight.addEventListener("click", (event) => {
			if (!layout.classList.contains("has-compact-brand") || !brandTargetUrl) {
				return;
			}

			if (event.target.closest("a, button, input, select, textarea, summary")) {
				return;
			}

			window.location.assign(brandTargetUrl);
		});

		brandHighlight.addEventListener("keydown", (event) => {
			if (!layout.classList.contains("has-compact-brand") || !brandTargetUrl) {
				return;
			}

			if (event.key !== "Enter" && event.key !== " ") {
				return;
			}

			event.preventDefault();
			window.location.assign(brandTargetUrl);
		});

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
