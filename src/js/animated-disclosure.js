/**
 * Shared open/close-with-CSS-transition behavior for a toggle button + panel
 * pair (mobile menu, header search dropdown, ...). Written once for the
 * mobile menu, then needed again verbatim for the header search dropdown —
 * extracted here instead of copy-pasting a second time (CLAUDE.md
 * "Reuse-first").
 *
 * The panel keeps using the `hidden` attribute for its actual shown/hidden
 * state (stays out of the tab order and the a11y tree when closed, exactly
 * like a plain toggle would), but flips it relative to the transition
 * instead of instantly: `hidden` is cleared before the opening transition
 * starts (with a forced reflow so the browser commits the closed starting
 * state first — without it the two style changes land in the same frame
 * and there's no "from" state to animate away from), and only re-applied
 * once the closing transition's `transitionend` fires, so the animation
 * isn't cut off. The panel's own CSS is expected to transition `opacity`
 * on a `.is-open` class this toggles — this module only handles the
 * `hidden` timing around that, not the transition itself.
 */
export function initAnimatedDisclosure({ toggle, panel, closeOnOutsideClick = true, closeOnEscape = true } = {}) {
  if (!toggle || !panel) return null;

  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  const isOpen = () => toggle.getAttribute("aria-expanded") === "true";

  const close = () => {
    panel.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");
    // With transitions off there's no transitionend to hide it on instead —
    // this is the only other retreat, see the listener below.
    if (reducedMotion.matches) {
      panel.hidden = true;
    }
  };

  const open = () => {
    panel.hidden = false;
    void panel.offsetHeight;
    panel.classList.add("is-open");
    toggle.setAttribute("aria-expanded", "true");
  };

  // Mirrors `close`'s hidden=true, but only once the closing transition has
  // actually finished playing — re-hiding immediately would cut the
  // animation off before anyone sees it. Guards on `is-open` so a
  // transition an in-progress reopen interrupts (no transitionend for a
  // canceled transition) can't re-hide a panel that's back open.
  panel.addEventListener("transitionend", (event) => {
    if (event.target === panel && event.propertyName === "opacity" && !panel.classList.contains("is-open")) {
      panel.hidden = true;
    }
  });

  toggle.addEventListener("click", () => {
    isOpen() ? close() : open();
  });

  if (closeOnOutsideClick) {
    document.addEventListener("click", (event) => {
      if (panel.hidden) return;
      if (panel.contains(event.target) || toggle.contains(event.target)) return;
      close();
    });
  }

  if (closeOnEscape) {
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !panel.hidden) {
        close();
        toggle.focus();
      }
    });
  }

  return { open, close, isOpen };
}
