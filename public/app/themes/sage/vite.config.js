import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';
import { ViteImageOptimizer } from 'vite-plugin-image-optimizer';
import VitePluginSvgSpritemap from '@spiriit/vite-plugin-svg-spritemap';

export default defineConfig({
  base: '/app/themes/onyx/public/build/',
  plugins: [
    laravel({
      input: [
        'resources/css/app.scss',
        'resources/js/app.js',
        'resources/css/editor.scss',
        'resources/js/editor.js',
        'resources/css/admin.scss',
        'resources/js/admin.js',
        'resources/css/wysiwyg.scss',
      ],
      refresh: true,
    }),

    wordpressPlugin(),

    wordpressThemeJson({
      disableTailwindColors: true,
      disableTailwindFonts: true,
      disableTailwindFontSizes: true,
    }),

    VitePluginSvgSpritemap('resources/images/icons/*.svg', {
      gutter: 10,
      injectSvgOnDev: true,
      svgo: true,
      styles: 'resources/css/spritemap.scss',
    }),

    ViteImageOptimizer({
      test: /\.svg$/i,
      exclude: /spritemap\.svg$/i,
    }),
  ],
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
  server: {
    // Respond to all network requests
    host: "0.0.0.0",
    port: 5173,
    strictPort: true,
    // Defines the origin of the generated asset URLs during development, this must be set to the
    // Vite dev server URL and selected Vite port. In general, `process.env.DDEV_PRIMARY_URL` will
    // give us the primary URL of the DDEV project, e.g. "https://test-vite.ddev.site". But since
    // DDEV can be configured to use another port (via `router_https_port`), the output can also be
    // "https://test-vite.ddev.site:1234". Therefore we need to strip a port number like ":1234"
    // before adding Vites port to achieve the desired output of "https://test-vite.ddev.site:5173".
    origin: `${(process.env.DDEV_PRIMARY_URL || '').replace(/:\d+$/, "")}:5173`,
    // Configure CORS securely for the Vite dev server to allow requests from *.ddev.site domains,
    // supports additional hostnames (via regex). If you use another `project_tld`, adjust this.
    cors: {
      origin: /https?:\/\/([A-Za-z0-9\-\.]+)?(\.ddev\.site)(?::\d+)?$/,
    },
  },
})
