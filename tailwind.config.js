/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.html",
    "./admin/**/*.php",
    "./api/**/*.php",
    "./assets/js/**/*.js",
    "./cms/**/*.php",
    "./lib/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#233A5C',
        primary_light: '#3A5B8C',
        secondary: '#A68B5B',
        accent: '#F8F9FB',
        text_dark: '#2C3241',
      }
    }
  },
  plugins: []
};


