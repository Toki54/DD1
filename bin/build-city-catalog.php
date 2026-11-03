<?php

declare(strict_types=1);

use App\Support\DepartmentCatalog;

require dirname(__DIR__) . '/vendor/autoload.php';

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php bin/build-city-catalog.php <source.csv> <target.php>\n");
    exit(1);
}

[$script, $sourcePath, $targetPath] = $argv;

$source = fopen($sourcePath, 'rb');
if ($source === false) {
    fwrite(STDERR, "Unable to read source CSV: {$sourcePath}\n");
    exit(1);
}

$header = fgetcsv($source);
if ($header === false) {
    fwrite(STDERR, "The source CSV has no header.\n");
    exit(1);
}

$departmentColumn = array_search('DDEP_C_COD', $header, true);
$cityColumn = array_search('DCOE_L_LIB', $header, true);

if ($departmentColumn === false || $cityColumn === false) {
    fwrite(STDERR, "Required columns DDEP_C_COD and DCOE_L_LIB are missing.\n");
    exit(1);
}

$citiesByDepartment = array_fill_keys(array_values(DepartmentCatalog::CHOICES), []);

while (($row = fgetcsv($source)) !== false) {
    $department = trim((string) ($row[$departmentColumn] ?? ''));
    $city = trim((string) ($row[$cityColumn] ?? ''));

    if ($city === '' || !DepartmentCatalog::isValidCode($department)) {
        continue;
    }

    $citiesByDepartment[$department][$city] = $city;
}

fclose($source);

foreach ($citiesByDepartment as &$cities) {
    $cities = array_values($cities);
    sort($cities, SORT_NATURAL | SORT_FLAG_CASE);
}
unset($cities);

ksort($citiesByDepartment, SORT_NATURAL);

$targetDirectory = dirname($targetPath);
if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
    fwrite(STDERR, "Unable to create target directory: {$targetDirectory}\n");
    exit(1);
}

$catalog = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($citiesByDepartment, true) . ";\n";

if (file_put_contents($targetPath, $catalog, LOCK_EX) === false) {
    fwrite(STDERR, "Unable to write target catalog: {$targetPath}\n");
    exit(1);
}

$cityCount = array_sum(array_map('count', $citiesByDepartment));
printf("Generated %d cities for %d departments.\n", $cityCount, count($citiesByDepartment));
