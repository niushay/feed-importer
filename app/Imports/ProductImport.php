<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;

class ProductImport extends BaseImport implements WithEvents
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        try {
            $this->incrementSuccessCount();
            return new Product([
                'gtin' => $row['gtin'],
                'language' => $row['language'],
                'title' => $row['title'],
                'picture' => $row['picture'],
                'description' => $row['description'],
                'price' => $row['price'],
                'stock' => $row['stock'],
            ]);
        } catch (\Exception $e) {
            $this->incrementErrorCount();
            return null;
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Log::info("Import event started for ");
            },
            AfterImport::class => function (AfterImport $event) {
                Log::info("Import event completed for ");
            },
            ImportFailed::class => function (ImportFailed $event) {
                Log::error("Import failed: {$event->getException()}");
            },
        ];
    }
}
