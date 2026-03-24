<?php
namespace App\Presentation\Item;

use Nette;

final class ItemPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    public function renderShow(int $id): void
    {
        $product = $this->database
            ->table('products')
            ->get($id);

        if (!$product) {
            $this->error('Tento produkt neexistuje v databázi.', 404);
        }

        $this->template->product = $product;
        $this->template->productData = $product->toArray();
    }
}
