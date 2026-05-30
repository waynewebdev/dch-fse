(function () {
	'use strict';

	// Auto-update copyright year in footer.
	document.querySelectorAll('[data-dch-year]').forEach(function (el) {
		el.textContent = new Date().getFullYear();
	});

	/* ----------------------------------------------------------------------
	 * Scroll-to-top button. Reveals after the user has scrolled roughly one
	 * viewport, smooth-scrolls back to the top on click.
	 * ---------------------------------------------------------------------- */
	var scrollTopBtn = document.querySelector('[data-dch-scroll-top]');
	if (scrollTopBtn) {
		scrollTopBtn.removeAttribute('hidden');

		var prefersReduced = window.matchMedia
			&& window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var visible = false;

		function updateScrollTop() {
			var threshold = Math.max(window.innerHeight || 600, 400);
			var should = window.scrollY > threshold;
			if (should !== visible) {
				visible = should;
				scrollTopBtn.classList.toggle('is-visible', visible);
			}
		}

		scrollTopBtn.addEventListener('click', function (e) {
			e.preventDefault();
			if (prefersReduced || !('scrollBehavior' in document.documentElement.style)) {
				window.scrollTo(0, 0);
			} else {
				window.scrollTo({ top: 0, behavior: 'smooth' });
			}
		});

		window.addEventListener('scroll', updateScrollTop, { passive: true });
		updateScrollTop();
	}

	var header = document.querySelector('[data-dch-header]');
	if (!header) return;

	// Sticky shadow on scroll.
	var stuck = false;
	function onScroll() {
		var should = window.scrollY > 4;
		if (should !== stuck) {
			stuck = should;
			header.classList.toggle('is-stuck', stuck);
		}
	}
	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	// Desktop dropdowns: keyboard + click toggle. (Hover handled in CSS.)
	header.querySelectorAll('.dch-nav__item--has-children').forEach(function (item) {
		var link = item.querySelector(':scope > .dch-nav__link');
		var caret = item.querySelector(':scope > .dch-nav__sub-toggle');

		function close() { item.classList.remove('dch-nav__item--open'); }
		function open()  { closeAllDesktop(); item.classList.add('dch-nav__item--open'); }
		function toggle(e) {
			e.preventDefault();
			item.classList.contains('dch-nav__item--open') ? close() : open();
		}

		// Caret button toggles without navigating.
		if (caret) caret.addEventListener('click', toggle);

		// Keyboard: Enter/Space on the link toggles the submenu.
		link.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') toggle(e);
			if (e.key === 'Escape') close();
		});
	});

	function closeAllDesktop() {
		header.querySelectorAll('.dch-nav__item--open').forEach(function (el) {
			el.classList.remove('dch-nav__item--open');
		});
	}

	document.addEventListener('click', function (e) {
		if (!header.contains(e.target)) closeAllDesktop();
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeAllDesktop();
	});

	// Mobile menu.
	var menu = document.getElementById('dch-mobile-menu');
	var hamburger = header.querySelector('.dch-header__hamburger');
	if (menu && hamburger) {
		menu.removeAttribute('hidden');

		function setOpen(isOpen) {
			document.body.classList.toggle('dch-mobile-open', isOpen);
			hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			hamburger.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
		}

		hamburger.addEventListener('click', function () {
			setOpen(!document.body.classList.contains('dch-mobile-open'));
		});

		menu.querySelectorAll('[data-dch-mobile-close]').forEach(function (el) {
			el.addEventListener('click', function () { setOpen(false); });
		});

		// Close on link click (so anchor nav inside the panel feels right).
		menu.querySelectorAll('a').forEach(function (a) {
			a.addEventListener('click', function () { setOpen(false); });
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') setOpen(false);
		});

		// Submenu toggles.
		menu.querySelectorAll('.dch-mobile__sub-toggle').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var li = btn.closest('.dch-mobile__item--has-children');
				if (!li) return;
				var open = li.classList.toggle('dch-mobile__item--open');
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		});
	}
})();
