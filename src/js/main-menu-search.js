import { initAnimatedDisclosure } from "./animated-disclosure.js";

/**
 * Header search icon -> dropdown form toggle. Same animated-open/close
 * behavior as the mobile menu (animated-disclosure.js) — see its doc
 * comment for the actual transition/hidden-attribute mechanics.
 */
export function initMainMenuSearch() {
  const toggle = document.querySelector("[data-search-toggle]");
  const form = document.getElementById("main-menu-search-form");

  if (!toggle || !form) return;

  const disclosure = initAnimatedDisclosure({ toggle, panel: form });
  if (!disclosure) return;

  const input = form.querySelector("input");

  toggle.addEventListener("click", () => {
    if (!disclosure.isOpen()) return;
    // Matches standard "reveal a search field" UX — keyboard/screen-reader
    // users shouldn't need a second Tab press to reach the field they just
    // opened. Deferred a frame so focus doesn't fight the opening
    // transition's own layout/paint work.
    requestAnimationFrame(() => input?.focus());
  });
}
