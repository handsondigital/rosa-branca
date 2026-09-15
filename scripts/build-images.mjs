// Build-time image pipeline (ARCHITECTURE_PLAN.md §8 "Imagens"): generates
// AVIF + WebP + a universally-supported fallback (JPEG for opaque photos,
// PNG for images with real alpha) at the widths each image is actually
// displayed at, so the theme never ships a 10MB PNG for a 380px card photo.
// Run via `npm run build:images` (also wired into `npm run build`).
//
// Source images live in assets/images/*.{png,jpg}; generated files land in
// assets/images/generated/, alongside a manifest.json that inc/images.php
// reads to print <picture>/srcset markup without hardcoding widths in PHP.

import { mkdir, readFile, writeFile } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import sharp from "sharp";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const THEME_ROOT = path.resolve(__dirname, "..");
const SRC_DIR = path.join(THEME_ROOT, "assets/images");
const OUT_DIR = path.join(SRC_DIR, "generated");

// Widths are the real display widths (1x) + a 2x retina step, capped to the
// source image's natural width so nothing gets upscaled. `sizes` is the
// value the <picture>/<img> should advertise for that usage.
const IMAGES = [
  { name: "banner-home", file: "banner-home.png", widths: [640, 960, 1440, 1920], sizes: "100vw" },
  { name: "foto-trigo-farinha", file: "foto-trigo-farinha.png", widths: [640, 960, 1440, 1920], sizes: "100vw" },
  { name: "encontre-rosa-branca-bg", file: "encontre-rosa-branca-bg.png", widths: [640, 960, 1440, 1920, 2560], sizes: "100vw" },
  { name: "foto-sobre-a-marca", file: "foto-sobre-a-marca.png", widths: [706, 1412], sizes: "(max-width: 767px) 100vw, 706px" },
  { name: "recipe-card-placeholder", file: "recipe-card-placeholder.png", widths: [380, 760], sizes: "(max-width: 430px) 100vw, 380px" },
  { name: "farinha-home-1", file: "farinha-home-1.png", widths: [322, 644], sizes: "(max-width: 400px) 100vw, 322px" },
  { name: "farinha-home-2", file: "farinha-home-2.png", widths: [322, 644], sizes: "(max-width: 400px) 100vw, 322px" },
  { name: "farinha-home-3", file: "farinha-home-3.png", widths: [322, 644], sizes: "(max-width: 400px) 100vw, 322px" },
];

const AVIF_QUALITY = 55;
const WEBP_QUALITY = 75;
const FALLBACK_QUALITY = 78;

async function buildOne(spec) {
  const srcPath = path.join(SRC_DIR, spec.file);
  const srcBuffer = await readFile(srcPath);
  const src = sharp(srcBuffer);
  const meta = await src.metadata();
  const hasAlpha = Boolean(meta.hasAlpha);
  const fallbackExt = hasAlpha ? "png" : "jpg";

  const widths = [...new Set(spec.widths.filter((w) => w <= meta.width))].sort((a, b) => a - b);
  if (widths.length === 0) widths.push(meta.width);

  const formats = { avif: [], webp: [], fallback: [] };

  for (const width of widths) {
    const resized = sharp(srcBuffer).resize({ width, withoutEnlargement: true });

    const avifFile = `${spec.name}-${width}.avif`;
    await resized.clone().avif({ quality: AVIF_QUALITY }).toFile(path.join(OUT_DIR, avifFile));
    formats.avif.push({ width, file: avifFile });

    const webpFile = `${spec.name}-${width}.webp`;
    await resized.clone().webp({ quality: WEBP_QUALITY }).toFile(path.join(OUT_DIR, webpFile));
    formats.webp.push({ width, file: webpFile });

    const fallbackFile = `${spec.name}-${width}.${fallbackExt}`;
    if (hasAlpha) {
      await resized.clone().png({ compressionLevel: 9, adaptiveFiltering: true }).toFile(path.join(OUT_DIR, fallbackFile));
    } else {
      await resized.clone().jpeg({ quality: FALLBACK_QUALITY, mozjpeg: true }).toFile(path.join(OUT_DIR, fallbackFile));
    }
    formats.fallback.push({ width, file: fallbackFile });
  }

  return [
    spec.name,
    {
      width: meta.width,
      height: meta.height,
      alpha: hasAlpha,
      sizes: spec.sizes,
      formats,
    },
  ];
}

async function main() {
  await mkdir(OUT_DIR, { recursive: true });

  const manifest = {};
  let totalBefore = 0;
  let totalAfter = 0;

  for (const spec of IMAGES) {
    const srcStat = await readFile(path.join(SRC_DIR, spec.file));
    totalBefore += srcStat.byteLength;

    const [name, entry] = await buildOne(spec);
    manifest[name] = entry;

    let entryBytes = 0;
    for (const list of Object.values(entry.formats)) {
      for (const item of list) {
        const stat = await readFile(path.join(OUT_DIR, item.file));
        entryBytes += stat.byteLength;
      }
    }
    totalAfter += entryBytes;

    console.log(`  ${name}: ${(srcStat.byteLength / 1024).toFixed(0)}KB source -> ${(entryBytes / 1024).toFixed(0)}KB across ${Object.values(entry.formats).flat().length} generated files`);
  }

  await writeFile(path.join(OUT_DIR, "manifest.json"), JSON.stringify(manifest, null, 2));

  console.log(`\nDone. Source total: ${(totalBefore / 1024 / 1024).toFixed(1)}MB, generated total: ${(totalAfter / 1024 / 1024).toFixed(1)}MB.`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
