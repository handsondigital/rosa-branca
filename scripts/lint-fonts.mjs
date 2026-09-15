// Font-usage audit: cross-checks src/styles/fonts.css's self-hosted
// @font-face declarations against what the rest of the CSS actually uses.
//
// This exists because of a real bug found in this project: tokens.css named
// "Nunito Sans"/"Montserrat" in font-family stacks for months with no
// @font-face, no <link>, no local files behind them at all — every page
// silently rendered in the fallback font the whole time. This script makes
// that class of mistake fail the pipeline instead of going unnoticed, and
// catches the opposite mistake too (self-hosting a weight/family nothing
// on the site actually uses — dead weight on every page load).
//
// Heuristic, not a full CSS parser: it assumes (true everywhere in this
// codebase today) that a font-weight override lives in the *same* rule
// block as the font-family declaration it applies to. A weight set via a
// separate selector (e.g. only inside a :hover block) would be missed.
//
// Usage: node scripts/lint-fonts.mjs  (wired into `npm run lint:fonts`)

import { readFile, readdir } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const STYLES_DIR = path.resolve(__dirname, "../src/styles");
const TOKENS_FILE = path.join(STYLES_DIR, "tokens.css");
const FONTS_FILE = path.join(STYLES_DIR, "fonts.css");

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

// Extracts { "--font-heading": "Montserrat", ... } from tokens.css's
// custom-property declarations — the first (non-fallback) family name only.
function extractFontTokens(css) {
  const tokens = {};
  const re = /(--font-[\w-]+)\s*:\s*([^;]+);/g;
  let match;
  while ((match = re.exec(css))) {
    const [, name, value] = match;
    const first = value.split(",")[0].trim().replace(/^["']|["']$/g, "");
    tokens[name] = first;
  }
  return tokens;
}

// Extracts { "Family|weight": true, ... } from fonts.css's @font-face rules.
function extractDeclaredFonts(css) {
  const declared = new Set();
  const re = /@font-face\s*\{([^}]*)\}/g;
  let match;
  while ((match = re.exec(css))) {
    const block = match[1];
    const family = (block.match(/font-family\s*:\s*([^;]+);/) || [])[1];
    const weight = (block.match(/font-weight\s*:\s*([^;]+);/) || [])[1];
    if (!family) continue;
    const normalizedFamily = family.trim().replace(/^["']|["']$/g, "");
    const normalizedWeight = (weight || "400").trim();
    declared.add(`${normalizedFamily}|${normalizedWeight}`);
  }
  return declared;
}

// Finds the {...} block that starts at `openBraceIndex`, respecting nesting.
function extractBlock(css, openBraceIndex) {
  let depth = 0;
  for (let i = openBraceIndex; i < css.length; i++) {
    if (css[i] === "{") depth++;
    if (css[i] === "}") {
      depth--;
      if (depth === 0) return css.slice(openBraceIndex + 1, i);
    }
  }
  return "";
}

// Extracts { "Family|weight": [locations], ... } for every
// `font-family: var(--font-X)` usage found across the given CSS files.
function extractUsedFonts(filesContent, fontTokens) {
  const used = new Map();
  for (const { file, css } of filesContent) {
    const re = /font-family\s*:\s*var\((--font-[\w-]+)\)/g;
    let match;
    while ((match = re.exec(css))) {
      const tokenName = match[1];
      const family = fontTokens[tokenName];
      if (!family) continue;

      // Find the enclosing rule block to look for a sibling font-weight.
      const blockStart = css.lastIndexOf("{", match.index);
      const block = blockStart === -1 ? "" : extractBlock(css, blockStart);
      const weightMatch = block.match(/font-weight\s*:\s*([^;]+);/);
      const weight = (weightMatch ? weightMatch[1] : "400").trim();

      const key = `${family}|${weight}`;
      const line = css.slice(0, match.index).split("\n").length;
      if (!used.has(key)) used.set(key, []);
      used.get(key).push(`${path.relative(STYLES_DIR, file)}:${line}`);
    }
  }
  return used;
}

async function main() {
  const tokensCss = await readFile(TOKENS_FILE, "utf8");
  const fontsCss = await readFile(FONTS_FILE, "utf8");
  const fontTokens = extractFontTokens(tokensCss);
  const declared = extractDeclaredFonts(fontsCss);

  const allCssFiles = await listCssFiles(STYLES_DIR);
  const otherFiles = allCssFiles.filter((f) => f !== FONTS_FILE);
  const filesContent = await Promise.all(
    otherFiles.map(async (file) => ({ file, css: await readFile(file, "utf8") }))
  );

  const used = extractUsedFonts(filesContent, fontTokens);

  const missing = [...used.keys()].filter((key) => !declared.has(key));
  const unused = [...declared].filter((key) => !used.has(key));

  let hasError = false;

  if (missing.length > 0) {
    hasError = true;
    console.error("\n✗ Font(s) used in CSS but never self-hosted (src/styles/fonts.css):\n");
    for (const key of missing) {
      const [family, weight] = key.split("|");
      console.error(`  ${family} ${weight} — used at ${used.get(key).join(", ")}`);
    }
    console.error("\n  Add a matching @font-face to fonts.css, or fix the font-weight used.");
  }

  if (unused.length > 0) {
    hasError = true;
    console.error("\n✗ Font(s) self-hosted but never used anywhere in src/styles:\n");
    for (const key of unused) {
      const [family, weight] = key.split("|");
      console.error(`  ${family} ${weight} — declared in fonts.css, no matching usage found`);
    }
    console.error("\n  Remove the unused @font-face + its .woff2 files, or use it if it's actually needed.");
  }

  if (hasError) {
    process.exit(1);
  }

  console.log(`✓ Font usage matches self-hosted files exactly (${declared.size} family/weight combos, ${used.size} in use).`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
