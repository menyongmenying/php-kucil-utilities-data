<?php

// Pastikan Anda meletakkan file ini di direktori yang sama dengan Data.php

use Kucil\Utilities\Data;

require_once __DIR__ . '/../vendor/autoload.php';

// Fungsi helper untuk membuat output lebih rapi
function print_header(string $title): void
{
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "  TEST: {$title}\n";
    echo str_repeat('=', 50) . "\n";
}

function assert_test($condition, string $message): void
{
    if ($condition) {
        echo "[OK] " . $message . "\n";
    } else {
        echo "[FAIL] " . $message . "\n";
    }
}

// ===================================================================
// 1. PENGUJIAN DASAR (INSTANTIATION)
// ===================================================================
print_header('Dasar Instantiation & Tipe Data');

$assocArray = ['name' => 'Andi', 'age' => 30, 'active' => true];
$dataFromAssoc = new Data($assocArray);
assert_test($dataFromAssoc->name === 'Andi', 'Membuat dari array asosiatif');

$stdObject = new stdClass();
$stdObject->name = 'Budi';
$stdObject->age = 25;
$dataFromObject = new Data($stdObject);
assert_test($dataFromObject->name === 'Budi', 'Membuat dari objek stdClass');

$numericArray = ['Red', 'Green', 'Blue'];
$dataFromNumeric = new Data($numericArray);
assert_test(isset($dataFromNumeric->data1) && $dataFromNumeric->data1 === 'Red', 'Membuat dari array numerik (key menjadi data1, dst.)');
assert_test(isset($dataFromNumeric->data2) && $dataFromNumeric->data2 === 'Green', 'Array numerik: key data2 ada');

$emptyData = new Data();
assert_test(count($emptyData) === 0, 'Membuat objek kosong dari null/tanpa argumen');


// ===================================================================
// 2. PENGUJIAN AKSES PROPERTI (OBJECT & ARRAY STYLE)
// ===================================================================
print_header('Akses Properti (Object & Array)');
$testData = new Data(['product' => 'Laptop', 'price' => 15000000]);

// Akses sebagai objek
assert_test($testData->product === 'Laptop', 'Akses sebagai objek (->product)');
// Akses sebagai array
assert_test($testData['price'] === 15000000, 'Akses sebagai array ([\'price\'])');

// Akses properti yang tidak ada
assert_test($testData->brand === null, 'Akses properti objek yang tidak ada mengembalikan NULL');
assert_test($testData['stock'] === null, 'Akses kunci array yang tidak ada mengembalikan NULL');

// Pengecekan dengan isset()
assert_test(isset($testData->product) === true, 'isset() pada properti yang ada mengembalikan true');
assert_test(isset($testData['price']) === true, 'isset() pada kunci array yang ada mengembalikan true');
assert_test(isset($testData->color) === false, 'isset() pada properti yang tidak ada mengembalikan false');


// ===================================================================
// 3. PENGUJIAN MODIFIKASI PROPERTI
// ===================================================================
print_header('Modifikasi Properti');
$user = new Data(['name' => 'Citra']);

// Menambah properti baru
$user->email = 'citra@example.com';
$user['city'] = 'Jakarta';
assert_test($user->email === 'citra@example.com', 'Menambah properti baru dengan gaya objek');
assert_test($user['city'] === 'Jakarta', 'Menambah properti baru dengan gaya array');

// Mengubah properti yang sudah ada
$user->name = 'Citra Dewi';
assert_test($user->name === 'Citra Dewi', 'Mengubah properti yang ada');

// Menghapus properti
unset($user->city);
assert_test(!isset($user->city), 'Menghapus properti dengan unset()');
assert_test($user->city === null, 'Properti yang dihapus mengembalikan NULL');


// ===================================================================
// 4. PENGUJIAN REKURSIF (LOGIKA BARU)
// ===================================================================
print_header('Pengujian Rekursif Selektif (Logika Baru)');
$mixedNestedArray = [
    'id' => 123,
    'config' => [ // Should become a Data object
        'setting_a' => 'on',
        'setting_b' => 'off'
    ],
    'tags' => [ // Should remain an array of strings
        'php', 'laravel', 'oop'
    ],
    'history' => [ // Should become an array of Data objects
        ['event' => 'created', 'by' => 'admin'],
        ['event' => 'updated', 'by' => 'user1']
    ]
];

$dataMixed = new Data($mixedNestedArray, true);

assert_test($dataMixed->config instanceof Data, 'Array asosiatif (config) menjadi objek Data');
assert_test($dataMixed->config->setting_a === 'on', 'Properti objek Data (config) dapat diakses');
assert_test(is_array($dataMixed->tags), 'Array numerik (tags) tetap menjadi array');
assert_test($dataMixed->tags[1] === 'laravel', 'Elemen array (tags) dapat diakses');

assert_test(is_array($dataMixed->history), 'Array dari array asosiatif (history) tetap array');
assert_test($dataMixed->history[0] instanceof Data, 'Elemen pertama dari history adalah objek Data');
assert_test($dataMixed->history[1]->event === 'updated', 'Properti dari objek Data dalam array history dapat diakses');


// ===================================================================
// 5. PENGUJIAN IMPLEMENTASI INTERFACE
// ===================================================================
print_header('Implementasi Interface (Countable, IteratorAggregate)');

$inventory = new Data(['books' => 10, 'pens' => 50, 'erasers' => 25]);

// Test Countable
assert_test(count($inventory) === 3, 'Fungsi count() bekerja dengan benar');

// Test IteratorAggregate (foreach)
echo "Iterasi melalui objek:\n";
$items = [];
foreach ($inventory as $key => $value) {
    echo " -> {$key}: {$value}\n";
    $items[$key] = $value;
}
assert_test(count($items) === 3 && $items['pens'] === 50, 'Objek dapat diiterasi dengan foreach');

echo str_repeat('=', 50) . "\n";
echo "Semua pengujian selesai.\n";
echo str_repeat('=', 50) . "\n";

