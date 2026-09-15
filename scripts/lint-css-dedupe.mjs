// Duplicate-CSS-value audit: fails the build if the same non-trivial value
// (a long calc()/min()/gradient/etc. — not a short, legitimately-reused
// thing like "16px" or "flex") is copy-pasted across two or more places in
// src/styles, instead of living once as a custom property (tokens.css).
//
// This exists because of a real bug found in this project: the container's
// capped-width formula (min(1240px, calc(100% - (var(--container-padding) *
// 2)))) was typed out identically in 5 different files (base.css, header.css,
// footer.css, home-hero.css, page-hero.css) plus a 6th single-side variant
// in home-sections.css — no shared source, so every place had to be found
// and edited by hand whenever the value needed to change. Extracted into
// --container-inline-size / --container-gutter (tokens.css); this script
// makes that class of drift fail the pipeline instead of quietly piling up
// again the next time someone needs "the same capped width" somewhere new.
//
// Heuristic, not a full duplication detector: it only looks at single
// declaration VALUES (the right-hand side of one property), not multi-
// declaration blocks — copy-pasting a whole rule body with different
// property order wouldn't be caught. `unicode-range` is excluded: browsers
// require it as a literal in @font-face, custom properties don't resolve
// there, so the same subset range necessarily repeats across weights
// (fonts.css) with no fix available.
//
// Usage: node scripts/lint-css-dedupe.mjs  (wired into `npm run lint:css-dedupe`)

import { readFile, readdir } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import postcss from "postcss";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const STYLES_DIR = path.resolve(__dirname, "../src/styles");

// Below this, values are common/short enough ("16px", "center", "var(--x)")
// that reuse is normal, not a sign of a copy-pasted formula worth extracting.
const MIN_VALUE_LENGTH = 40;
const EXCLUDED_PROPERTIES = new Set(["unicode-range"]);

async function listCssFiles(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...(await listCssFiles(full)));
    } else if (entry.name.endsWith(".css")) {
      files.push(full);
    }
  }
  return files;
}

function normalize(value) {
  return value.replace(/\s+/g, " ").trim();
}

async function main() {
  const files = await listCssFiles(STYLES_DIR);

  // value -> [{ file, line, selector, property }]
  const occurrences = new Map();

  for (const file of files) {
    const css = await readFile(file, "utf8");
    const root = postcss.parse(css, { from: file });

    root.walkDecls((decl) => {
      if (EXCLUDED_PROPERTIES.has(decl.prop)) return;

      const value = normalize(decl.value);
      if (value.length < MIN_VALUE_LENGTH) return;

      const selector = decl.parent?.selector ?? decl.parent?.params ?? "(unknown)";
      const entry = { file: path.relative(STYLES_DIR, file), line: decl.source.start.line, selector, property: decl.prop };

      if (!occurrences.has(value)) occurrences.set(value, []);
      occurrences.get(value).push(entry);
    });
  }

  const duplicates = [...occurrences.entries()].filter(([, uses]) => uses.length > 1);

  if (duplicates.length > 0) {
    console.error(`\n✗ Found ${duplicates.length} CSS value(s) duplicated across the stylesheet instead of living in one custom property:\n`);
    for (const [value, uses] of duplicates) {
      console.error(`  "${value}" (${value.length} chars) — used ${uses.length}x:`);
      for (const use of uses) {
        console.error(`    ${use.file}:${use.line}  ${use.selector} { ${use.property}: ... }`);
      }
      console.error("");
    }
    console.error("  Extract the repeated value into a custom property (tokens.css) and reuse it with var(...) at each site.");
    process.exit(1);
  }

  console.log(`✓ No duplicated CSS values found (${files.length} files scanned, ${occurrences.size} distinct long values).`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
