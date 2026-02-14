/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#2EC4B6',      // Light Sea Green
        frozen: '#CBF3F0',       // Frozen Water
        accent: '#FF9F1C',       // Amber Glow
        highlight: '#FFBF69',    // Honey Bronze
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'sans-serif'],
        display: ['Bebas Neue', 'cursive'],
      },
      container: {
        center: true,
        padding: '1rem',
      },
    },
  },
  plugins: [],
}
