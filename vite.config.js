import { defineConfig } from 'vite';
import path from 'path';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    
    build: {
        outDir: 'dist',
        emptyOutDir: true,

        rollupOptions: {
            input: {
                admin: path.resolve(__dirname, 'admin/src/js/admin.js'),
                style: path.resolve(__dirname, 'admin/src/scss/admin.scss'),
            },

            output: {
                entryFileNames: 'js/[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        return 'css/[name][extname]';
                    }

                    return 'assets/[name][extname]';
                },
            },
        },
    },
    plugins: [ viteStaticCopy({ targets: [ 
        { 
            src: 'node_modules/bootstrap/dist/**/*', 
            dest: 'bootstrap' 
        },
        { 
            src: 'node_modules/bootstrap-icons/font/bootstrap-icons.css', 
            dest: 'bootstrap' 
        },
         { 
            src: 'node_modules/bootstrap-icons/font/bootstrap-icons.css', 
            dest: 'bootstrap-icons' 
        },
         { 
            src: 'node_modules/bootstrap-icons/font/fonts/**/*', 
            dest: 'bootstrap-icons/fonts' 
        },
        { 
            src: 'admin/src/js/validate.min.js', 
            dest: 'js' 
        }
    ] }) ]
});