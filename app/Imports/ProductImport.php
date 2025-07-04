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
            $product = new Product([
                'gtin'        => $this->getField($row, 'gtin', 0),
                'language'    => $this->getField($row, 'language', 1),
                'title'       => $this->getField($row, 'title', 2),
                'picture'     => $this->getField($row, 'picture', 3),
                'description' => $this->getField($row, 'description', 4),
                'price'       => $this->getField($row, 'price', 5),
                'stock'       => $this->getField($row, 'stock', 6),
            ]);
            $this->incrementSuccessCount();
            return $product;
        } catch (\Exception $e) {
            Log::error("Error importing row: {$e->getMessage()}");
            $this->incrementErrorCount();
            return null;
        }
    }
}
