/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}", // Très important : cela dit à Tailwind de scanner tes composants Angular
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}