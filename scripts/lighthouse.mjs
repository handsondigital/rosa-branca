// Local Lighthouse audit (ARCHITECTURE_PLAN.md §9 "Core Web Vitals").
//
// Google's public PageSpeed Insights tool can't reach this site — it needs a
// publicly reachable URL, and this project runs on http://rosa-branca.local.
// Lighthouse is the same engine PSI uses for its lab-data score, so running
// it locally against both in-scope pages (PAGES_PLAN.md: Home, Fale Conosco)
// gives the same numbers PSI would report once the site is public.
//
// Usage: npm run lighthouse  (site must be reachable — `npm run build` first,
// no dev server required; WordPress serves the built dist/ assets directly)

import { mkdir, writeFile } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import * as chromeLauncher from "chrome-launcher";
import lighthouse from "lighthouse";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const REPORT_DIR = path.resolve(__dirname, "../reports/lighthouse");

const SITE_URL = process.env.ROSA_BRANCA_URL || "http://rosa-branca.local";
const PAGES = [
  { name: "home", url: `${SITE_URL}/` },
  { name: "fale-conosco", url: `${SITE_URL}/fale-conosco/` },
];

// ARCHITECTURE_PLAN.md §9 thresholds.
const BUDGETS = { lcp: 2500, cls: 0.1, tbt: 200 };

const CATEGORY_KEYS = ["performance", "accessibility", "best-practices", "seo"];

function fmtScore(score) {
  const pct = Math.round(score * 100);
  const flag = pct >= 90 ? "✓" : pct >= 50 ? "!" : "✗";
  return `${pct}/100 ${flag}`;
}

function fmtMetric(value, unit, budget, higherIsBetter = false) {
  if (value === undefined || value === null) return "n/a";
  const ok = higherIsBetter ? value >= budget : value <= budget;
  const flag = ok ? "✓" : "✗";
  const shown = unit === "s" ? (value / 1000).toFixed(2) : value.toFixed(3);
  return `${shown}${unit} ${flag}`;
}

async function run() {
  await mkdir(REPORT_DIR, { recursive: true });

  const chrome = await chromeLauncher.launch({ chromeFlags: ["--headless=new"] });
  const results = [];

  try {
    for (const page of PAGES) {
      console.log(`\nAuditing ${page.name} (${page.url})...`);

      const runnerResult = await lighthouse(page.url, {
        port: chrome.port,
        output: ["json", "html"],
        onlyCategories: CATEGORY_KEYS,
        formFactor: "mobile",
        screenEmulation: { mobile: true, width: 412, height: 823, deviceScaleFactor: 1.75, disabled: false },
        // "simulate" (Lighthouse's default) builds an arithmetic dependency
        // model instead of actually throttling — it can badly overestimate
        // render-blocking delay from a stylesheet with many sub-resources
        // (confirmed here: self-hosting fonts made "simulate" report LCP
        // render-delay jumping from ~50ms to ~1.3s / Home failing its 2.5s
        // budget, while real devtools throttling showed 1.7s the whole
        // time — a Lantern modeling artifact, not a real regression).
        // "devtools" actually runs the page under throttled conditions, so
        // it's slower per run but trustworthy — worth it for a script whose
        // whole purpose is an accurate number.
        throttlingMethod: "devtools",
      });

      const { lhr, report } = runnerResult;
      const [jsonReport, htmlReport] = report;

      await writeFile(path.join(REPORT_DIR, `${page.name}.json`), jsonReport);
      await writeFile(path.join(REPORT_DIR, `${page.name}.html`), htmlReport);

      const audits = lhr.audits;
      const metrics = {
        lcp: audits["largest-contentful-paint"]?.numericValue,
        cls: audits["cumulative-layout-shift"]?.numericValue,
        tbt: audits["total-blocking-time"]?.numericValue,
        speedIndex: audits["speed-index"]?.numericValue,
        tti: audits["interactive"]?.numericValue,
      };

      results.push({
        name: page.name,
        url: page.url,
        categories: Object.fromEntries(CATEGORY_KEYS.map((k) => [k, lhr.categories[k]?.score ?? null])),
        metrics,
      });
    }
  } finally {
    await chrome.kill();
  }

  console.log("\n" + "=".repeat(72));
  console.log("LIGHTHOUSE SUMMARY (mobile, real DevTools throttling)");
  console.log("=".repeat(72));

  for (const r of results) {
    console.log(`\n${r.name}  (${r.url})`);
    console.log(`  Performance:     ${fmtScore(r.categories.performance)}`);
    console.log(`  Accessibility:   ${fmtScore(r.categories.accessibility)}`);
    console.log(`  Best Practices:  ${fmtScore(r.categories["best-practices"])}`);
    console.log(`  SEO:             ${fmtScore(r.categories.seo)}`);
    console.log(`  ---`);
    console.log(`  LCP:             ${fmtMetric(r.metrics.lcp, "s", BUDGETS.lcp)}  (budget <${BUDGETS.lcp / 1000}s)`);
    console.log(`  CLS:             ${fmtMetric(r.metrics.cls, "", BUDGETS.cls)}  (budget <${BUDGETS.cls})`);
    console.log(`  TBT:             ${fmtMetric(r.metrics.tbt, "s", BUDGETS.tbt)}  (budget <${BUDGETS.tbt}ms, proxy for INP)`);
    console.log(`  Speed Index:     ${(r.metrics.speedIndex / 1000).toFixed(2)}s`);
    console.log(`  Time to Interactive: ${(r.metrics.tti / 1000).toFixed(2)}s`);
  }

  console.log(`\nFull HTML/JSON reports saved to reports/lighthouse/`);
  console.log(`(git-ignored — regenerate anytime with "npm run lighthouse")`);
}

run().catch((err) => {
  console.error(err);
  process.exit(1);
});
