/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
        './resources/js/**/*.ts',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#2E3A8C',
                'primary-hover': '#1F2A66',
                accent: '#F4C430',
                success: '#1ABC9C',
                info: '#1ABC9C',
                warning: '#F4C430',
            }
        },
    },
    plugins: [],
};
