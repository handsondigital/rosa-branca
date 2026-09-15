import { createRoot } from "react-dom/client";
import { flushSync } from "react-dom";
import Carousel from "./components/Carousel.jsx";

/**
 * Mounts the Carousel island onto a server-rendered container, only if that
 * container exists on the page (ARCHITECTURE_PLAN.md §16: React must never
 * be the only content — it mounts onto real, already-visible HTML).
 *
 * Options are read from data-* attributes set by the PHP template, and the
 * slide markup itself is captured from the container's real children before
 * React takes over, so no content is duplicated as a separate JSON payload.
 */
export function mountAllCarousels(selector) {
  document.querySelectorAll(selector).forEach(mountCarousel);
}

function mountCarousel(container) {
  const slidesHtml = Array.from(container.children).map((child) => child.outerHTML);
  if (slidesHtml.length === 0) return;

  const variant = container.dataset.variant || "peek";
  const showArrows = container.dataset.arrows !== "false";
  const showDots = container.dataset.dots !== "false";
  const autoplayMs = container.dataset.autoplay ? Number(container.dataset.autoplay) : null;
  const ariaLabel = container.dataset.label || undefined;
  // Dots and arrows both portal into the SAME shared node (see
  // recipes.php / products.php's .carousel__controls) — Carousel.jsx
  // always renders arrows before dots, so within that one container
  // that's also their DOM/portal order; .home-section--bleed-right
  // reverses it visually with flex-direction: row-reverse (carousel.css)
  // rather than swapping anything here.
  const controlsTarget = container.dataset.controlsTarget ? document.querySelector(container.dataset.controlsTarget) : null;
  const dotsTarget = controlsTarget;
  const arrowsTarget = controlsTarget;
  const gap = container.dataset.gap ? Number(container.dataset.gap) : undefined;

  // The SSR fallback has no arrows row at all (that's entirely rendered by
  // <Carousel> below) — a static placeholder inside .carousel__controls
  // (see recipes.php / products.php) reserves its exact box pre-mount so
  // this swap doesn't shift the layout. Once the real arrows exist (or
  // fall back to rendering inline, if there's no target), the placeholder
  // must go.
  const arrowsPlaceholder = controlsTarget?.querySelector(".carousel__arrows-placeholder");
  const hasArrowsPlaceholder = showArrows && Boolean(arrowsPlaceholder);

  const mountPoint = document.createElement("div");
  container.replaceWith(mountPoint);
  if (hasArrowsPlaceholder) {
    arrowsPlaceholder.remove();
  }

  // flushSync forces the whole mount (render + effects, including Embla's
  // own layout measurements) to commit before this function returns, in
  // the same task as the two DOM swaps above. Without it, React 18 can
  // defer the actual commit to a later task — the browser then paints an
  // intermediate "empty mountPoint" frame, which showed up as a real,
  // measured Lighthouse CLS regression (two separate layout shifts instead
  // of the DOM ending up in its final state in one go).
  flushSync(() => {
    createRoot(mountPoint).render(
      <Carousel
        slidesHtml={slidesHtml}
        variant={variant}
        showArrows={showArrows}
        showDots={showDots}
        autoplayMs={autoplayMs}
        ariaLabel={ariaLabel}
        dotsTarget={dotsTarget}
        arrowsTarget={arrowsTarget}
        {...(gap !== undefined ? { gap } : {})}
      />
    );
  });
}
