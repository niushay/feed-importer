<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProductImport extends BaseImport
{
    /**
     * Map CSV row to a Product model.
     */
    public function model(array $row): ?Model
    {
        try {
            $productData = $this->mapRowToProductData($row);

            $product = new Product($productData);
            $this->incrementSuccessCount();

            return $product;
        } catch (\Exception $e) {
            Log::error("Error importing row: {$e->getMessage()}");
            $this->incrementErrorCount();

            return null;
        }
    }

    private function mapRowToProductData(array $row): array
    {
        return [
            'gtin' => $this->getField($row, 'gtin', 0),
            'language' => $this->getField($row, 'language', 1),
            'title' => $this->getField($row, 'title', 2),
            'picture' => $this->getField($row, 'picture', 3),
            'description' => $this->getField($row, 'description', 4),
            'price' => $this->getField($row, 'price', 5),
            'stock' => $this->getField($row, 'stock', 6),
        ];
    }
}
