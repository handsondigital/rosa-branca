import { useCallback, useEffect, useState } from "react";
import { createPortal } from "react-dom";
import useEmblaCarousel from "embla-carousel-react";
import Autoplay from "embla-carousel-autoplay";
// Raw SVG source (Vite's ?raw import), not an <img src="...svg"> URL — keeps
// the exact markup and lets it live in the DOM as a real inline <svg>.
import arrowLeftSvg from "../../../assets/icons/carousel-arrow-left.svg?raw";
import arrowRightSvg from "../../../assets/icons/carousel-arrow-right.svg?raw";

/**
 * Generic carousel island shared by the Home hero, recipes and products
 * sections (ARCHITECTURE_PLAN.md §4/§11: one reusable component instead of
 * three bespoke ones). Built on Embla Carousel — replaced Swiper after
 * Swiper's `slidesPerView="auto"` mode (needed for the "peek" partial-reveal
 * effect) turned out to have real quirks confirmed by direct testing:
 * `activeIndex`/`realIndex` never updated, the `slideChange` event never
 * fired at all, and `loop` stalled navigation outright. Embla's model is
 * scroll-snap-based natively (no separate "index" abstraction that doesn't
 * match fractional-visible-items scenarios), so none of that class of bug
 * applies, and it also ships ~10x lighter (headless, no bundled CSS).
 *
 * Per Figma (confirmed via Figma MCP, not guessed from a screenshot — see
 * git history for an earlier wrong attempt that put them in the text
 * column), the dots AND arrows for the "peek" sections (recipes/products)
 * both sit in one row directly under the media, not the text column:
 * arrows at the row's outer/bleeding edge, dots at its inner edge (nearest
 * the text column). `dotsTarget`/`arrowsTarget` (in practice the same DOM
 * node — see mount-carousel.jsx) are where portals render them, so one
 * Embla instance still drives controls physically separate from the
 * slides themselves.
 *
 * Progressive enhancement: `slidesHtml` is the real server-rendered markup
 * captured from the DOM before React mounted (see home.js), so the exact
 * same HTML WordPress emitted is what gets displayed — Embla only adds the
 * interactive shell around it.
 */
export default function Carousel({
  slidesHtml,
  variant = "peek", // "hero" | "peek"
  showArrows = true,
  showDots = true,
  autoplayMs = null,
  ariaLabel,
  dotsTarget = null,
  arrowsTarget = null,
  gap = 30, // px between slides — Figma varies this per section (e.g. recipe cards: 50, product images: 0)
}) {
  const count = slidesHtml.length;
  const isHero = variant === "hero";

  const plugins = autoplayMs && count > 1 ? [Autoplay({ delay: autoplayMs, stopOnInteraction: false })] : [];
  const [viewportRef, emblaApi] = useEmblaCarousel({ loop: isHero && count > 1, align: "start" }, plugins);

  const [selectedIndex, setSelectedIndex] = useState(0);
  const [scrollSnapCount, setScrollSnapCount] = useState(count);
  const [canPrev, setCanPrev] = useState(false);
  const [canNext, setCanNext] = useState(false);

  const sync = useCallback((api) => {
    setSelectedIndex(api.selectedScrollSnap());
    setScrollSnapCount(api.scrollSnapList().length);
    setCanPrev(api.canScrollPrev());
    setCanNext(api.canScrollNext());
  }, []);

  useEffect(() => {
    if (!emblaApi) return undefined;
    // Embla's initial selectedScrollSnap()/canScrollPrev()/etc. only exist
    // once the instance is created (this effect firing is that signal) —
    // there's no external "ready" event to subscribe to instead, so this
    // one synchronous setState reads the API's already-current values
    // rather than deriving state over time.
    // eslint-disable-next-line react-hooks/set-state-in-effect
    sync(emblaApi);
    emblaApi.on("select", sync);
    emblaApi.on("reInit", sync);
    return () => {
      emblaApi.off("select", sync);
      emblaApi.off("reInit", sync);
    };
  }, [emblaApi, sync]);

  const hasArrows = showArrows && count > 1;
  const hasDots = showDots && scrollSnapCount > 1;

  const arrows = hasArrows && (
    <div className="carousel__arrows">
      <button
        type="button"
        className="carousel__arrow"
        onClick={() => emblaApi?.scrollPrev()}
        disabled={!isHero && !canPrev}
        aria-label="Anterior"
        dangerouslySetInnerHTML={{ __html: arrowLeftSvg }}
      />
      <button
        type="button"
        className="carousel__arrow"
        onClick={() => emblaApi?.scrollNext()}
        disabled={!isHero && !canNext}
        aria-label="Próximo"
        dangerouslySetInnerHTML={{ __html: arrowRightSvg }}
      />
    </div>
  );

  const dots = hasDots && (
    <div className="carousel__dots">
      {Array.from({ length: scrollSnapCount }, (_, index) => (
        <button
          key={index}
          type="button"
          className={`carousel__dot${index === selectedIndex ? " carousel__dot--active" : ""}`}
          aria-label={`Ir para o item ${index + 1}`}
          aria-current={index === selectedIndex}
          onClick={() => emblaApi?.scrollTo(index)}
        />
      ))}
    </div>
  );

  return (
    <div className={`carousel carousel--${variant}`}>
      <div className="carousel__viewport" ref={viewportRef} role="region" aria-roledescription="carousel" aria-label={ariaLabel}>
        <div className="carousel__track" style={{ gap: `${gap}px` }}>
          {slidesHtml.map((html, index) => (
            <div
              key={index}
              className="carousel__slide"
              role="group"
              aria-roledescription="slide"
              aria-label={`${index + 1} / ${count}`}
              dangerouslySetInnerHTML={{ __html: html }}
            />
          ))}
        </div>
      </div>

      {arrows && (arrowsTarget ? createPortal(arrows, arrowsTarget) : arrows)}

      {dots && (dotsTarget ? createPortal(dots, dotsTarget) : dots)}
    </div>
  );
}
