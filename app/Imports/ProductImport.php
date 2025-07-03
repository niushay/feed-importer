<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;

class ProductImport extends BaseImport implements WithEvents
{
    /**
    * @param array $row
    * @return Model|null
    */
    public function model(array $row): ?Model
    {
        try {
            $counter = 0;
            $product = new Product([
                'gtin' => $row['gtin'] ?? $row[$counter],
                'language' => $row['language']?? $row[$counter + 1],
                'title' => $row['title']?? $row[$counter + 2],
                'picture' => $row['picture']?? $row[$counter + 3],
                'description' => $row['description']?? $row[$counter + 4],
                'price' => $row['price']?? $row[$counter + 5],
                'stock' => $row['stock']?? $row[$counter + 6],
            ]);
            $this->incrementSuccessCount();
            return $product;
        } catch (\Exception $e) {
            Log::error("Error importing row : {$row['gtin']}: {$e->getMessage()}");
            $this->incrementErrorCount();
            return null;
        }
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Log::info("Import event started");
            },
            AfterImport::class => function (AfterImport $event) {
                Log::info("Import event completed");
            },
            ImportFailed::class => function (ImportFailed $event) {
                Log::error("Import failed: {$event->getException()}");
            },
        ];
    }
}
