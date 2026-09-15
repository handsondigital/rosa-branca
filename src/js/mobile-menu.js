/**
 * Mobile hamburger menu toggle. Plain DOM/vanilla JS — a show/hide toggle
 * doesn't carry enough state or interaction complexity to justify React
 * (ARCHITECTURE_PLAN.md §3/§16).
 *
 * Open/close is animated (header.css: opacity + transform on .is-open) but
 * still uses the `hidden` attribute for its actual shown/hidden state, so a
 * closed menu stays out of the tab order and the a11y tree exactly like
 * before — this only changes *when* `hidden` flips relative to the CSS
 * transition, not whether it's used.
 */
export function initMobileMenu() {
  const toggle = document.querySelector('[data-mobile-menu-toggle]');
  const menu = document.getElementById('mobile-menu');

  if (!toggle || !menu) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  const close = () => {
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    // With transitions off there's no transitionend to hide it on instead —
    // this is the only other retreat, see the listener below.
    if (reducedMotion.matches) {
      menu.hidden = true;
    }
  };

  const open = () => {
    menu.hidden = false;
    // Forces the browser to commit the just-unhidden, still-closed (opacity:0)
    // state to the render tree before the next line flips it to open — without
    // this the two style changes land in the same frame and the transition
    // has no "from" state to animate away from, so it'd just snap open.
    void menu.offsetHeight;
    menu.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
  };

  // Mirrors `close`'s hidden=true, but only once the closing transition has
  // actually finished playing — re-hiding immediately (like `close` used to)
  // would cut the animation off before anyone sees it. Guards on `is-open`
  // so a transition an in-progress reopen interrupts (no transitionend for
  // a canceled transition) can't re-hide a menu that's back open.
  menu.addEventListener('transitionend', (event) => {
    if (event.target === menu && event.propertyName === 'opacity' && !menu.classList.contains('is-open')) {
      menu.hidden = true;
    }
  });

  toggle.addEventListener('click', () => {
    const isOpen = toggle.getAttribute('aria-expanded') === 'true';
    isOpen ? close() : open();
  });

  document.addEventListener('click', (event) => {
    if (menu.hidden) return;
    if (menu.contains(event.target) || toggle.contains(event.target)) return;
    close();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !menu.hidden) {
      close();
      toggle.focus();
    }
  });

  window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
    if (event.matches) close();
  });
}
