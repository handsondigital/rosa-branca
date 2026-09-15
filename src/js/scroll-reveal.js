// Lightweight fade/rise-in for [data-reveal] sections as they enter the
// viewport. Progressive enhancement: the hidden state is scoped under a
// `has-scroll-reveal` class this module adds to <html> itself, so without
// JS (or on IntersectionObserver/matchMedia support failure) sections stay
// at their default, fully-visible CSS state — never hidden-by-default in
// scroll-reveal.css itself. prefers-reduced-motion skips the effect
// entirely for the same reason motion.css/mobile-menu.js etc. do: skip on
// mount, always safe to skip. IntersectionObserver is compositor-adjacent
// (no scroll-event listener, no per-frame layout read) — the browser does
// the intersection math off the main thread.
export function initScrollReveal() {
  const targets = document.querySelectorAll("[data-reveal]");
  if (!targets.length) return;

  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;
  if (prefersReducedMotion || !("IntersectionObserver" in window)) return;

  document.documentElement.classList.add("has-scroll-reveal");

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
}
