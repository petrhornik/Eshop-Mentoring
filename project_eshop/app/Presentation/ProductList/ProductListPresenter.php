<?php
namespace App\Presentation\ProductList;

use Nette;

final class ProductListPresenter extends Nette\Application\UI\Presenter
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

        foreach ($products as $product) {
            $categoryNames = [];

            foreach ($product->related('product_categories') as $productCategory) {
                $category = $productCategory->ref('categories', 'category_id');

                if ($category !== null) {
                    $categoryNames[] = $category->name;
                }
            }

            $productCategories[$product->id] = $categoryNames;
        }

        $this->template->products = $products;
        $this->template->productCategories = $productCategories;
        $this->template->categories = $categories;
    }
}
