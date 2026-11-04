<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/models/Book.php';

/** Builds the data needed by the server-rendered public catalog. */
final class CatalogController
{
    public static function index(): void
    {
        $categories = Book::categories();
        $books = Book::published();
        $componentOptions = [
            'active' => 'katalog',
            'logo_href' => 'index.php',
            'about_href' => 'tentang.php',
            'show_reservation' => true,
            'linked_logo' => false,
            'page_links' => [
                'beranda' => 'index.php',
                'tentang' => 'tentang.php',
                'event' => 'infoevent.php',
                'katalog' => 'katalog.php',
            ],
        ];

        require dirname(__DIR__, 2) . '/views/public/catalog.php';
    }
}
