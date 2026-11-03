<?php

declare(strict_types=1);

namespace App\Support;

final class CityCatalog
{
    /**
     * @return list<string>
     */
    public static function forDepartment(?string $department): array
    {
        if ($department === null || !DepartmentCatalog::isValidCode($department)) {
            return [];
        }

        $catalog = self::all();

        return $catalog[$department] ?? [];
    }

    /**
     * @return array<string, list<string>>
     */
    private static function all(): array
    {
        static $catalog = null;

        if ($catalog !== null) {
            return $catalog;
        }

        $catalogPath = dirname(__DIR__, 2) . '/config/data/cities_by_department.php';

        if (!is_file($catalogPath)) {
            throw new \RuntimeException('City catalog is missing. Run bin/build-city-catalog.php first.');
        }

        $catalog = require $catalogPath;

        if (!is_array($catalog)) {
            throw new \RuntimeException('City catalog has an invalid format.');
        }

        return $catalog;
    }
}
