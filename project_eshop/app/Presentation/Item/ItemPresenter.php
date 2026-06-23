<?php
namespace App\Presentation\Item;

use Nette;
use App\Presentation\BasePresenter;

final class ItemPresenter extends BasePresenter
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

        $pricing = $product->related('product_pricing')
            ->where('currency', 'CZK')
            ->fetch();

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
        $this->template->pricing = $pricing;
    }

    public function handleAddToCart(int $productId): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $userId = $this->getUser()->getId();

        $existing = $this->database->table('cart_items')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->fetch();

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + 1,
            ]);
        } else {
            $this->database->table('cart_items')->insert([
                'user_id'    => $userId,
                'product_id' => $productId,
                'quantity'   => 1,
            ]);
        }

        $this->flashMessage('Produkt přidán do košíku.', 'success');
        $this->flashMessage('WELLLLL....nefunguje....', 'error');
        $this->redirect('this');
    }
}
