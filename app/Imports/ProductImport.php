<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class ProductImport extends BaseImport implements ToModel, WithHeadingRow, WithProgressBar
{
    use Importable;

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

    public function batchSize(): int
    {
        return 100;
    }
}
