<?php

use App\Jobs\ImportFileJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command;

use function Pest\Laravel\artisan;

test('fails if file does not exist', function () {
    artisan('import:feed', [
        'file' => 'storage/app/public/missing.csv',
    ])->assertExitCode(Command::FAILURE);
});

test('fails if file type is not supported', function () {
    $fakeFile = 'fake.txt';
    createCsvFile($fakeFile, 'fake content');

    artisan('import:feed', [
        'file' => $fakeFile,
    ])->assertExitCode(Command::FAILURE);

    Storage::disk('public')->delete($fakeFile);
});

test('fails if model is invalid', function () {
    $fakeCsv = 'products.csv';
    createCsvFile($fakeCsv);

    artisan('import:feed', [
        'file' => $fakeCsv,
        '--model' => 'InvalidModel',
    ])->assertExitCode(Command::FAILURE);

    Storage::disk('public')->delete($fakeCsv);
});

test('dispatches job when with-queue is true', function () {
    Bus::fake();
    $fileName = 'products.csv';

    createCsvFile($fileName);

    $result = Artisan::call('import:feed', [
        'file' => $fileName,
        '--with-queue' => 'true',
    ]);

    expect($result)->toBe(Command::SUCCESS);

    Bus::assertDispatched(ImportFileJob::class);

    Storage::disk('public')->delete($fileName);
});

test('imports directly when with-queue is false', function () {
    $fileName = 'products.csv';
    createCsvFile($fileName);

    $result = Artisan::call('import:feed', [
        'file' => $fileName,
        '--with-queue' => 'false',
    ]);

    expect($result)->toBe(Command::SUCCESS);

    $this->assertDatabaseHas('products', [
        'gtin' => '12345',
        'title' => 'Product Title',
        'price' => 10.00,
        'stock' => 5,
    ]);

    Storage::disk('public')->delete($fileName);
});

test('fails if file is empty', function () {
    $fileName = 'empty.csv';
    createCsvFile($fileName, '');

    artisan('import:feed', [
        'file' => $fileName,
    ])->assertExitCode(Command::FAILURE);

    Storage::disk('public')->delete($fileName);
});

test('imports when some columns are missing', function () {
    $fileName = 'missing-columns.csv';
    createCsvFile($fileName, "gtin,language,title\n12345,en,Product Title");

    artisan('import:feed', [
        'file' => $fileName,
    ])->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('products', [
        'gtin' => '12345',
        'language' => 'en',
        'title' => 'Product Title',
        'picture' => null,
        'description' => null,
        'price' => null,
        'stock' => null,
    ]);

    Storage::disk('public')->delete($fileName);
});

test('fails if CSV structure is invalid', function () {
    $fileName = 'invalid-structure.csv';
    createCsvFile($fileName, "testColumn1,testColumn2,testColumn3\n12345,en,Product Title");

    artisan('import:feed', [
        'file' => $fileName,
    ])->assertExitCode(Command::FAILURE);

    Storage::disk('public')->delete($fileName);
});

test('fails if CSV contains malformed data', function () {
    $fileName = 'malformed.csv';
    createCsvFile($fileName, "gtin,language,title,price,stock\n12345,en,Product Title,non-numeric,5");

    artisan('import:feed', [
        'file' => $fileName,
    ])->assertExitCode(Command::FAILURE);

    Storage::disk('public')->delete($fileName);
});

test('imports correctly for valid model', function () {
    $fileName = 'valid-product.csv';
    createCsvFile($fileName);

    artisan('import:feed', [
        'file' => $fileName,
        '--model' => 'Product',
    ])->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('products', [
        'gtin' => '12345',
        'title' => 'Product Title',
        'price' => 10.00,
        'stock' => 5,
    ]);

    Storage::disk('public')->delete($fileName);
});

test('does not queue job when with-queue is false', function () {
    Bus::fake();
    $fileName = 'products.csv';

    createCsvFile($fileName);

    artisan('import:feed', [
        'file' => $fileName,
        '--with-queue' => 'false',
    ])->assertExitCode(Command::SUCCESS);

    Bus::assertNotDispatched(ImportFileJob::class);

    Storage::disk('public')->delete($fileName);
});

test('imports without header', function () {
    $fileName = 'no-header.csv';
    createCsvFile($fileName, '12345,en,Product Title,,Description,10.00,5');

    artisan('import:feed', [
        'file' => $fileName,
        '--with-header' => 'false',
    ])->assertExitCode(Command::SUCCESS);

    $this->assertDatabaseHas('products', [
        'gtin' => '12345',
        'title' => 'Product Title',
        'price' => 10.00,
        'stock' => 5,
    ]);

    Storage::disk('public')->delete($fileName);
});
