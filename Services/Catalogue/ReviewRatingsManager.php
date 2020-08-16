<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Reports\Model\ResourceModel\Review\Collection;

class ReviewRatingsManager
{
    private $reviewFactory;

    public function __construct(ReviewFactory $reviewFactory, Collection $collection)
    {
        $this->reviewFactory = $reviewFactory;
    }

    public function getSummary(array $productIds, $storeId): array
    {
        $reviews = $this->getReviewRatings($productIds, $storeId);
        $productReviews = $this->getInitReviewsArray($productIds);

        foreach ($reviews as $review) {
            $reviewData = $review->getData();
            $productId = $reviewData['entity_pk_value'];
            $productReviews[$productId]['totalReviews']++;

            if (count($reviewData['rating_votes'])) {
                foreach ($reviewData['rating_votes'] as $ratingVote) {
                    $ratingData = $ratingVote->getData();
                    $productReviews[$productId]['totalRatings']++;
                    $productReviews[$productId]['ratingsSum'] += $ratingData['percent'];
                    $productReviews[$productId]['avgRatings'] =
                       $productReviews[$productId]['ratingsSum'] / $productReviews[$productId]['totalRatings'];
                }
            }
        }
        return $productReviews;
    }

    private function getInitReviewsArray($productIds)
    {
        $reviewsSummary = [];
        foreach ($productIds as $productId) {
            $reviewsSummary[$productId] = [
            'totalReviews' => 0,
            'avgRatings' => 0,
            'totalRatings' => 0,
            'ratingsSum' => 0,
            ];
        }

        return $reviewsSummary;
    }

    private function getReviewRatings(array $productIds, $storeId)
    {
        $reviewCollection = $this->reviewFactory->create()->getResourceCollection();
        $reviewEntityTable = $reviewCollection->getTable('review_entity');

        $reviews = $reviewCollection->addStatusFilter(
            Review::STATUS_APPROVED
        )->join($reviewEntityTable, 'main_table.entity_id = ' . $reviewEntityTable . '.entity_id')
         ->addFilter($reviewEntityTable . '.entity_code', 'product')
         ->addFieldToFilter('main_table.entity_pk_value', ['IN' => [$productIds]])
         ->addStoreFilter($storeId)
         ->addRateVotes();

        $reviews = $reviews->getItems();

        return $reviews;
    }
}
