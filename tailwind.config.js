import { defineConfig } from 'tailwindcss'

export default defineConfig({
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/livewire/flux/stubs/**/*.blade.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
})
