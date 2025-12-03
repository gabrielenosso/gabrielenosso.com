/** @type {import('tailwindcss').Config} */
export default {
    content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'night-blue': '#0a1628',
                'light-blue': '#6b9bd1',
                'gold': '#d4af37',
            },
        },
    },
    plugins: [],
}
