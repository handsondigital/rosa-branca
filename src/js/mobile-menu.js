import { initAnimatedDisclosure } from "./animated-disclosure.js";

/**
 * Mobile hamburger menu toggle. Plain DOM/vanilla JS — a show/hide toggle
 * doesn't carry enough state or interaction complexity to justify React
 * (ARCHITECTURE_PLAN.md §3/§16).
 *
 * Open/close animation (header.css: opacity + transform on .is-open) is
 * handled by the shared animated-disclosure.js helper — see its own doc
 * comment for why `hidden` flips relative to the transition instead of
 * instantly.
 */
export function initMobileMenu() {
  const toggle = document.querySelector("[data-mobile-menu-toggle]");
  const menu = document.getElementById("mobile-menu");

  if (!toggle || !menu) return;

  const disclosure = initAnimatedDisclosure({ toggle, panel: menu });
  if (!disclosure) return;

  window.matchMedia("(min-width: 1024px)").addEventListener("change", (event) => {
    if (event.matches) disclosure.close();
  });
}
