<?php
declare(strict_types=1);

namespace RealtimeDespatch\OrderFlow\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Module\Manager;

class ProductReindexer
{
    private Manager $moduleManager;
    private IndexerRegistry $indexerRegistry;
    private ProductResourceModel $productResource;

    public function __construct(
        Manager $moduleManager,
        IndexerRegistry $indexerRegistry,
        ProductResourceModel $productResource
    ) {
        $this->moduleManager = $moduleManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->productResource = $productResource;
    }

    public function reindexSkus(array $skus): void
    {
        if (!$skus) {
            return;
        }

        if (!$this->moduleManager->isEnabled('Magento_InventoryApi')) {
            return;
        }

        $skus = array_values(array_unique($skus));
        $idsBySkus = $this->productResource->getProductsIdsBySkus($skus);
        $productIds = array_map('intval', array_values($idsBySkus));
        if (!$productIds) {
            return;
        }

        $this->indexerRegistry->get('cataloginventory_stock')->reindexList($productIds);
        $this->indexerRegistry->get('catalog_product_price')->reindexList($productIds);
        $this->indexerRegistry->get('catalogsearch_fulltext')->reindexList($productIds);
    }
}
