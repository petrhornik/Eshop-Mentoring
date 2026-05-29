<?php
namespace App\Presentation\SellerPortfolio;

use Nette;
use App\Presentation\BasePresenter;

final class SellerPortfolioPresenter extends BasePresenter
{
    private const FIELD_LABELS = [
        'id' => 'Portfolio ID',
        'user_id' => 'ID prodejce',
        'description' => 'Popis obchodu',
        'business_name' => 'Obchodní jméno',
        'brand_name' => 'Značka',
        'store_name' => 'Název obchodu',
        'tagline' => 'Hlavní slogan',
        'country' => 'Země',
        'city' => 'Město',
        'address' => 'Adresa',
        'website' => 'Web',
        'phone' => 'Telefon',
        'email' => 'E-mail',
        'shipping_info' => 'Doprava',
        'return_policy' => 'Vrácení',
        'founded_year' => 'Rok založení',
        'created_at' => 'Vytvořeno',
        'updated_at' => 'Aktualizováno',
        'creation_date' => 'Vytvořeno',
        'modification_date' => 'Upraveno',
    ];

    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    public function renderPortfolio(int $user_id): void
    {
        $portfolio = $this->database
            ->table('seller_portfolios')
            ->where('user_id', $user_id)
            ->fetch();

        if (!$portfolio) {
            $this->error('Hledaný prodejce neexistuje.', 404);
        }

        $seller = $portfolio->ref('users', 'user_id');
        $products = $this->database
            ->table('products')
            ->where('seller_id', $user_id)
            ->order('creation_date DESC');

        $productCategories = [];
        $sellerProductData = [];
        $ratingValues = [];

        foreach ($products as $product) {
            $categoryNames = [];

            foreach ($product->related('product_categories') as $productCategory) {
                $category = $productCategory->ref('categories', 'category_id');

                if ($category !== null) {
                    $categoryNames[] = $category->name;
                }
            }

            $productCategories[$product->id] = $categoryNames;
            $sellerProductData[$product->id] = $product->toArray();

            if ($product->star_rating !== null) {
                $ratingValues[] = (float) $product->star_rating;
            }
        }

        $portfolioData = $portfolio->toArray();
        $portfolioFields = $this->buildPortfolioFields($portfolioData);
        $description = $this->pickFirstNonEmptyValue($portfolioData, [
            'description',
            'about',
            'bio',
            'story',
            'tagline',
        ]) ?? 'Tento prodejce zatím nezveřejnil detailní představení. Portfolio ale obsahuje základní data a aktuální nabídku produktů.';

        $headline = $this->pickFirstNonEmptyValue($portfolioData, [
            'store_name',
            'business_name',
            'brand_name',
            'tagline',
        ]) ?? ($seller?->name ?? ('Prodejce #' . $user_id));

        $this->template->portfolio = $portfolio;
        $this->template->portfolioData = $portfolioData;
        $this->template->portfolioFields = $portfolioFields;
        $this->template->seller = $seller;
        $this->template->sellerProducts = $products;
        $this->template->sellerProductData = $sellerProductData;
        $this->template->productCategories = $productCategories;
        $this->template->sellerDescription = $description;
        $this->template->sellerHeadline = $headline;
        $this->template->sellerStats = [
            'productCount' => count($productCategories),
            'averageRating' => $ratingValues !== [] ? round(array_sum($ratingValues) / count($ratingValues), 1) : null,
            'portfolioFieldCount' => count($portfolioFields),
        ];
        $this->template->profileImage = $this->resolveSellerAsset($user_id, 'profile');
        $this->template->bannerImage = $this->resolveSellerAsset($user_id, 'banner');
    }

    /**
     * @param array<string, mixed> $portfolioData
     * @return array<int, array{label: string, value: string}>
     */
    private function buildPortfolioFields(array $portfolioData): array
    {
        $fields = [];

        foreach ($portfolioData as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $fields[] = [
                'label' => self::FIELD_LABELS[$key] ?? $this->formatFieldLabel($key),
                'value' => $this->formatFieldValue($value),
            ];
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $portfolioData
     * @param array<int, string> $keys
     */
    private function pickFirstNonEmptyValue(array $portfolioData, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $portfolioData[$key] ?? null;

            if ($value !== null && $value !== '') {
                return $this->formatFieldValue($value);
            }
        }

        return null;
    }

    private function formatFieldLabel(string $key): string
    {
        return mb_convert_case(str_replace('_', ' ', $key), MB_CASE_TITLE, 'UTF-8');
    }

    private function formatFieldValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Ano' : 'Ne';
        }

        return (string) $value;
    }

    private function resolveSellerAsset(int $userId, string $assetName): string
    {
        $projectRoot = dirname(__DIR__, 3);
        $assetBase = $projectRoot . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'seller' . DIRECTORY_SEPARATOR;

        foreach ([
            $userId . DIRECTORY_SEPARATOR . $assetName . '.svg',
            $userId . DIRECTORY_SEPARATOR . $assetName . '.png',
            'default' . DIRECTORY_SEPARATOR . $assetName . '.svg',
        ] as $relativePath) {
            if (is_file($assetBase . $relativePath)) {
                return '/assets/seller/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            }
        }

        return '/assets/seller/default/' . $assetName . '.svg';
    }
}
