/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Marianne', 'Arial', 'sans-serif'],
      },
      colors: {
        'dsfr-blue': '#003189',
        'dsfr-blue-light': '#0063CB',
        'dsfr-red': '#E1000F',
        'dsfr-green': '#18753C',
        'cesizen-green': '#2D6A4F',
        'cesizen-light': '#D8F3DC',
        'cesizen-blue': '#EFF6FF',
      }
    },
  },
  plugins: [require('daisyui')],
  daisyui: {
    themes: [{
      cesizen: {
        "primary": "#003189",
        "primary-content": "#FFFFFF",
        "secondary": "#0063CB",
        "secondary-content": "#FFFFFF",
        "accent": "#2D6A4F",
        "accent-content": "#FFFFFF",
        "neutral": "#161616",
        "base-100": "#F5F5FE",
        "base-200": "#EBEBFB",
        "base-300": "#DDDDDD",
        "base-content": "#161616",
        "info": "#0063CB",
        "success": "#18753C",
        "warning": "#B34000",
        "error": "#E1000F",
      }
    }],
    defaultTheme: "cesizen",
  }
}
