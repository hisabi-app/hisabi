const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            maxHeight: {
                '22': '5.625rem'
            },
            minHeight: {
                '22': '5.625rem'
            }
        },
    },

    safelist: [
        'badge-red',
        'badge-blue',
        'badge-green',
        'badge-orange',
        'badge-purple',
        'badge-pink',
        'badge-indigo',
        'badge-gray',
        'md:w-1/2',
        'md:w-1/3',
        'md:w-1/4',
       ],

    plugins: [require('@tailwindcss/forms')],
};
