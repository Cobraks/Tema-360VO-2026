// =====================================================
// Global Variables
// =====================================================
// Ya no usamos jQuery
let isTocToggling = false;

// =====================================================
// Función: Inicializar popup del footer
// =====================================================
function initBotonFlotanteContacto() {
	const popup = document.getElementById("popup");
	const openBtn = document.getElementById("open-popup");
	const closeBtn = document.getElementById("close-popup");

	if (!popup || !openBtn || !closeBtn) return;

	openBtn.addEventListener("click", () => {
		popup.style.display = "flex";
	});

	closeBtn.addEventListener("click", () => {
		popup.style.display = "none";
	});

	// Mejor: escuchar en popup, no en window (menos trabajo global)
	popup.addEventListener("click", (event) => {
		if (event.target === popup) popup.style.display = "none";
	});
}

// =====================================================
// Función: Animación de botones en el pie de página
// =====================================================
function initializeFooterButtonAnimations() {
	const wrapper = document.querySelector(".button--footer-wrapper");
	const buttons = document.querySelectorAll(".button--footer");
	if (!wrapper || !buttons.length) return;

	buttons.forEach((button, index) => {
		button.style.transitionDelay = `${index * 0.15}s`;
	});

	if (!("IntersectionObserver" in window)) {
		buttons.forEach((b) => b.classList.add("animate-in"));
		return;
	}

	const io = new IntersectionObserver(
		(entries) => {
			if (!entries[0]?.isIntersecting) return;
			buttons.forEach((b) => b.classList.add("animate-in"));
			io.disconnect();
		},
		{ rootMargin: "200px 0px", threshold: 0 },
	);

	io.observe(wrapper);
}

// =====================================================
// Función: Eliminar clases ocultas con retraso
// =====================================================
function removeHiddenClassWithDelay() {
	setTimeout(() => {
		document.querySelectorAll(".mobile-nav").forEach((el) => {
			el.classList.remove("initially-hidden");
		});
	}, 100);
}

// =====================================================
// Función: Manejo del menú de navegación móvil
// =====================================================
function initializeMobileMenu() {
	const toggles = document.querySelectorAll(".menu-button, .menu-button-line");
	const closeBtns = document.querySelectorAll(
		".close-button, .close-button-line",
	);
	const backdrop = document.querySelector(".backdrop");
	const mobileNav = document.querySelector(".mobile-nav");
	const body = document.body;

	if (!mobileNav) return;

	toggles.forEach((btn) =>
		btn.addEventListener("click", () => {
			mobileNav.classList.add("open");
			backdrop?.classList.add("open");
			document
				.querySelectorAll(".menu-button, .close-button")
				.forEach((el) => el.classList.add("open"));
			body.classList.add("no-scroll");
		}),
	);

	closeBtns.forEach((btn) =>
		btn.addEventListener("click", () => {
			mobileNav.classList.remove("open");
			backdrop?.classList.remove("open");
			document
				.querySelectorAll(".menu-button, .close-button")
				.forEach((el) => el.classList.remove("open"));
			body.classList.remove("no-scroll");
		}),
	);

	backdrop?.addEventListener("click", (event) => {
		if (
			!mobileNav.contains(event.target) &&
			!Array.from(toggles).some((t) => t.contains(event.target))
		) {
			mobileNav.classList.remove("open");
			backdrop.classList.remove("open");
			body.classList.remove("no-scroll");
		}
	});
}

// =====================================================
// Función: Alternar clases dinámicas para usuarios logueados al hacer scroll
// =====================================================
function toggleScrolledLoggedIn() {
	const scrollY = window.scrollY;
	const targets = document.querySelectorAll(
		"header, aside.buscador, nav.nav-breadcrumb, nav.mobile-nav, .ordenar_por",
	);

	if (document.body.classList.contains("logged-in")) {
		targets.forEach((el) => {
			if (scrollY > 0) el.classList.add("scrolled_logged-in");
			else el.classList.remove("scrolled_logged-in");
		});
	} else {
		const header = document.querySelector("header");
		if (!header) return;
		if (scrollY > 0) header.classList.add("scrolled");
		else header.classList.remove("scrolled");
	}
}

// =====================================================
// Función: Manejo del scroll y visibilidad dinámica de elementos
// =====================================================
function handleScrollBehavior() {
	let lastScrollTop = 0;
	let lastScrollPos = 0;

	// Throttle por rAF: reduce trabajo main-thread
	let ticking = false;

	window.addEventListener(
		"scroll",
		() => {
			if (ticking) return;
			ticking = true;

			requestAnimationFrame(() => {
				ticking = false;

				if (isTocToggling) return;
				toggleScrolledLoggedIn();

				const st = window.scrollY;

				if (st > lastScrollTop && st > lastScrollPos + 1) {
					document.querySelector(".nav-breadcrumb")?.classList.add("hidden");
					document
						.querySelector("aside.buscador")
						?.classList.add("hidden-breadcrumb");
					document.body.classList.add("hidden");
					lastScrollPos = st;
				} else if (st < lastScrollPos - 1) {
					document.querySelector(".nav-breadcrumb")?.classList.remove("hidden");
					document
						.querySelector("aside.buscador")
						?.classList.remove("hidden-breadcrumb");
					document.body.classList.remove("hidden");
					lastScrollPos = st;
				}

				lastScrollTop = Math.max(0, st);
			});
		},
		{ passive: true },
	);
}

// =====================================================
// Función: Inicializar la Tabla de Contenidos (TOC)
// Optimizada: sin scroll handler que mida layout
// =====================================================
function initializeTableOfContents() {
	const tocContainer = document.querySelector(".toc-container__content");
	const tocContainerElement = document.getElementById("toc-container");
	if (!tocContainer || !tocContainerElement) return;

	const content = document.querySelector(".custom-page__content");
	if (!content) return;

	const allHeadings = Array.from(
		content.querySelectorAll("h2, h3, h4, h5, h6"),
	).filter((h) => !h.closest(".vehicle-card__container"));

	if (!allHeadings.length) return;

	document.querySelector(".toc-container__toggle")?.classList.add("show");

	let tocHtml = "<ul class='toc__list'>";
	let idCounter = 0;
	let headingNumbers = [0, 0, 0, 0, 0];
	const idPrefix = "toc-heading-";

	allHeadings.forEach((heading) => {
		const level = parseInt(heading.tagName[1], 10) - 2;
		headingNumbers[level]++;
		for (let i = level + 1; i < headingNumbers.length; i++)
			headingNumbers[i] = 0;

		const headingNumber = headingNumbers.slice(0, level + 1).join(".");
		const id = heading.id || idPrefix + idCounter++;
		heading.id = id;

		const itemClass =
			level === 1 ? "toc__item toc__item--subitem" : "toc__item";

		tocHtml +=
			`<li class="${itemClass}">` +
			`<a href="#${id}" class="toc__link">` +
			`<span class="toc__number">${headingNumber}.</span> ` +
			`${heading.textContent}` +
			`</a>` +
			`</li>`;
	});

	tocHtml += "</ul>";
	tocContainer.innerHTML = tocHtml;

	// Delegación: 1 listener en vez de N
	tocContainer.addEventListener("click", (event) => {
		const link = event.target.closest(".toc__link");
		if (!link) return;

		event.preventDefault();

		const target = document.querySelector(link.getAttribute("href"));
		if (target) {
			// Sin medir layout: scroll-margin-top lo resuelve en CSS
			target.scrollIntoView({ behavior: "smooth", block: "start" });
		}

		tocContainer
			.querySelectorAll(".toc__item")
			.forEach((li) => li.classList.remove("active"));
		link.parentElement.classList.add("active");
		allHeadings.forEach((h) => h.classList.remove("active"));
		target?.classList.add("active");

		// Cerrar si está abierto (móvil)
		if (tocContainer.classList.contains("show")) {
			setTimeout(() => {
				tocContainer.classList.remove("show");
				tocContainerElement.classList.remove("open");
				document.querySelectorAll(".backdrop").forEach((b) => {
					b.classList.remove("open");
					b.style.zIndex = "";
				});
				const toggleText = document.querySelector(
					".toc-container__toggle .toc-container__text",
				);
				if (toggleText) toggleText.textContent = "Mostrar tabla de contenidos";
			}, 250);
		}
	});

	document
		.querySelector(".toc-container__toggle")
		?.addEventListener("click", () => {
			if (isTocToggling) return;
			isTocToggling = true;

			tocContainer.classList.toggle("show");
			tocContainerElement.classList.toggle("open");
			document
				.querySelectorAll(".backdrop")
				.forEach((b) => b.classList.toggle("open"));

			const toggleText = document.querySelector(
				".toc-container__toggle .toc-container__text",
			);

			if (tocContainerElement.classList.contains("open")) {
				document
					.querySelectorAll(".backdrop")
					.forEach((b) => (b.style.zIndex = "97"));
				if (toggleText) toggleText.textContent = "Ocultar tabla de contenidos";
				tocContainer.querySelectorAll(".toc__item").forEach((item, idx) => {
					setTimeout(() => item.classList.add("show"), 40 + idx * 18);
				});
			} else {
				document
					.querySelectorAll(".backdrop")
					.forEach((b) => (b.style.zIndex = ""));
				if (toggleText) toggleText.textContent = "Mostrar tabla de contenidos";
				tocContainer
					.querySelectorAll(".toc__item")
					.forEach((item) => item.classList.remove("show"));
			}

			setTimeout(() => {
				isTocToggling = false;
			}, 250);
		});

	// End-of-container sin scroll: sentinel + IO
	if ("IntersectionObserver" in window) {
		const parent = tocContainerElement.parentElement;
		if (parent) {
			const sentinel = document.createElement("div");
			sentinel.className = "toc-end-sentinel";
			parent.appendChild(sentinel);

			const io = new IntersectionObserver(
				(entries) => {
					if (entries[0]?.isIntersecting) {
						tocContainerElement.classList.add("end-of-container");

						// cierre defensivo
						tocContainer.classList.remove("show");
						tocContainerElement.classList.remove("open");
						document.querySelectorAll(".backdrop").forEach((b) => {
							b.classList.remove("open");
							b.style.zIndex = "";
						});
						const toggleText = document.querySelector(
							".toc-container__toggle .toc-container__text",
						);
						if (toggleText)
							toggleText.textContent = "Mostrar tabla de contenidos";
					} else {
						tocContainerElement.classList.remove("end-of-container");
					}
				},
				{ rootMargin: "0px 0px -15% 0px", threshold: 0 },
			);

			io.observe(sentinel);
		}
	}
}

// =====================================================
// Función: Inicializar el botón de copiar enlace
// =====================================================
function initializeCopyButton() {
	const copyButton = document.getElementById("copy-button");
	const copyButtonText = document.querySelector(".share__button-text");

	if (!copyButton || !copyButtonText) return;

	copyButton.addEventListener("click", async () => {
		try {
			if (navigator.clipboard?.writeText) {
				await navigator.clipboard.writeText(window.location.href);
			} else {
				const tempInput = document.createElement("input");
				tempInput.value = window.location.href;
				document.body.appendChild(tempInput);
				tempInput.select();
				document.execCommand("copy");
				document.body.removeChild(tempInput);
			}

			copyButtonText.textContent = "¡Link copiado!";
			copyButton.disabled = true;

			setTimeout(() => {
				copyButtonText.textContent = "Copiar enlace";
				copyButton.disabled = false;
			}, 3000);
		} catch (_) {}
	});
}

// =====================================================
// Función: Inicializar el botón de compartir
// =====================================================
function initializeShareButton() {
	const shareButton = document.getElementById("share-button");
	if (!shareButton) return;

	shareButton.addEventListener("click", () => {
		if (navigator.share) {
			navigator
				.share({
					title: document.title,
					text: "¡Mira este contenido increíble!",
					url: window.location.href,
				})
				.catch(() => {});
		}
	});
}

// =====================================================
// Formularios CF7: mantener estado is-filled
// =====================================================
function initCf7FilledState() {
	function syncFilled(container) {
		const field = container.querySelector(".input");
		if (!field) return;
		container.classList.toggle("is-filled", field.value.trim().length > 0);
	}

	function init(scope) {
		(scope || document)
			.querySelectorAll(".wpcf7 .inputContainer")
			.forEach(syncFilled);
	}

	init(document);

	document.addEventListener("input", (e) => {
		const el = e.target;
		if (!el?.classList?.contains("input")) return;
		const container = el.closest(".inputContainer");
		if (container) syncFilled(container);
	});

	document.addEventListener("change", (e) => {
		const el = e.target;
		if (!el?.classList?.contains("input")) return;
		const container = el.closest(".inputContainer");
		if (container) syncFilled(container);
	});

	document.addEventListener("wpcf7reset", (e) => init(e.target));
	document.addEventListener("wpcf7mailsent", (e) => init(e.target));
}

// =====================================================
// Inicialización
// =====================================================
document.addEventListener("DOMContentLoaded", () => {
	initBotonFlotanteContacto();

	initializeFooterButtonAnimations();
	removeHiddenClassWithDelay();
	initializeMobileMenu();
	handleScrollBehavior();
	toggleScrolledLoggedIn();

	if (document.querySelector('[data-toc-enabled="1"]')) {
		initializeTableOfContents();
	}

	initializeCopyButton();
	initializeShareButton();

	initCf7FilledState();
});
