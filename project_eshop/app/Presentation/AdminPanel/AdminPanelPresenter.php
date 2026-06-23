<?php

namespace App\Presentation\AdminPanel;

use Nette;
use App\Presentation\BasePresenter;

final class AdminPanelPresenter extends BasePresenter
{
    private const PRODUCT_DEPENDENCY_TABLES = [
        'product_categories',
        'product_pricing',
        'product_reviews',
        'product_stock',
        'product_translations',
    ];

    public function __construct(
        private Nette\Database\Explorer $database,
    )
    {}

    public function renderDefault(?int $sellerId = null): void
    {
        $sellerOptions = [];
        $sellerRows = $this->database
            ->table('products')
            ->where('seller_id IS NOT NULL')
            ->group('seller_id')
            ->order('seller_id');

        foreach ($sellerRows as $productRow) {
            $seller = $productRow->ref('users', 'seller_id');

            if ($seller !== null) {
                $sellerOptions[(int) $seller->id] = $seller->name;
            }
        }

        asort($sellerOptions, SORT_NATURAL | SORT_FLAG_CASE);

        $selectedSeller = null;
        $products = [];

        if ($sellerId !== null) {
            $selectedSeller = $this->database
                ->table('users')
                ->get($sellerId);

            if ($selectedSeller !== null) {
                $products = $this->database
                    ->table('products')
                    ->where('seller_id', $selectedSeller->id)
                    ->order('creation_date DESC')
                    ->fetchAll();
            }
        }

        $this->template->sellers = $sellerOptions;
        $this->template->selectedSellerId = $sellerId;
        $this->template->selectedSeller = $selectedSeller;
        $this->template->products = $products;
    }

    public function renderSellers(string $sellerName): void
    {
        $selectedSeller = $this->database
            ->table('users')
            ->where('name', $sellerName)
            ->fetch();

        $this->setView('itemList');
        $this->template->sellerName = $sellerName;
        $this->template->selectedSellerId = $selectedSeller?->id;
        $this->template->products = $selectedSeller === null
            ? []
            : $this->database
            ->table('products')
            ->where('seller_id', $selectedSeller->id)
            ->fetchAll();
    }

    public function handleDeleteProduct(int $productId, ?int $sellerId = null): void
    {
        $product = $this->database
            ->table('products')
            ->get($productId);

        if ($product === null) {
            $this->flashMessage('Product was not found.', 'warning');
            $this->redirect('default', ['sellerId' => $sellerId]);
        }

        if ($sellerId !== null && (int) $product->seller_id !== $sellerId) {
            $this->flashMessage('Selected product does not belong to the filtered seller.', 'danger');
            $this->redirect('default', ['sellerId' => $sellerId]);
        }

        try {
            $this->database->beginTransaction();

            foreach (self::PRODUCT_DEPENDENCY_TABLES as $tableName) {
                $this->database
                    ->table($tableName)
                    ->where('product_id', $productId)
                    ->delete();
            }

            $productName = (string) ($product->name ?? ('#' . $productId));
            $product->delete();

            $this->database->commit();
            $this->flashMessage(sprintf('Product "%s" byl smazán!', $productName), 'success');
        } catch (\Throwable $e) {
            $this->database->rollBack();
            $this->flashMessage('Product could not be deleted. It may still be referenced by orders or other data.', 'danger');
        }

        $this->redirect('default', ['sellerId' => $sellerId]);
    }
}
