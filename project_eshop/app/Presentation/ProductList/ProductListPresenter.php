<?php
namespace App\Presentation\ProductList;

use Nette;
use App\Presentation\BasePresenter;

final class ProductListPresenter extends BasePresenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    public function renderProducts(): void
    {

        $products = $this->database
            ->table('products')
            ->order('creation_date DESC');

        $categories = $this->database
            ->table('categories');

        $productCategories = [];
        $productPrices = [];

        foreach ($products as $product) {
            $categoryNames = [];

            foreach ($product->related('product_categories') as $productCategory) {
                $category = $productCategory->ref('categories', 'category_id');

                if ($category !== null) {
                    $categoryNames[] = $category->name;
                }
            }

            $productCategories[$product->id] = $categoryNames;

            $priceByCurrency = [];

            foreach ($product->related('product_pricing') as $pricing) {
                $priceByCurrency[$pricing->currency] = $pricing->price_no_vat;
            }

            $productPrices[$product->id] = $priceByCurrency;
        }

        $this->template->products = $products;
        $this->template->productCategories = $productCategories;
        $this->template->categories = $categories;
        $this->template->productPrices = $productPrices;
    }
}
