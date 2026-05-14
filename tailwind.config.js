import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';


/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,js,ts,jsx,tsx}',
        './node_modules/primevue/**/*.{vue,js,ts,jsx,tsx}',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                body: ['"Branding SF"'],
            },
            colors: {
                "primary-50": "rgb(var(--primary-50))",
                "primary-100": "rgb(var(--primary-100))",
                "primary-200": "rgb(var(--primary-200))",
                "primary-300": "rgb(var(--primary-300))",
                "primary-400": "rgb(var(--primary-400))",
                "primary-500": "rgb(var(--primary-500))",
                "primary-600": "rgb(var(--primary-600))",
                "primary-700": "rgb(var(--primary-700))",
                "primary-800": "rgb(var(--primary-800))",
                "primary-900": "rgb(var(--primary-900))",
                "primary-950": "rgb(var(--primary-950))",

                "secondary-50": "rgb(var(--secondary-50))",
                "secondary-100": "rgb(var(--secondary-100))",
                "secondary-200": "rgb(var(--secondary-200))",
                "secondary-300": "rgb(var(--secondary-300))",
                "secondary-400": "rgb(var(--secondary-400))",
                "secondary-500": "rgb(var(--secondary-500))",
                "secondary-600": "rgb(var(--secondary-600))",
                "secondary-700": "rgb(var(--secondary-700))",
                "secondary-800": "rgb(var(--secondary-800))",
                "secondary-900": "rgb(var(--secondary-900))",
                "secondary-950": "rgb(var(--secondary-950))",

                "tertiary-50": "rgb(var(--tertiary-50))",
                "tertiary-100": "rgb(var(--tertiary-100))",
                "tertiary-200": "rgb(var(--tertiary-200))",
                "tertiary-300": "rgb(var(--tertiary-300))",
                "tertiary-400": "rgb(var(--tertiary-400))",
                "tertiary-500": "rgb(var(--tertiary-500))",
                "tertiary-600": "rgb(var(--tertiary-600))",
                "tertiary-700": "rgb(var(--tertiary-700))",
                "tertiary-800": "rgb(var(--tertiary-800))",
                "tertiary-900": "rgb(var(--tertiary-900))",
                "tertiary-950": "rgb(var(--tertiary-950))",

                "surface-0": "rgb(var(--surface-0))",
                "surface-50": "rgb(var(--surface-50))",
                "surface-100": "rgb(var(--surface-100))",
                "surface-200": "rgb(var(--surface-200))",
                "surface-300": "rgb(var(--surface-300))",
                "surface-400": "rgb(var(--surface-400))",
                "surface-500": "rgb(var(--surface-500))",
                "surface-600": "rgb(var(--surface-600))",
                "surface-700": "rgb(var(--surface-700))",
                "surface-800": "rgb(var(--surface-800))",
                "surface-900": "rgb(var(--surface-900))",
                "surface-950": "rgb(var(--surface-950))",
            },
        },
    },

    plugins: [forms, typography, require('tailwindcss-primeui')],
};
