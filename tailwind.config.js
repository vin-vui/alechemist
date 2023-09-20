import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                "sans": ['Figtree', ...defaultTheme.fontFamily.sans],
                "shangrila": ['Shangrila'],
                "shangrila-caps": ['ShangrilaC'],
            },
            colors: {
                "rich-black": "#12121A",
                "xanthous": "#F3AE1A",
                "tawny": "#CA6018",
                "old-gold": "#E2C05D",
                "licorice": "#2A1515",
                "white": "#FFFFFF",
                "anti-flash-white": "#EFEFEF",
            }
        },
    },

    plugins: [forms, typography],
};
