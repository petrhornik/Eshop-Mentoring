import { defineConfig } from 'vite';
import nette from '@nette/vite-plugin';

export default defineConfig({
	plugins: [
		nette({
			entry: 'main.js',
		}),
	],

	build: {
		outDir: 'www/dist',
	},

	css: {
		devSourcemap: true,
	},
});
