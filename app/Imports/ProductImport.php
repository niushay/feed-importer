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
