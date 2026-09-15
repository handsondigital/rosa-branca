import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { writeFileSync, rmSync, existsSync } from "node:fs";
import { resolve } from "node:path";

const HOT_FILE = resolve(__dirname, "dist/.hot");
const DEV_SERVER_URL = "http://localhost:5173";

/**
 * Writes/removes dist/.hot so functions.php can detect whether the Vite dev
 * server is running (mirrors the Laravel Mix/Vite "hot file" convention).
 */
function hotFilePlugin() {
  return {
    name: "rosa-branca-hot-file",
    configureServer(server) {
      server.httpServer?.once("listening", () => {
        writeFileSync(HOT_FILE, DEV_SERVER_URL);
      });
      process.on("exit", () => {
        if (existsSync(HOT_FILE)) rmSync(HOT_FILE);
      });
    },
  };
}

export default defineConfig({
  plugins: [react(), hotFilePlugin()],
  base: "/wp-content/themes/rosa-branca/dist/",
  server: {
    port: 5173,
    strictPort: true,
    origin: DEV_SERVER_URL,
    cors: true,
  },
  build: {
    manifest: true,
    outDir: "dist",
    emptyOutDir: true,
    rollupOptions: {
      input: {
        global: resolve(__dirname, "src/entries/global.js"),
        home: resolve(__dirname, "src/entries/home.js"),
        "fale-conosco": resolve(__dirname, "src/entries/fale-conosco.js"),
      },
    },
  },
});
