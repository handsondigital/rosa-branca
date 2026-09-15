// OTF/TTF -> WOFF2 font pipeline. Converts every raw source font in
// assets/fonts/raw/ into a self-hosted .woff2 in assets/fonts/generated/ —
// the same basename, just recompressed (WOFF2 is ~30-50% smaller on the
// wire than OTF/TTF for identical glyph data, and every browser this theme
// targets supports it natively; see ARCHITECTURE_PLAN.md §8).
//
// Mirrors build-images.mjs's convention: sources under assets/ are
// versioned, generated derivatives are not (see .gitignore) and are
// rebuilt by this script — wired into `npm run build` as `build:fonts`.
//
// Run via `npm run build:fonts` whenever a raw font file is added/replaced.

import { mkdir, readdir, readFile, writeFile } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { compress } from "wawoff2";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const THEME_ROOT = path.resolve(__dirname, "..");
const RAW_DIR = path.join(THEME_ROOT, "assets/fonts/raw");
const OUT_DIR = path.join(THEME_ROOT, "assets/fonts/generated");
const SOURCE_EXTENSIONS = new Set([".otf", ".ttf"]);

async function main() {
  await mkdir(OUT_DIR, { recursive: true });

  const entries = await readdir(RAW_DIR, { withFileTypes: true });
  const sources = entries
    .filter((entry) => entry.isFile() && SOURCE_EXTENSIONS.has(path.extname(entry.name).toLowerCase()))
    .map((entry) => entry.name)
    .sort();

  if (sources.length === 0) {
    console.log("No .otf/.ttf files found in assets/fonts/raw/ — nothing to convert.");
    return;
  }

  let totalBefore = 0;
  let totalAfter = 0;

  for (const file of sources) {
    const srcBuffer = await readFile(path.join(RAW_DIR, file));
    const woff2 = await compress(srcBuffer);

    const outFile = `${path.basename(file, path.extname(file))}.woff2`;
    await writeFile(path.join(OUT_DIR, outFile), woff2);

    totalBefore += srcBuffer.byteLength;
    totalAfter += woff2.byteLength;

    console.log(`  ${file} -> ${outFile}: ${(srcBuffer.byteLength / 1024).toFixed(0)}KB -> ${(woff2.byteLength / 1024).toFixed(0)}KB`);
  }

  const pct = 100 - (totalAfter / totalBefore) * 100;
  console.log(`\nDone. ${sources.length} font(s) converted. ${(totalBefore / 1024).toFixed(0)}KB -> ${(totalAfter / 1024).toFixed(0)}KB (-${pct.toFixed(0)}%).`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
