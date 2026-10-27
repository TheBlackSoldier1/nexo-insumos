<?php
/** Preparación local repetible: no reemplaza .env ni una base SQLite existente. */
chdir(__DIR__);
if (PHP_VERSION_ID < 80401 || PHP_INT_SIZE !== 8) {
    fwrite(STDERR, "Se requiere PHP 8.4.1 o superior de 64 bits.\n");
    exit(1);
}
foreach (['pdo_sqlite', 'mbstring', 'dom', 'xml', 'fileinfo', 'openssl'] as $extension) {
    if (!extension_loaded($extension)) {
        fwrite(STDERR, "Activa la extension PHP: {$extension}\n");
        exit(1);
    }
}
if (!is_file('vendor/autoload.php')) {
    fwrite(STDERR, "Ejecuta primero: composer install\n");
    exit(1);
}
if (!is_file('.env') && !copy('.env.example', '.env')) {
    fwrite(STDERR, "No fue posible crear .env\n");
    exit(1);
}
if (!is_file('database/database.sqlite') && !touch('database/database.sqlite')) {
    fwrite(STDERR, "No fue posible crear la base SQLite.\n");
    exit(1);
}
echo "Entorno local preparado. Los archivos existentes se conservaron.\n";
echo "En una instalacion nueva: php artisan key:generate\n";
echo "Luego: php artisan migrate\n";
echo "Opcional, datos de prueba: php artisan db:seed --class=DemoSeeder\n";
echo "Iniciar: php artisan serve --host=127.0.0.1 --port=8127\n";
