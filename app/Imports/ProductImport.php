<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProductImport extends BaseImport
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
}
