(function () {
	'use strict';

	var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	/* ----------------------------------------------------------------------
	 * Trigger helper. Calls `fn(el)` once when `el` is in view. Falls back to
	 * triggering immediately if the element is already in the initial viewport
	 * (so above-the-fold content doesn't wait), and after a safety timeout
	 * regardless (so animations never silently fail to fire).
	 * ---------------------------------------------------------------------- */
	function whenInView(el, fn) {
		var fired = false;
		function trigger() { if (fired) return; fired = true; fn(el); }

		if (prefersReduced) { trigger(); return; }

		var rect = el.getBoundingClientRect();
		var vh = window.innerHeight || document.documentElement.clientHeight;
		if (rect.top < vh && rect.bottom > 0) {
			trigger();
			return;
		}

		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) { trigger(); io.disconnect(); }
				});
			}, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
			io.observe(el);
		}
		// Last-ditch fallback so content never stays hidden.
		setTimeout(trigger, 5000);
	}

	/* ----------------------------------------------------------------------
	 * Char / line / block animation. Mirrors trx_addons fade-in effects.
	 *   - char:  splits text into chars, reveals each with a stagger
	 *   - line:  reveals each direct child as a line with a stagger
	 *   - block: reveals the whole element (no split)
	 * ---------------------------------------------------------------------- */
	function setupAnim(el) {
		if (el.dataset.dchAnimReady) return;
		el.dataset.dchAnimReady = '1';

		var mode = el.getAttribute('data-dch-anim') || 'char';
		var stagger = parseInt(el.getAttribute('data-dch-stagger') || '30', 10);
		var spans = [];

		if (mode === 'block') {
			el.classList.add('dch-anim-block');
			whenInView(el, function () { el.classList.add('is-in'); });
			return;
		}

		if (mode === 'line') {
			var children = el.children.length ? [].slice.call(el.children) : [el];
			children.forEach(function (c) { c.classList.add('dch-anim-line'); spans.push(c); });
		} else {
			var lines = el.children.length ? [].slice.call(el.children) : [el];
			lines.forEach(function (line) {
				var text = line.textContent;
				line.textContent = '';
				for (var i = 0; i < text.length; i++) {
					var ch = text.charAt(i);
					if (ch === ' ') { line.appendChild(document.createTextNode(' ')); continue; }
					var s = document.createElement('span');
					s.className = 'dch-anim-char';
					s.textContent = ch;
					line.appendChild(s);
					spans.push(s);
				}
			});
		}

		whenInView(el, function () {
			if (prefersReduced) { spans.forEach(function (s) { s.classList.add('is-in'); }); return; }
			spans.forEach(function (s, i) {
				setTimeout(function () { s.classList.add('is-in'); }, i * stagger);
			});
		});
	}

	/* ----------------------------------------------------------------------
	 * Counter — count from data-from to data-to over data-duration ms,
	 * triggered when the element scrolls into view.
	 * ---------------------------------------------------------------------- */
	function setupCounter(el) {
		var from = parseInt(el.getAttribute('data-from') || '0', 10);
		var to = parseInt(el.getAttribute('data-to') || '0', 10);
		var duration = parseInt(el.getAttribute('data-duration') || '1500', 10);

		if (prefersReduced) { el.textContent = String(to); return; }

		whenInView(el, function () {
			// setInterval is more reliable than rAF in unfocused/throttled tabs.
			var start = Date.now();
			var iv = setInterval(function () {
				var t = Math.min(1, (Date.now() - start) / duration);
				var eased = 1 - Math.pow(1 - t, 3);
				el.textContent = String(Math.round(from + (to - from) * eased));
				if (t >= 1) clearInterval(iv);
			}, 16);
		});
	}

	/* ----------------------------------------------------------------------
	 * Hero slider — fade transition with autoplay loop and pagination dots.
	 * Mirrors legacy Swiper config: effect: 'fade', loop: true,
	 * autoplay { delay: 7000 }, speed: 600.
	 * ---------------------------------------------------------------------- */
	function initSlider(slider) {
		var slides = [].slice.call(slider.querySelectorAll('.dch-hero__slide'));
		var dots = [].slice.call(slider.querySelectorAll('.dch-hero__dot'));
		if (slides.length < 2) return;

		var autoplay = parseInt(slider.getAttribute('data-autoplay') || '7000', 10);
		var idx = slides.findIndex(function (s) { return s.classList.contains('is-active'); });
		if (idx < 0) idx = 0;
		var timer = null;
		var paused = false;

		function show(next) {
			if (next === idx) return;
			slides[idx].classList.remove('is-active');
			slides[idx].setAttribute('aria-hidden', 'true');
			idx = (next + slides.length) % slides.length;
			slides[idx].classList.add('is-active');
			slides[idx].setAttribute('aria-hidden', 'false');
			dots.forEach(function (d, i) {
				d.classList.toggle('is-active', i === idx);
				d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
			});
		}

		function start() {
			stop();
			if (paused || prefersReduced) return;
			timer = setInterval(function () { show(idx + 1); }, autoplay);
		}
		function stop() { if (timer) { clearInterval(timer); timer = null; } }

		dots.forEach(function (d) {
			d.addEventListener('click', function () {
				show(parseInt(d.getAttribute('data-index'), 10) || 0);
				stop();
				paused = true;
			});
		});

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) stop(); else start();
		});

		start();
	}

	/* ----------------------------------------------------------------------
	 * Gallery — full-width slide carousel with prev/next controls. Active
	 * slide is centered in viewport, prev/next peek on the edges. Loops by
	 * jumping back to the equivalent slide in a duplicated head/tail group
	 * (à la Swiper's loop). Mirrors legacy slidesPerView:2, centered, slide.
	 * ---------------------------------------------------------------------- */
	function initGallery(root) {
		var track = root.querySelector('[data-dch-gallery-track]');
		var viewport = root.querySelector('[data-dch-gallery-viewport]');
		var prev = root.querySelector('[data-dch-gallery-prev]');
		var next = root.querySelector('[data-dch-gallery-next]');
		if (!track || !viewport) return;

		var slides = [].slice.call(track.children);
		if (slides.length < 2) return;

		// Clone head & tail for seamless loop. Three groups total: [tail][orig][head].
		var clonesPerSide = slides.length;
		slides.forEach(function (s) {
			var c = s.cloneNode(true);
			c.setAttribute('aria-hidden', 'true');
			track.appendChild(c);
		});
		slides.forEach(function (s) {
			var c = s.cloneNode(true);
			c.setAttribute('aria-hidden', 'true');
			track.insertBefore(c, track.firstChild);
		});

		var totalSlides = slides.length; // original count
		var idx = 0; // index within the original group
		var animating = false;

		function slideStep() {
			var s = track.children[clonesPerSide]; // first original slide
			return s.getBoundingClientRect().width + parseFloat(getComputedStyle(track).gap || '0');
		}

		function position(instant) {
			var step = slideStep();
			var viewportW = viewport.getBoundingClientRect().width;
			var slideW = step - parseFloat(getComputedStyle(track).gap || '0');
			// center the active slide horizontally
			var offset = (viewportW - slideW) / 2 - step * (clonesPerSide + idx);
			if (instant) {
				track.classList.add('is-instant');
				track.style.transform = 'translateX(' + offset + 'px)';
				// force reflow so transition reactivates next time
				track.offsetWidth;
				track.classList.remove('is-instant');
			} else {
				track.style.transform = 'translateX(' + offset + 'px)';
			}
		}

		function onTransitionEnd() {
			if (idx < 0) {
				idx += totalSlides;
				position(true);
			} else if (idx >= totalSlides) {
				idx -= totalSlides;
				position(true);
			}
			animating = false;
		}
		track.addEventListener('transitionend', onTransitionEnd);

		function go(delta) {
			if (animating) return;
			animating = true;
			idx += delta;
			position(false);
		}

		if (prev) prev.addEventListener('click', function () { go(-1); });
		if (next) next.addEventListener('click', function () { go(1); });

		// Keyboard support on the section root
		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') go(-1);
			if (e.key === 'ArrowRight') go(1);
		});

		// Reposition on resize.
		var rt;
		window.addEventListener('resize', function () {
			clearTimeout(rt);
			rt = setTimeout(function () { position(true); }, 80);
		});

		// Initial layout (delayed slightly so images can size properly).
		position(true);
		setTimeout(function () { position(true); }, 50);
	}

	/* ---------------------------------------------------------------------- */

	ready(function () {
		document.querySelectorAll('[data-dch-anim]').forEach(setupAnim);
		document.querySelectorAll('[data-dch-counter]').forEach(setupCounter);
		document.querySelectorAll('[data-dch-slider]').forEach(initSlider);
		document.querySelectorAll('[data-dch-gallery]').forEach(initGallery);
	});
})();
