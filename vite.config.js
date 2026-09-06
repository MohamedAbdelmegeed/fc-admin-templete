import { readFileSync, existsSync } from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// الصفحة بتتحمّل من https://localhost:8443 (nginz)، فالمتصفح بيرفض
// اتصال HMR بـ ws:// عادي كـ mixed content — لازم wss:// وده محتاج
// خادم Vite نفسه TLS. بنستخدم نفس شهادة mkcert اللي nginx بيستخدمها
// (docs/00 بند ٢)، ولو مش موجودة (أول تشغيل قبل ما حد يولّدها)
// بنرجع لـ HTTP عادي بدل ما نكسر npm run dev.
const certDir = new URL('./docker/nginx/certs/', import.meta.url);
const keyPath = new URL('local-key.pem', certDir);
const certPath = new URL('local.pem', certDir);
const hasCerts = existsSync(keyPath) && existsSync(certPath);

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // ثيم لوحة admin — Filament بيحمّله عبر viteTheme()
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // 0.0.0.0 عشان يستقبل الطلبات من بره الحاوية — الافتراضي
        // (localhost) بيسمع جوّه الحاوية بس، ومتصفح ويندوز مايوصلوش.
        host: '0.0.0.0',
        port: 5173,
        // من غيرها Vite بيسكت وينتقل لبورت تاني لو 5173 مشغول — وده
        // بيكسر الرابط اللي @vite() في Blade مستنّي عليه من غير أي
        // تحذير واضح في المتصفح.
        strictPort: true,
        https: hasCerts
            ? { key: readFileSync(keyPath), cert: readFileSync(certPath) }
            : undefined,
        hmr: {
            host: 'localhost',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
