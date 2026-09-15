// Lightweight fade/rise-in as elements enter the viewport. Two shapes:
//   - [data-reveal] — the element itself fades in (About, Fale Conosco: one
//     static block, no internal stagger).
//   - [data-reveal-group] + [data-reveal-item] children — the group is
//     purely the IntersectionObserver trigger (never fades itself); its
//     items fade in staggered, one at a time, once the group intersects
//     (Recipes/Products: the carousel cards, not the group wrapper).
//     The group is deliberately NOT the card itself: the Recipes/Products
//     "peek" layout intentionally bleeds the first card mostly off-screen
//     at rest (carousel.css) — observing that card directly could sit
//     below the intersection threshold indefinitely. `.home-section__media`
//     (the whole, unclipped media column) is always a well-behaved
//     intersection target regardless of that internal crop.
//
// Progressive enhancement: the hidden state is scoped under a
// `has-scroll-reveal` class this module adds to <html> itself, so without
// JS (or on IntersectionObserver/matchMedia support failure) everything
// stays at its default, fully-visible CSS state — never hidden-by-default
// in scroll-reveal.css itself. prefers-reduced-motion skips the effect
// entirely for the same reason motion.css/mobile-menu.js etc. do: skip on
// mount, always safe to skip. IntersectionObserver is compositor-adjacent
// (no scroll-event listener, no per-frame layout read) — the browser does
// the intersection math off the main thread.
//
// Query is deferred one frame (requestAnimationFrame) rather than run at
// module-eval time: on Home, the [data-reveal-item] cards live *inside* the
// [data-carousel-mount] container that mount-carousel.jsx replaces wholesale
// with freshly-rendered React nodes (flushSync'd, but still a later <script
// type="module"> than this one, hence a later synchronous task within the
// same "before first paint" window). Querying immediately would style/
// observe the pre-mount server-rendered nodes, discarded a moment later.
// One rAF runs after every same-frame module's synchronous top-level code
// has finished, which is enough to land after that swap.
export function initScrollReveal() {
  requestAnimationFrame(() => {
    const targets = document.querySelectorAll("[data-reveal], [data-reveal-group]");
    if (!targets.length) return;

    const prefersReducedMotion = window.matchMedia(
      "(prefers-reduced-motion: reduce)"
    ).matches;
    if (prefersReducedMotion || !("IntersectionObserver" in window)) return;

    document.documentElement.classList.add("has-scroll-reveal");

    const groupCounts = new WeakMap();
    document.querySelectorAll("[data-reveal-item]").forEach((item) => {
      const group = item.closest("[data-reveal-group]") || item.parentElement;
      const index = groupCounts.get(group) || 0;
      item.style.setProperty("--reveal-index", index);
      groupCounts.set(group, index + 1);
    });

    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          entry.target.classList.add("is-revealed");
          observer.unobserve(entry.target);
        }
      },
      { threshold: 0.15, rootMargin: "0px 0px -80px 0px" }
    );

    targets.forEach((target) => observer.observe(target));
  });
}
