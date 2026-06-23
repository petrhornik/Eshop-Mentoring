<?php declare(strict_types=1);

namespace App\Presentation\ShoppingCart;

use Nette;
use App\Presentation\BasePresenter;


final class ShoppingCartPresenter extends BasePresenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    public function renderOverview(): void
    {


        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("Pro tuto akci se musíte přihlásit!", 'success');
            $this->redirect('UserForm:login');
        }

        $userId = $this->getUser()->getId();

        $cart_items = $this->database->table('cart_items')
            ->where('user_id', $userId);

        $count_all_products = $cart_items
            ->sum('quantity');

        $count_different_products = $cart_items
            ->count('product_id');


        $this->template->cart_items = $cart_items;
        $this->template->count_all_products = $count_all_products;
        $this->template->count_different_products = $count_different_products;
    }

    public function handleRemoveFromCart($id): void
    {

    }

    public function handleRemoveAllFromCart(): void
    {
        $userId = $this->getUser()->getId();

        $this->database->table('cart_items')
            ->get($userId)
            ->delete();
    }
}
