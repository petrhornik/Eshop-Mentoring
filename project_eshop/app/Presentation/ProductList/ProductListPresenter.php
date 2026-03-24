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
        $this->template->products = $this->database
        ->table('products')
        ->order('creation_date DESC');

        // $this->template->sellers = $this->database->query("SELECT usr.name FROM users AS usr JOIN products AS p ON usr.id = p.seller_id");
    }
}