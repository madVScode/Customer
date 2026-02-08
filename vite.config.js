import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    tailwindcss(),
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
  server: {
    host: '0.0.0.0',      // Boleh diakses dari LAN / HP
    port: 5173,           // Port tetap
    strictPort: true,     // Tak lompat port
    hmr: {
      host: '192.168.8.110',  // Browser tahu WebSocket URL
      port: 5173,
    },
    watch: {
      usePolling: true,
    },
  },
})
