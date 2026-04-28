/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'ambato-azul': '#003366',
        'ambato-rojo': '#CC0000',
      },
    },
  },
  plugins: [],
}