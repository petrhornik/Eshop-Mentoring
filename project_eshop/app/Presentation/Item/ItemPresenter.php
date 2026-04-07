<?php
namespace App\Presentation\Item;

use Nette;
use Nette\Utils\Finder;

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

        /*$reviews = $this->database
            ->table('product_reviews')
            ->where('product_id', $id);

        if ($reviews->count() === 0) {
            $this->template->reviews = "Not found";
        }else{
            $this->template->reviews = $reviews;
        }

        dump($id);
        dump($reviews->message->fetchAll());
        */
        $this->template->product = $product;
        $this->template->productData = $product->toArray();
    }
}
