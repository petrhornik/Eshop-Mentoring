<?php
namespace App\Presentation\SellerPortfolio;

use Nette;

final class SellerPortfolioPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    public function renderPortfolio(int $user_id): void
    {
        $portfolio = $this->database
            ->table('seller_portfolios')
            ->get($user_id);

        if (!$portfolio) {
            $this->error('Hledaný prodejce neexistuje.', 404);
        }

        $this->template->portfolio = $portfolio;
        $this->template->portfolioData = $portfolio->toArray();
    }
}