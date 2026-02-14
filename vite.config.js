import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  base: './', // Use relative paths for production build
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
    }
  },
  server: {
    proxy: {
      // Proxy API requests to Apache (PHP backend)
      '/aplikasi-air/api': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false
      },
      '/aplikasi-air/uploads': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false
      },
      // Alternative: /api proxy that rewrites to /aplikasi-air/api
      '/api': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/api/, '/aplikasi-air/api')
      },
      '/uploads': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/uploads/, '/aplikasi-air/uploads')
      }
    }
  }
})
