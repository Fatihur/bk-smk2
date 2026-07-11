import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#e8f0fa',
                    100: '#c5daf2',
                    200: '#9ebee8',
                    300: '#72a2dd',
                    400: '#4f8bd5',
                    500: '#0C5CAB',
                    600: '#0a4f93',
                    700: '#08427b',
                    800: '#063563',
                    900: '#04284b',
                },
                surface: {
                    DEFAULT: '#09090b',
                    card: '#121215',
                    border: '#1c1c22',
                },
            },
            borderRadius: {
                card: '0.75rem',
                btn: '0.5rem',
            },
        },
    },

    plugins: [forms],
};
